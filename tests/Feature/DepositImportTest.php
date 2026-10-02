<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\SavingsLedger;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Services\DepositImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepositImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected WasteCategory $catAsoy;
    protected WasteCategory $catEmberan;
    protected WasteCategory $catGabrukan;
    protected WasteCustomer $custSarti;
    protected WasteCustomer $custBudi;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions & roles
        $perm = Permission::firstOrCreate(['name' => 'manage_deposits', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $role->givePermissionTo($perm);

        $this->adminUser = User::factory()->create([
            'phone' => '081234567890',
        ]);
        $this->adminUser->assignRole($role);

        $this->regularUser = User::factory()->create([
            'phone' => '081234567891',
        ]);

        // Setup master data
        $this->catAsoy = WasteCategory::create([
            'name' => 'Asoy',
            'code' => 'ASY',
            'unit' => 'kg',
            'member_price' => 3000,
        ]);

        $this->catEmberan = WasteCategory::create([
            'name' => 'Emberan',
            'code' => 'EMB',
            'unit' => 'kg',
            'member_price' => 2500,
        ]);

        $this->catGabrukan = WasteCategory::create([
            'name' => 'Gabrukan',
            'code' => 'GBR',
            'unit' => 'kg',
            'member_price' => 4000,
        ]);

        $this->custSarti = WasteCustomer::create([
            'customer_code' => WasteCustomer::generateNextCustomerCode(),
            'name' => 'Bu Sarti',
            'phone' => '0811111111',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->custBudi = WasteCustomer::create([
            'customer_code' => WasteCustomer::generateNextCustomerCode(),
            'name' => 'Pak Budi',
            'phone' => '0822222222',
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        $dir = storage_path('app/temp_imports');
        if (File::isDirectory($dir)) {
            File::cleanDirectory($dir);
        }
        parent::tearDown();
    }

    /**
     * Helper to create real test Excel files.
     */
    protected function createExcelFile(array $sheetsData): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $isFirst = true;

        foreach ($sheetsData as $sheetTitle => $rows) {
            if ($isFirst) {
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle((string) $sheetTitle);
                $isFirst = false;
            } else {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle((string) $sheetTitle);
            }
            $sheet->fromArray($rows, null, 'A1');
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'test_ecobank_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile(
            $tempPath,
            'catatan_timbangan.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    protected function extractTokenFromRedirect($response): string
    {
        $location = $response->headers->get('Location');
        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        return $query['token'] ?? '';
    }

    public function test_unauthorized_user_cannot_access_import_routes(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('bank-sampah.deposits.import'));
        $response->assertStatus(403);
    }

    public function test_upload_file_detects_sheets_and_guesses_date(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
            ],
            '12-10-26' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Pak Budi', 'Emberan', 3, 2500, 7500],
            ],
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $token = $this->extractTokenFromRedirect($response);
        $this->assertNotEmpty($token);

        // Follow redirect to see sheet selection view
        $viewResponse = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('28-09-2026');
        $viewResponse->assertSee('12-10-26');
    }

    public function test_invalid_file_rejected_by_mime_and_extension(): void
    {
        $invalidFile = UploadedFile::fake()->create('hacked.php', 100, 'application/x-php');

        $response = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), [
            'file' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_parsing_exact_customer_and_material_auto_matches(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
                ['Pak Budi', 'Gabrukan', 8, 4000, 32000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $previewResponse = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $previewResponse->assertRedirect(route('bank-sampah.deposits.import', ['token' => $token]));

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertNotNull($staging);
        $this->assertCount(2, $staging['rows']);
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals($this->custSarti->id, $staging['rows'][0]['customer_id']);
        $this->assertEquals($this->catAsoy->id, $staging['rows'][0]['category_id']);
    }

    public function test_unmatched_customer_triggers_needs_mapping(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti Baru', 'Asoy', 5, 3000, 15000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('NEEDS MAPPING', $staging['rows'][0]['status']);
        $this->assertEquals('NEEDS MAPPING', $staging['rows'][0]['customer_status']);
    }

    public function test_quick_add_customer_resolves_all_identical_rows(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Warga Baru', 'Asoy', 2, 3000, 6000],
                ['Warga Baru', 'Emberan', 3, 2500, 7500],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Quick add the new customer
        $response = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.quick-add-customer'), [
            'token' => $token,
            'raw_customer' => 'Warga Baru',
            'name' => 'Warga Baru',
            'phone' => '0899999999',
            'address' => 'RT 05',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('waste_customers', [
            'name' => 'Warga Baru',
            'phone' => '0899999999',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        // Both rows must now be resolved to VALID!
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals('VALID', $staging['rows'][1]['status']);
        $this->assertEquals('Warga Baru', $staging['rows'][0]['customer_name']);
        $this->assertEquals('Warga Baru', $staging['rows'][1]['customer_name']);
    }

    public function test_bulk_add_customers_resolves_multiple_unmatched(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Nasabah X', 'Asoy', 2, 3000, 6000],
                ['Nasabah Y', 'Emberan', 3, 2500, 7500],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Bulk Add both customers
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), [
            'token' => $token,
            'customers' => [
                ['raw_customer' => 'Nasabah X', 'name' => 'Nasabah X', 'phone' => '081211', 'address' => 'RW 01'],
                ['raw_customer' => 'Nasabah Y', 'name' => 'Nasabah Y', 'phone' => '081212', 'address' => 'RW 02'],
            ],
        ]);

        $this->assertDatabaseHas('waste_customers', ['name' => 'Nasabah X']);
        $this->assertDatabaseHas('waste_customers', ['name' => 'Nasabah Y']);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals('VALID', $staging['rows'][1]['status']);
    }

    public function test_unmatched_material_mapped_once_resolves_all_rows(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Plastik Kresek Asoy', 4, 3000, 12000],
                ['Pak Budi', 'Plastik Kresek Asoy', 2, 3000, 6000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Map "Plastik Kresek Asoy" to $this->catAsoy
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'material_mappings' => [
                'Plastik Kresek Asoy' => $this->catAsoy->id,
            ],
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals('VALID', $staging['rows'][1]['status']);
        $this->assertEquals($this->catAsoy->id, $staging['rows'][0]['category_id']);
        $this->assertEquals($this->catAsoy->id, $staging['rows'][1]['category_id']);
    }

    public function test_excel_price_used_as_snapshot_does_not_modify_master_member_price(): void
    {
        // Category Asoy has member_price = 3000 in DB, but Excel has 3500
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 10, 3500, 35000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Commit import
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), [
            'token' => $token,
        ]);

        // Master category member_price must remain 3000!
        $this->catAsoy->refresh();
        $this->assertEquals(3000, (float) $this->catAsoy->member_price);

        // Transaction detail must store 3500 as snapshot
        $detail = DepositDetail::latest('id')->first();
        $this->assertEquals(3500, (float) $detail->price_per_unit);
        $this->assertEquals(35000, (float) $detail->subtotal);
    }

    public function test_deposit_grouping_multiple_materials_same_customer_creates_one_deposit_and_one_savings_ledger(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
                ['Bu Sarti', 'Emberan', 2, 2500, 5000],
                ['Bu Sarti', 'Gabrukan', 1, 4000, 4000],
                ['Pak Budi', 'Gabrukan', 10, 4000, 40000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $commitResponse = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), [
            'token' => $token,
        ]);

        $commitResponse->assertRedirect(route('bank-sampah.deposits.import', ['result' => 'success']));

        // Exactly 2 Deposits must be created: 1 for Bu Sarti, 1 for Pak Budi!
        $this->assertEquals(2, Deposit::whereDate('date', '2026-09-28')->count());

        $sartiDeposit = Deposit::where('waste_customer_id', $this->custSarti->id)->first();
        $this->assertNotNull($sartiDeposit);
        $this->assertEquals(3, $sartiDeposit->details()->count());
        $this->assertEquals(24000, (float) $sartiDeposit->total_amount);

        // Exactly 1 SavingsLedger for Bu Sarti with amount 24,000
        $ledger = SavingsLedger::where('waste_customer_id', $this->custSarti->id)
            ->where('reference_id', $sartiDeposit->id)
            ->first();
        $this->assertNotNull($ledger);
        $this->assertEquals(24000, (float) $ledger->amount);
        $this->assertEquals('credit', $ledger->type);
    }

    public function test_exact_reimport_fingerprint_blocked(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
            ],
        ]);

        // First import
        $res1 = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token1 = $this->extractTokenFromRedirect($res1);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token1,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), ['token' => $token1]);

        // Second import of the EXACT same file, sheet, and date
        // Create identical file content
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('28-09-2026');
        $sheet->fromArray([
            ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
            ['Bu Sarti', 'Asoy', 5, 3000, 15000],
        ], null, 'A1');
        $tempPath2 = tempnam(sys_get_temp_dir(), 'test_ecobank_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath2);

        $file2 = new UploadedFile($tempPath2, 'catatan_timbangan.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $res2 = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file2]);
        $token2 = $this->extractTokenFromRedirect($res2);

        $preview2 = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token2,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $preview2->assertSessionHasErrors('sheet_name');
    }

    public function test_commit_blocked_if_errors_or_unmapped_rows_remain(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Unregistered Customer', 'Asoy', 5, 3000, 15000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Attempt to commit with unmapped customer
        $commitResponse = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), [
            'token' => $token,
        ]);

        $commitResponse->assertSessionHasErrors('commit');
        $this->assertEquals(0, Deposit::count());
    }

    public function test_total_calculation_and_rounding_validation(): void
    {
        // 0.33 kg @ 1000 = 330. Excel has 331 (delta = 1 <= 2 => VALID / auto-round).
        // 0.50 kg @ 1000 = 500. Excel has 600 (delta = 100 > 2 => WARNING total).
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 0.33, 3000, 991], // system = 990, delta = -1 <= 2 => VALID
                ['Bu Sarti', 'Emberan', 2, 2500, 6000], // system = 5000, delta = -1000 > 2 => WARNING
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertFalse($staging['rows'][0]['has_total_warning']);

        $this->assertEquals('WARNING', $staging['rows'][1]['status']);
        $this->assertTrue($staging['rows'][1]['has_total_warning']);
    }

    public function test_duplicate_same_customer_and_date_append_or_skip(): void
    {
        // Existing deposit for Bu Sarti on 2026-09-28
        $existing = Deposit::create([
            'waste_customer_id' => $this->custSarti->id,
            'date' => '2026-09-28',
            'total_amount' => 10000,
        ]);

        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
                ['Pak Budi', 'Emberan', 4, 2500, 10000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // Bu Sarti flagged as having existing deposit
        $staging = Cache::get("deposit_import_{$token}");
        $this->assertTrue($staging['rows'][0]['has_existing_deposit']);

        // Set policy for Bu Sarti to 'skip'
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'duplicate_policies' => [
                $this->custSarti->id => 'skip',
            ],
        ]);

        // Commit import
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), [
            'token' => $token,
        ]);

        // Pak Budi gets imported, Bu Sarti is skipped (so only 1 new deposit is created, total deposits on this date = 2 including existing)
        $this->assertEquals(2, Deposit::whereDate('date', '2026-09-28')->count());
        $this->assertEquals(1, Deposit::where('waste_customer_id', $this->custBudi->id)->count());
        $this->assertEquals(1, Deposit::where('waste_customer_id', $this->custSarti->id)->count());
    }

    public function test_empty_rows_are_ignored_and_invalid_weight_produces_error(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Bu Sarti', 'Asoy', 5, 3000, 15000],
                [null, null, null, null, null], // completely blank row
                ['Pak Budi', 'Emberan', -2, 2500, -5000], // negative weight => ERROR
                ['', '', '', '', ''], // empty string row
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        // Total rows must be 2 (blank rows ignored!)
        $this->assertCount(2, $staging['rows']);
        $this->assertEquals('VALID', $staging['rows'][0]['status']);
        $this->assertEquals('ERROR', $staging['rows'][1]['status']);
    }

    /**
     * Realistic 300-row scenario: repeated customer/material labels must be
     * resolvable through UNIQUE entity actions only, never row by row.
     *
     * Layout: 50 rows exact-matched + 250 rows across 8 new unique customers
     * and 3 new unique materials.
     */
    public function test_300_rows_resolve_through_unique_entity_actions_only(): void
    {
        $sheetRows = [['Nasabah', 'Material', 'Berat', 'Harga', 'Total']];

        // 50 rows of exact matches (automatically VALID)
        for ($i = 0; $i < 30; $i++) {
            $sheetRows[] = ['Bu Sarti', 'Asoy', 5, 3000, 15000];
        }
        for ($i = 0; $i < 20; $i++) {
            $sheetRows[] = ['Pak Budi', 'Emberan', 4, 2500, 10000];
        }

        // 8 new unique customers with repeated labels (250 rows total)
        $newCustomers = [
            'Warga Baru Alpha' => 45,
            'Warga Baru Beta' => 40,
            'Warga Baru Gamma' => 35,
            'Warga Baru Delta' => 30,
            'Warga Baru Epsilon' => 30,
            'Warga Baru Zeta' => 25,
            'Warga Baru Eta' => 25,
            'Warga Baru Theta' => 20,
        ];

        // 3 new unique materials mapped to existing categories by price
        $newMaterials = [
            'Kardus Aqua Bekas' => ['category' => 'catAsoy', 'price' => 3000],
            'Botol Kaca Hijau' => ['category' => 'catEmberan', 'price' => 2500],
            'Koran Bekas Tebal' => ['category' => 'catGabrukan', 'price' => 4000],
        ];
        $newMaterialNames = array_keys($newMaterials);

        $rowIdx = 0;
        foreach ($newCustomers as $custName => $rowCount) {
            for ($i = 0; $i < $rowCount; $i++) {
                // First rows of each customer use a new material label (cycled),
                // remaining rows use an existing master material.
                if ($i < 10) {
                    $matName = $newMaterialNames[$rowIdx % 3];
                    $price = $newMaterials[$matName]['price'];
                } else {
                    $matName = 'Asoy';
                    $price = 3000;
                }
                $sheetRows[] = [$custName, $matName, 2, $price, 2 * $price];
                $rowIdx++;
            }
        }

        $this->assertCount(301, $sheetRows); // header + 300 data rows

        $file = $this->createExcelFile(['28-09-2026' => $sheetRows]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);
        $this->assertNotEmpty($token);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertCount(300, $staging['rows']);

        // 1. Unique grouping: 8 unresolved customers (not 250 actions), 3 unresolved materials
        $previewRes = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $previewRes->assertStatus(200);

        $unmatchedCustomers = [];
        $unmatchedMaterials = [];
        foreach ($staging['rows'] as $r) {
            if (in_array($r['customer_status'], ['NEEDS MAPPING', 'AMBIGUOUS'], true)) {
                $unmatchedCustomers[$r['raw_customer']] = true;
            }
            if ($r['material_status'] === 'NEEDS MAPPING') {
                $unmatchedMaterials[$r['raw_material']] = true;
            }
        }
        $this->assertCount(8, $unmatchedCustomers);
        $this->assertCount(3, $unmatchedMaterials);

        // Existing exact matches remain automatically VALID
        $exactValid = array_filter($staging['rows'], fn($r) => in_array($r['raw_customer'], ['Bu Sarti', 'Pak Budi'], true));
        $this->assertCount(50, $exactValid);
        foreach ($exactValid as $r) {
            $this->assertEquals('VALID', $r['status']);
        }

        // 2 & 3. ONE bulk operation creates all 8 customers and propagates to every repeated row
        $bulkPayload = ['token' => $token, 'customers' => []];
        foreach (array_keys($newCustomers) as $custName) {
            $bulkPayload['customers'][] = ['raw_customer' => $custName, 'name' => $custName];
        }

        $this->actingAs($this->adminUser)
            ->post(route('bank-sampah.deposits.import.bulk-add-customers'), $bulkPayload)
            ->assertRedirect();

        // 4. No duplicate customers for repeated raw names
        foreach (array_keys($newCustomers) as $custName) {
            $this->assertEquals(1, WasteCustomer::where('name', $custName)->count());
        }

        $staging = Cache::get("deposit_import_{$token}");
        foreach ($staging['rows'] as $r) {
            // Every row now references a customer (propagation to all repeated rows)
            $this->assertNotNull($r['customer_id'], "Row {$r['excel_row_num']} still has no customer");
        }

        // 5 & 6. ONE explicit mapping per unique material resolves all its repeated rows
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.resolve'), [
            'token' => $token,
            'material_mappings' => [
                'Kardus Aqua Bekas' => $this->catAsoy->id,
                'Botol Kaca Hijau' => $this->catEmberan->id,
                'Koran Bekas Tebal' => $this->catGabrukan->id,
            ],
        ])->assertRedirect();

        $staging = Cache::get("deposit_import_{$token}");
        foreach ($staging['rows'] as $r) {
            $this->assertNotNull($r['category_id'], "Row {$r['excel_row_num']} still has no category");
        }

        // 7 & 8. canCommit becomes true: preview shows ready state with zero unresolved blockers
        $readyRes = $this->actingAs($this->adminUser)->get(route('bank-sampah.deposits.import', ['token' => $token]));
        $readyRes->assertStatus(200);
        $readyRes->assertSee('Siap Diimpor', false);
        $readyRes->assertDontSee('Belum Siap Diimpor', false);

        // Final commit uses existing rules and creates one deposit per unique customer (10 total)
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.commit'), [
            'token' => $token,
        ])->assertRedirect();

        $this->assertEquals(10, Deposit::whereDate('date', '2026-09-28')->count());
        $this->assertEquals(300, DepositDetail::count());
    }

    public function test_bulk_customer_creation_is_atomic_on_validation_failure(): void
    {
        $file = $this->createExcelFile([
            '28-09-2026' => [
                ['Nasabah', 'Material', 'Berat', 'Harga', 'Total'],
                ['Nasabah Atom A', 'Asoy', 2, 3000, 6000],
                ['Nasabah Atom B', 'Asoy', 2, 3000, 6000],
            ],
        ]);

        $uploadRes = $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.parse'), ['file' => $file]);
        $token = $this->extractTokenFromRedirect($uploadRes);

        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.preview'), [
            'token' => $token,
            'sheet_name' => '28-09-2026',
            'confirmed_date' => '2026-09-28',
        ]);

        // One entry violates validation (empty name) -> whole bulk operation must fail atomically
        $this->actingAs($this->adminUser)->post(route('bank-sampah.deposits.import.bulk-add-customers'), [
            'token' => $token,
            'customers' => [
                ['raw_customer' => 'Nasabah Atom A', 'name' => 'Nasabah Atom A'],
                ['raw_customer' => 'Nasabah Atom B', 'name' => ''],
            ],
        ]);

        $this->assertDatabaseMissing('waste_customers', ['name' => 'Nasabah Atom A']);
        $this->assertDatabaseMissing('waste_customers', ['name' => 'Nasabah Atom B']);

        $staging = Cache::get("deposit_import_{$token}");
        $this->assertEquals('NEEDS MAPPING', $staging['rows'][0]['status']);
        $this->assertEquals('NEEDS MAPPING', $staging['rows'][1]['status']);
    }
}
