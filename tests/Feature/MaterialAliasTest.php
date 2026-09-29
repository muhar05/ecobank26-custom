<?php

namespace Tests\Feature;

use App\Models\Collector;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\MaterialAlias;
use App\Models\SavingsLedger;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use App\Models\WasteCustomer;
use App\Models\WastePrice;
use App\Services\BankSampahService;
use App\Services\MaterialMappingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaterialAliasTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected MaterialMappingService $mappingService;
    protected WasteCategoryGroup $groupKertas;
    protected WasteCategoryGroup $groupPlastik;
    protected WasteCategory $catKertasJelek;
    protected WasteCategory $catKertasHVS;
    protected WasteCategory $catPlastikAsoy;
    protected Collector $collectorA;
    protected Collector $collectorB;

    protected function setUp(): void
    {
        parent::setUp();

        // Permissions & Roles
        $permDeposit = Permission::firstOrCreate(['name' => 'manage_deposits', 'guard_name' => 'web']);
        $permPrice = Permission::firstOrCreate(['name' => 'manage_waste_prices', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $role->givePermissionTo([$permDeposit, $permPrice]);

        $this->adminUser = User::factory()->create([
            'phone' => '081234567890',
        ]);
        $this->adminUser->assignRole($role);

        $this->mappingService = app(MaterialMappingService::class);

        // Setup Groups
        $this->groupKertas = WasteCategoryGroup::create([
            'code' => 'KRT',
            'name' => 'Kertas',
            'is_active' => true,
        ]);

        $this->groupPlastik = WasteCategoryGroup::create([
            'code' => 'PLS',
            'name' => 'Plastik',
            'is_active' => true,
        ]);

        // Setup Master Categories
        $this->catKertasJelek = WasteCategory::create([
            'code'                    => 'KRT.01',
            'name'                    => 'Kertas Jelek/Kusut',
            'waste_category_group_id' => $this->groupKertas->id,
            'unit'                    => 'kg',
            'member_price'            => 800,
        ]);

        $this->catKertasHVS = WasteCategory::create([
            'code'                    => 'KRT.02',
            'name'                    => 'Kertas HVS',
            'waste_category_group_id' => $this->groupKertas->id,
            'unit'                    => 'kg',
            'member_price'            => 2000,
        ]);

        $this->catPlastikAsoy = WasteCategory::create([
            'code'                    => 'PLS.01',
            'name'                    => 'Asoy',
            'waste_category_group_id' => $this->groupPlastik->id,
            'unit'                    => 'kg',
            'member_price'            => 1500,
        ]);

        // Setup Collectors
        $this->collectorA = Collector::create([
            'name'  => 'PT Daur Ulang Mandiri',
            'phone' => '0811111111',
        ]);

        $this->collectorB = Collector::create([
            'name'  => 'Lapak Berkah Jaya',
            'phone' => '0822222222',
        ]);
    }

    /**
     * Test 1: Exact canonical match by name or code resolves directly to VALID.
     */
    public function test_exact_canonical_match_resolves_to_valid(): void
    {
        // By Name
        $resName = $this->mappingService->resolve('Kertas HVS', MaterialAlias::SOURCE_BANK_SAMPAH);
        $this->assertEquals('VALID', $resName['status']);
        $this->assertEquals('EXACT', $resName['match_type']);
        $this->assertEquals($this->catKertasHVS->id, $resName['category']->id);

        // By Code
        $resCode = $this->mappingService->resolve('KRT.01', MaterialAlias::SOURCE_BANK_SAMPAH);
        $this->assertEquals('VALID', $resCode['status']);
        $this->assertEquals($this->catKertasJelek->id, $resCode['category']->id);
    }

    /**
     * Test 2: Known persistent alias resolves to ALIAS.
     */
    public function test_known_alias_resolves_to_alias(): void
    {
        $this->mappingService->registerAlias(
            'Kertas Bekas',
            $this->catKertasJelek->id,
            MaterialAlias::SOURCE_BANK_SAMPAH,
            null
        );

        $res = $this->mappingService->resolve('Kertas Bekas', MaterialAlias::SOURCE_BANK_SAMPAH);
        $this->assertEquals('ALIAS', $res['status']);
        $this->assertEquals('ALIAS', $res['match_type']);
        $this->assertEquals($this->catKertasJelek->id, $res['category']->id);
        $this->assertNotNull($res['alias']);
    }

    /**
     * Test 3: Normalization produces consistent lookup across cases and extra spaces.
     */
    public function test_normalization_produces_consistent_lookup(): void
    {
        $this->assertEquals('kertas hvs', $this->mappingService->normalizeLabel('Kertas HVS'));
        $this->assertEquals('kertas hvs', $this->mappingService->normalizeLabel('  kertas   hvs  '));
        $this->assertEquals('kertas hvs', $this->mappingService->normalizeLabel("Kertas\n\tHVS"));

        $this->mappingService->registerAlias(
            'Kertas Buram Halus',
            $this->catKertasHVS->id,
            MaterialAlias::SOURCE_BANK_SAMPAH
        );

        // All variations resolve to the same alias
        $res1 = $this->mappingService->resolve('  kertas   BURAM   halus  ');
        $this->assertEquals('ALIAS', $res1['status']);
        $this->assertEquals($this->catKertasHVS->id, $res1['category']->id);
    }

    /**
     * Test 4: Batch resolution maps multiple rows with the same external label once.
     */
    public function test_batch_resolution_maps_multiple_rows_once(): void
    {
        $this->mappingService->registerAlias(
            'Kertas Kusut',
            $this->catKertasJelek->id,
            MaterialAlias::SOURCE_BANK_SAMPAH
        );

        $rawLabels = array_fill(0, 10, 'Kertas Kusut');
        $resolvedCategories = [];

        foreach ($rawLabels as $label) {
            $res = $this->mappingService->resolve($label, MaterialAlias::SOURCE_BANK_SAMPAH);
            $resolvedCategories[] = $res['category'] ? $res['category']->id : null;
        }

        // All 10 rows resolve to catKertasJelek
        $this->assertCount(10, $resolvedCategories);
        $this->assertEquals(array_fill(0, 10, $this->catKertasJelek->id), $resolvedCategories);
    }

    /**
     * Test 5: Source isolation between Aggregator A and Aggregator B.
     */
    public function test_source_isolation_between_aggregators(): void
    {
        // Aggregator A maps "Kertas Campur" to Kertas Jelek/Kusut
        $this->mappingService->registerAlias(
            'Kertas Campur',
            $this->catKertasJelek->id,
            MaterialAlias::SOURCE_AGGREGATOR,
            $this->collectorA->id
        );

        // Aggregator B maps "Kertas Campur" to Kertas HVS
        $this->mappingService->registerAlias(
            'Kertas Campur',
            $this->catKertasHVS->id,
            MaterialAlias::SOURCE_AGGREGATOR,
            $this->collectorB->id
        );

        // When resolving for Aggregator A -> Kertas Jelek
        $resA = $this->mappingService->resolve('Kertas Campur', MaterialAlias::SOURCE_AGGREGATOR, $this->collectorA->id);
        $this->assertEquals('ALIAS', $resA['status']);
        $this->assertEquals($this->catKertasJelek->id, $resA['category']->id);

        // When resolving for Aggregator B -> Kertas HVS
        $resB = $this->mappingService->resolve('Kertas Campur', MaterialAlias::SOURCE_AGGREGATOR, $this->collectorB->id);
        $this->assertEquals('ALIAS', $resB['status']);
        $this->assertEquals($this->catKertasHVS->id, $resB['category']->id);

        // When resolving for Bank Sampah -> NOT resolved (isolation preserved!)
        $resBS = $this->mappingService->resolve('Kertas Campur', MaterialAlias::SOURCE_BANK_SAMPAH);
        $this->assertEquals('NEEDS_MAPPING', $resBS['status']);
    }

    /**
     * Test 6: Prefix candidate suggestion provides candidates but does NOT auto-resolve.
     */
    public function test_prefix_candidate_suggestion_does_not_auto_resolve(): void
    {
        $res = $this->mappingService->resolve('Kertas Buram', MaterialAlias::SOURCE_BANK_SAMPAH);

        // Must NOT auto-match
        $this->assertEquals('NEEDS_MAPPING', $res['status']);
        $this->assertNull($res['category']);

        // Candidates must contain Kertas categories as suggestion
        $candidateNames = $res['candidates']->pluck('name')->toArray();
        $this->assertContains('Kertas Jelek/Kusut', $candidateNames);
        $this->assertContains('Kertas HVS', $candidateNames);
    }

    /**
     * Test 7: Quick Add Canonical Material creates WasteCategory and registers alias.
     */
    public function test_quick_add_material_creates_category_and_registers_alias(): void
    {
        $depositImportService = app(\App\Services\DepositImportService::class);

        $newCat = $depositImportService->quickAddCategory(
            'Kardus Tebal',
            $this->groupKertas->id,
            'kg',
            1200,
            'Kardus Coklat Tebal' // raw label to alias
        );

        $this->assertDatabaseHas('waste_categories', [
            'name'         => 'Kardus Tebal',
            'member_price' => 1200,
        ]);

        $this->assertDatabaseHas('material_aliases', [
            'external_name'     => 'Kardus Coklat Tebal',
            'waste_category_id' => $newCat->id,
            'source_type'       => MaterialAlias::SOURCE_BANK_SAMPAH,
        ]);

        // Resolving the raw label now yields ALIAS to the new category!
        $res = $this->mappingService->resolve('Kardus Coklat Tebal', MaterialAlias::SOURCE_BANK_SAMPAH);
        $this->assertEquals('ALIAS', $res['status']);
        $this->assertEquals($newCat->id, $res['category']->id);
    }

    /**
     * Test 8: Bulk mapping registers multiple aliases at once.
     */
    public function test_bulk_mapping_registers_multiple_aliases(): void
    {
        $mappings = [
            'Kertas Bekas'  => $this->catKertasJelek->id,
            'Kertas Putihan' => $this->catKertasHVS->id,
            'Plastik Kresek' => $this->catPlastikAsoy->id,
        ];

        $registered = $this->mappingService->registerAliases($mappings, MaterialAlias::SOURCE_BANK_SAMPAH);
        $this->assertCount(3, $registered);

        $this->assertDatabaseHas('material_aliases', ['external_name' => 'Kertas Bekas']);
        $this->assertDatabaseHas('material_aliases', ['external_name' => 'Kertas Putihan']);
        $this->assertDatabaseHas('material_aliases', ['external_name' => 'Plastik Kresek']);
    }

    /**
     * Test 9: Historical transactions remain immutable when alias mapping changes later.
     */
    public function test_historical_immutability_when_alias_changes(): void
    {
        $cust = WasteCustomer::create([
            'customer_code' => WasteCustomer::generateNextCustomerCode(),
            'name'          => 'Ibu Ani',
            'status'        => 'active',
        ]);

        // 1. Initial alias: "Kertas Kantor" -> Kertas HVS (ID: catKertasHVS)
        $this->mappingService->registerAlias(
            'Kertas Kantor',
            $this->catKertasHVS->id,
            MaterialAlias::SOURCE_BANK_SAMPAH
        );

        // 2. Create historical deposit using that category
        $bankSampahService = app(BankSampahService::class);
        $deposit = $bankSampahService->recordDeposit([
            'waste_customer_id' => $cust->id,
            'date'              => '2026-09-01',
            'details'           => [
                [
                    'waste_category_id' => $this->catKertasHVS->id,
                    'weight'            => 10.0,
                    'price_per_unit'    => 2000,
                    'subtotal'          => 20000,
                ],
            ],
        ]);

        $detailId = $deposit->details()->first()->id;

        // 3. Later, operator re-points the alias "Kertas Kantor" to Kertas Jelek/Kusut
        $this->mappingService->registerAlias(
            'Kertas Kantor',
            $this->catKertasJelek->id,
            MaterialAlias::SOURCE_BANK_SAMPAH
        );

        // 4. Verify historical DepositDetail and SavingsLedger NEVER changed!
        $detail = DepositDetail::find($detailId);
        $this->assertEquals($this->catKertasHVS->id, $detail->waste_category_id);
        $this->assertEquals(2000, (float) $detail->price_per_unit);
        $this->assertEquals(20000, (float) $detail->subtotal);

        $ledger = SavingsLedger::where('reference_id', $deposit->id)->first();
        $this->assertEquals(20000, (float) $ledger->amount);
    }

    /**
     * Test 10: Inactive alias is ignored and returns NEEDS_MAPPING.
     */
    public function test_inactive_alias_is_ignored(): void
    {
        $alias = $this->mappingService->registerAlias(
            'Kertas Bekas',
            $this->catKertasJelek->id,
            MaterialAlias::SOURCE_BANK_SAMPAH,
            null,
            true
        );

        // Deactivate alias
        $this->mappingService->toggleAliasStatus($alias->id, false);

        $res = $this->mappingService->resolve('Kertas Bekas', MaterialAlias::SOURCE_BANK_SAMPAH);
        $this->assertEquals('NEEDS_MAPPING', $res['status']);
        $this->assertNull($res['category']);
    }

    /**
     * Test 11: Duplicate alias registration updates/reuses safely without DB violation.
     */
    public function test_duplicate_alias_registration_is_safe(): void
    {
        // First registration
        $alias1 = $this->mappingService->registerAlias(
            'Kertas Bekas',
            $this->catKertasJelek->id,
            MaterialAlias::SOURCE_BANK_SAMPAH
        );

        // Second registration with same normalized name
        $alias2 = $this->mappingService->registerAlias(
            '  kertas   bekas  ',
            $this->catKertasJelek->id,
            MaterialAlias::SOURCE_BANK_SAMPAH
        );

        $this->assertEquals($alias1->id, $alias2->id);
        $this->assertEquals(1, MaterialAlias::where('normalized_name', 'kertas bekas')->count());
    }

    /**
     * Test 12: Aggregator price import uses scoped alias correctly.
     */
    public function test_aggregator_price_import_resolves_scoped_alias(): void
    {
        // Map "Kertas Industri" -> catKertasHVS for collectorA
        $this->mappingService->registerAlias(
            'Kertas Industri',
            $this->catKertasHVS->id,
            MaterialAlias::SOURCE_AGGREGATOR,
            $this->collectorA->id
        );

        // Build Excel with collector price list
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DATA IMPORT');
        $sheet->fromArray([
            ['Kode Grup', 'Nama Grup', 'Kode Kategori', 'Nama Kategori', 'Satuan', 'Harga Nasabah', 'Harga Agregator', 'Tanggal Berlaku'],
            ['', '', '', 'Kertas Industri', 'kg', '2000', '2800', '2026-09-30'],
        ], null, 'A1');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_price_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);
        $file = new UploadedFile($tempPath, 'harga.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        // Post to WastePriceImportController
        $response = $this->actingAs($this->adminUser)->post(route('bank-sampah.waste-prices.import.store'), [
            'collector_id' => $this->collectorA->id,
            'file'         => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('import_result');

        // WastePrice must be created for collectorA and catKertasHVS!
        $this->assertDatabaseHas('waste_prices', [
            'collector_id'      => $this->collectorA->id,
            'waste_category_id' => $this->catKertasHVS->id,
            'collector_price'   => 2800,
        ]);
    }
}
