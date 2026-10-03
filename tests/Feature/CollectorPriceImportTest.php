<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Collector;
use App\Models\MaterialAlias;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use App\Models\WastePrice;
use App\Models\WastePriceHistory;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CollectorPriceImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected AdminDevice $device;
    protected Collector $collectorA;
    protected Collector $collectorB;
    protected WasteCategory $catKardus;
    protected WasteCategory $catBesi;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [
            'view_waste_bank',
            'manage_waste_prices',
            'manage_sales',
            'manage_deposits',
        ];

        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        foreach ($permissions as $p) {
            $perm = Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            $role->givePermissionTo($perm);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);

        $this->device = AdminDevice::create([
            'user_id' => $this->admin->id,
            'device_token' => 'collector-import-token-' . uniqid(),
            'device_name' => 'Admin PC',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        $this->collectorA = Collector::create(['name' => 'PT Daur Mulia', 'phone' => '0811111111']);
        $this->collectorB = Collector::create(['name' => 'CV Sumber Berkah', 'phone' => '0822222222']);

        $this->catKardus = WasteCategory::create(['name' => 'Kardus Bekas', 'unit' => 'kg', 'member_price' => 2000]);
        $this->catBesi = WasteCategory::create(['name' => 'Besi Tua', 'unit' => 'kg', 'member_price' => 4000]);
    }

    private function actingAdmin()
    {
        return $this->actingAs($this->admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->device->device_token);
    }

    private function createPriceExcel(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($rows, null, 'A1');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_cpi_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile(
            $tempPath,
            'harga_lapak_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    public function test_upload_excel_valid_and_parses_into_review(): void
    {
        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Kardus Bekas', 2500],
            ['Besi Tua', 5000],
        ]);

        $response = $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertSee('Mapping', false);
        $response->assertSee('Review', false);
        $response->assertSee('Kardus Bekas', false);
        $response->assertSee('Besi Tua', false);
        $response->assertSee('2500', false);
        $response->assertSee('5000', false);
    }

    public function test_material_exact_match(): void
    {
        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Kardus Bekas', 2700],
        ]);

        $response = $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertSee('Harga Baru', false);
        $response->assertSee((string) $this->catKardus->id, false);
    }

    public function test_material_tidak_ditemukan_menjadi_perlu_mapping(): void
    {
        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Material Misterius X', 9900],
        ]);

        $response = $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertSee('Perlu Mapping', false);
        $response->assertSee('Material Misterius X', false);

        // Session contains token
        $sessionKey = collect(session()->all())->keys()->first(fn($k) => str_starts_with($k, 'collector_price_import_'));
        $this->assertNotNull($sessionKey);
        $token = str_replace('collector_price_import_', '', $sessionKey);

        // Attempting to commit without resolving mapping MUST fail
        $commitFail = $this->actingAdmin()->post(route('bank-sampah.collectors.import.commit', $this->collectorA), [
            'token' => $token,
            'mappings' => [0 => ''], // empty mapping
        ]);

        $commitFail->assertSessionHasErrors('commit');
        $this->assertCount(0, WastePrice::all());
    }

    public function test_tidak_auto_create_material(): void
    {
        $initialCategoryCount = WasteCategory::count();

        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Bahan Baru Asing', 8000],
        ]);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        // WasteCategory count MUST NOT increase
        $this->assertEquals($initialCategoryCount, WasteCategory::count());
    }

    public function test_harga_baru_dan_unit_mengikuti_master_material(): void
    {
        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Kardus Bekas', 2600],
        ]);

        $resParse = $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $sessionKey = collect(session()->all())->keys()->first(fn($k) => str_starts_with($k, 'collector_price_import_'));
        $token = str_replace('collector_price_import_', '', $sessionKey);

        $resCommit = $this->actingAdmin()->post(route('bank-sampah.collectors.import.commit', $this->collectorA), [
            'token' => $token,
            'mappings' => [0 => $this->catKardus->id],
        ]);

        $resCommit->assertRedirect(route('bank-sampah.collectors.show', $this->collectorA));

        $price = WastePrice::where('collector_id', $this->collectorA->id)
            ->where('waste_category_id', $this->catKardus->id)
            ->first();

        $this->assertNotNull($price);
        $this->assertEquals(2600, $price->collector_price);

        // WastePriceHistory must have old_price = null, new_price = 2600, unit = kg
        $history = WastePriceHistory::where('waste_price_id', $price->id)->first();
        $this->assertNotNull($history);
        $this->assertNull($history->old_price);
        $this->assertEquals(2600, $history->new_price);
        $this->assertEquals('kg', $history->unit);
    }

    public function test_harga_berubah_menghasilkan_history_old_price_ke_new_price(): void
    {
        // Initial price 4500 for Besi Tua
        $existing = WastePrice::create([
            'collector_id' => $this->collectorA->id,
            'waste_category_id' => $this->catBesi->id,
            'collector_price' => 4500,
        ]);

        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Besi Tua', 5200], // Changed from 4500 to 5200
        ]);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $sessionKey = collect(session()->all())->keys()->first(fn($k) => str_starts_with($k, 'collector_price_import_'));
        $token = str_replace('collector_price_import_', '', $sessionKey);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.commit', $this->collectorA), [
            'token' => $token,
            'mappings' => [0 => $this->catBesi->id],
        ]);

        $this->assertEquals(5200, $existing->fresh()->collector_price);

        $history = WastePriceHistory::where('waste_price_id', $existing->id)
            ->latest('changed_at')
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals(4500, $history->old_price);
        $this->assertEquals(5200, $history->new_price);
        $this->assertEquals('kg', $history->unit);
    }

    public function test_harga_sama_tidak_membuat_history(): void
    {
        // Existing price 4500
        $existing = WastePrice::create([
            'collector_id' => $this->collectorA->id,
            'waste_category_id' => $this->catBesi->id,
            'collector_price' => 4500,
        ]);

        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Besi Tua', 4500], // Same price
        ]);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $sessionKey = collect(session()->all())->keys()->first(fn($k) => str_starts_with($k, 'collector_price_import_'));
        $token = str_replace('collector_price_import_', '', $sessionKey);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.commit', $this->collectorA), [
            'token' => $token,
            'mappings' => [0 => $this->catBesi->id],
        ]);

        // Zero history records created
        $this->assertCount(0, WastePriceHistory::where('waste_price_id', $existing->id)->get());
    }

    public function test_import_hanya_untuk_agregator_yang_sedang_diproses(): void
    {
        // Collector B already has price 3000 for Kardus
        $priceB = WastePrice::create([
            'collector_id' => $this->collectorB->id,
            'waste_category_id' => $this->catKardus->id,
            'collector_price' => 3000,
        ]);

        // Import for Collector A: Kardus 2500
        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Kardus Bekas', 2500],
        ]);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $sessionKey = collect(session()->all())->keys()->first(fn($k) => str_starts_with($k, 'collector_price_import_'));
        $token = str_replace('collector_price_import_', '', $sessionKey);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.commit', $this->collectorA), [
            'token' => $token,
            'mappings' => [0 => $this->catKardus->id],
        ]);

        // Collector A price is 2500
        $this->assertEquals(2500, WastePrice::where('collector_id', $this->collectorA->id)->where('waste_category_id', $this->catKardus->id)->value('collector_price'));

        // Collector B price MUST remain untouched at 3000
        $this->assertEquals(3000, $priceB->fresh()->collector_price);
    }

    public function test_atomic_commit(): void
    {
        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Kardus Bekas', 2500],
            ['Besi Tua', 5000],
        ]);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $sessionKey = collect(session()->all())->keys()->first(fn($k) => str_starts_with($k, 'collector_price_import_'));
        $token = str_replace('collector_price_import_', '', $sessionKey);

        // Row 1 has valid category, but row 2 has invalid category 99999
        $res = $this->actingAdmin()->post(route('bank-sampah.collectors.import.commit', $this->collectorA), [
            'token' => $token,
            'mappings' => [
                0 => $this->catKardus->id,
                1 => 99999, // Non-existent
            ],
        ]);

        $res->assertSessionHasErrors('commit');

        // Due to atomicity, NEITHER row should be committed
        $this->assertCount(0, WastePrice::where('collector_id', $this->collectorA->id)->get());
    }

    public function test_historical_sale_detail_tidak_berubah_saat_import_harga(): void
    {
        // Existing price 2500
        $price = WastePrice::create([
            'collector_id' => $this->collectorA->id,
            'waste_category_id' => $this->catKardus->id,
            'collector_price' => 2500,
        ]);

        // Historical Sale with snapshot 2500
        $sale = Sale::create([
            'collector_id' => $this->collectorA->id,
            'date' => '2026-09-15',
            'total_amount' => 125000,
        ]);
        $detail = SaleDetail::create([
            'sale_id' => $sale->id,
            'waste_category_id' => $this->catKardus->id,
            'weight' => 50,
            'price_per_unit' => 2500,
            'member_price' => 2000,
            'subtotal' => 125000,
            'margin' => 25000,
        ]);

        // Import new price 3500 for Collector A
        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Kardus Bekas', 3500],
        ]);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $sessionKey = collect(session()->all())->keys()->first(fn($k) => str_starts_with($k, 'collector_price_import_'));
        $token = str_replace('collector_price_import_', '', $sessionKey);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.commit', $this->collectorA), [
            'token' => $token,
            'mappings' => [0 => $this->catKardus->id],
        ]);

        // Master price updated to 3500
        $this->assertEquals(3500, $price->fresh()->collector_price);

        // Historical SaleDetail snapshot MUST REMAIN strictly 2500 and 125000
        $detailFresh = $detail->fresh();
        $this->assertEquals(2500, $detailFresh->price_per_unit);
        $this->assertEquals(2000, $detailFresh->member_price);
        $this->assertEquals(125000, $detailFresh->subtotal);
        $this->assertEquals(25000, $detailFresh->margin);
    }

    public function test_quick_add_creates_master_material_without_member_price(): void
    {
        $group = WasteCategoryGroup::create(['name' => 'Kertas', 'code' => 'KRT', 'is_active' => true]);

        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Kardus Super Premium', 9000],
        ]);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $sessionKey = collect(session()->all())->keys()->first(fn($k) => str_starts_with($k, 'collector_price_import_'));
        $token = str_replace('collector_price_import_', '', $sessionKey);

        // Even if a manipulated client sends member_price, it must be ignored
        $res = $this->actingAdmin()->postJson(route('bank-sampah.collectors.import.quick-add-category', $this->collectorA), [
            'token' => $token,
            'raw_name' => 'Kardus Super Premium',
            'name' => 'Kardus Premium',
            'waste_category_group_id' => $group->id,
            'unit' => 'kg',
            'member_price' => 9999,
        ]);

        $res->assertOk()->assertJsonPath('success', true);

        $catId = $res->json('category.id');
        $this->assertNotNull($catId);

        // Created in the SAME master used by Deposit & Sale, WITHOUT member_price input
        $this->assertDatabaseHas('waste_categories', [
            'id' => $catId,
            'name' => 'Kardus Premium',
            'unit' => 'kg',
            'waste_category_group_id' => $group->id,
        ]);

        // member_price stays at database default (0) — Excel price must NEVER leak into it
        $this->assertEquals(0, (float) WasteCategory::find($catId)->member_price);
        $this->assertNotEquals(9000, (float) WasteCategory::find($catId)->member_price);

        // Code auto-generated, alias scoped to this aggregator
        $this->assertNotEmpty(WasteCategory::find($catId)->code);
        $this->assertDatabaseHas('material_aliases', [
            'source_type' => MaterialAlias::SOURCE_AGGREGATOR,
            'source_id' => $this->collectorA->id,
            'waste_category_id' => $catId,
        ]);
    }

    public function test_quick_add_rejects_duplicate_existing_material(): void
    {
        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Kardus Bekas', 9000],
        ]);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $sessionKey = collect(session()->all())->keys()->first(fn($k) => str_starts_with($k, 'collector_price_import_'));
        $token = str_replace('collector_price_import_', '', $sessionKey);

        $countBefore = WasteCategory::count();

        $res = $this->actingAdmin()->postJson(route('bank-sampah.collectors.import.quick-add-category', $this->collectorA), [
            'token' => $token,
            'raw_name' => 'kardus bekas',
            'name' => 'Kardus Bekas',
            'waste_category_group_id' => null,
            'unit' => 'kg',
        ]);

        $res->assertStatus(422);
        $this->assertEquals($countBefore, WasteCategory::count());
    }

    public function test_quick_add_ignores_member_price_and_requires_valid_session(): void
    {
        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Material Aneh Sekali', 5000],
        ]);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        // Invalid session token -> rejected, nothing created
        $res = $this->actingAdmin()->postJson(route('bank-sampah.collectors.import.quick-add-category', $this->collectorA), [
            'token' => 'bogus-token',
            'raw_name' => 'Material Aneh Sekali',
            'name' => 'Material Aneh Sekali',
            'unit' => 'kg',
        ]);

        $res->assertStatus(419);
        $this->assertDatabaseMissing('waste_categories', ['name' => 'Material Aneh Sekali']);
    }

    public function test_full_flow_unknown_material_quick_add_then_commit(): void
    {
        $group = WasteCategoryGroup::create(['name' => 'Plastik', 'code' => 'PLS', 'is_active' => true]);

        $file = $this->createPriceExcel([
            ['Jenis Barang', 'Harga Lapak'],
            ['Botol Unik Langka', 7500],
        ]);

        $this->actingAdmin()->post(route('bank-sampah.collectors.import.parse', $this->collectorA), [
            'file' => $file,
        ]);

        $sessionKey = collect(session()->all())->keys()->first(fn($k) => str_starts_with($k, 'collector_price_import_'));
        $token = str_replace('collector_price_import_', '', $sessionKey);

        $quickAdd = $this->actingAdmin()->postJson(route('bank-sampah.collectors.import.quick-add-category', $this->collectorA), [
            'token' => $token,
            'raw_name' => 'Botol Unik Langka',
            'name' => 'Botol Unik Langka',
            'waste_category_group_id' => $group->id,
            'unit' => 'pcs',
        ]);

        $quickAdd->assertOk();
        $catId = $quickAdd->json('category.id');

        $commit = $this->actingAdmin()->post(route('bank-sampah.collectors.import.commit', $this->collectorA), [
            'token' => $token,
            'mappings' => [0 => $catId],
        ]);

        $commit->assertRedirect(route('bank-sampah.collectors.show', $this->collectorA));

        // Excel price lands ONLY in collector_price; member_price stays at default 0
        $this->assertDatabaseHas('waste_prices', [
            'collector_id' => $this->collectorA->id,
            'waste_category_id' => $catId,
            'collector_price' => 7500,
        ]);
        $this->assertEquals(0, (float) WasteCategory::find($catId)->member_price);
        $this->assertEquals('pcs', WasteCategory::find($catId)->unit);

        // History created: NULL -> new, per existing flow
        $price = WastePrice::where('collector_id', $this->collectorA->id)->where('waste_category_id', $catId)->first();
        $this->assertNotNull($price);
        $history = WastePriceHistory::where('waste_price_id', $price->id)->latest('id')->first();
        $this->assertNotNull($history);
        $this->assertNull($history->old_price);
        $this->assertEquals(7500, (float) $history->new_price);
    }
}
