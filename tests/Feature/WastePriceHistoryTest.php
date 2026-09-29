<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Collector;
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

class WastePriceHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected AdminDevice $adminDevice;
    protected Collector $collectorA;
    protected Collector $collectorB;
    protected WasteCategory $catKertas;
    protected WasteCategory $catBotol;

    protected function setUp(): void
    {
        parent::setUp();

        $permPrice = Permission::firstOrCreate(['name' => 'manage_waste_prices', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $role->givePermissionTo($permPrice);

        $this->adminUser = User::factory()->create([
            'phone' => '081234567890',
        ]);
        $this->adminUser->assignRole($role);

        // Setup active admin device
        $this->adminDevice = AdminDevice::create([
            'user_id'       => $this->adminUser->id,
            'device_token'  => 'device_token_for_history_tests',
            'device_name'   => 'PC Admin Gudang',
            'browser'       => 'Chrome',
            'platform'      => 'Windows',
            'is_revoked'    => false,
            'first_seen_at' => now(),
            'last_seen_at'  => now(),
        ]);

        $group = WasteCategoryGroup::create([
            'code'      => 'KRT',
            'name'      => 'Kertas',
            'is_active' => true,
        ]);

        $this->catKertas = WasteCategory::create([
            'code'                    => 'KRT.01',
            'name'                    => 'Kertas Jelek',
            'waste_category_group_id' => $group->id,
            'unit'                    => 'kg',
            'member_price'            => 1000,
        ]);

        $this->catBotol = WasteCategory::create([
            'code'                    => 'PLS.01',
            'name'                    => 'Botol Plastik',
            'unit'                    => 'kg',
            'member_price'            => 2000,
        ]);

        $this->collectorA = Collector::create([
            'name'  => 'Pengepul A',
            'phone' => '0811111111',
        ]);

        $this->collectorB = Collector::create([
            'name'  => 'Pengepul B',
            'phone' => '0822222222',
        ]);
    }

    /**
     * Helper to create real test Excel price files.
     */
    protected function createPriceExcelFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DATA IMPORT');
        $sheet->fromArray($rows, null, 'A1');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_price_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile(
            $tempPath,
            'daftar_harga.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    /**
     * Test 6: Manual price change creates a single history record with old and new price.
     */
    public function test_manual_price_change_creates_history(): void
    {
        // 1. Initial price: 1500
        $price = WastePrice::create([
            'collector_id'      => $this->collectorA->id,
            'waste_category_id' => $this->catKertas->id,
            'collector_price'   => 1500,
        ]);

        // 2. Change price to 1700 manually
        $response = $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->adminDevice->device_token)
            ->put(route('bank-sampah.waste-prices.update', $price), [
                'collector_id'      => $this->collectorA->id,
                'waste_category_id' => $this->catKertas->id,
                'collector_price'   => 1700,
            ]);

        $response->assertRedirect(route('bank-sampah.waste-prices.index'));

        // Current price updated
        $price->refresh();
        $this->assertEquals(1700, (float) $price->collector_price);

        // Exactly 1 history record created for the change
        $history = WastePriceHistory::where('waste_price_id', $price->id)->first();
        $this->assertNotNull($history);
        $this->assertEquals(1500, (float) $history->old_price);
        $this->assertEquals(1700, (float) $history->new_price);
        $this->assertEquals('MANUAL', $history->source);
        $this->assertEquals($this->adminUser->id, $history->changed_by_user_id);
        $this->assertEquals($this->adminDevice->id, $history->admin_device_id);
    }

    /**
     * Test 7: Manual price update with same price does NOT create history.
     */
    public function test_manual_price_no_change_does_not_create_history(): void
    {
        $price = WastePrice::create([
            'collector_id'      => $this->collectorA->id,
            'waste_category_id' => $this->catKertas->id,
            'collector_price'   => 1500,
        ]);

        // Submit same price: 1500
        $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->adminDevice->device_token)
            ->put(route('bank-sampah.waste-prices.update', $price), [
                'collector_id'      => $this->collectorA->id,
                'waste_category_id' => $this->catKertas->id,
                'collector_price'   => 1500,
            ]);

        $this->assertEquals(0, WastePriceHistory::where('waste_price_id', $price->id)->count());
    }

    /**
     * Test 8: Import price change creates history with source = IMPORT.
     */
    public function test_import_price_change_creates_history_with_source_import(): void
    {
        $price = WastePrice::create([
            'collector_id'      => $this->collectorA->id,
            'waste_category_id' => $this->catKertas->id,
            'collector_price'   => 1500,
        ]);

        $file = $this->createPriceExcelFile([
            ['Kode Grup', 'Nama Grup', 'Kode Kategori', 'Nama Kategori', 'Satuan', 'Harga Nasabah', 'Harga Agregator', 'Tanggal Berlaku'],
            ['KRT', 'Kertas', 'KRT.01', 'Kertas Jelek', 'kg', '1000', '1700', '2026-09-30'],
        ]);

        $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->adminDevice->device_token)
            ->post(route('bank-sampah.waste-prices.import.store'), [
                'collector_id' => $this->collectorA->id,
                'file'         => $file,
            ]);

        $price->refresh();
        $this->assertEquals(1700, (float) $price->collector_price);

        $history = WastePriceHistory::where('waste_price_id', $price->id)->first();
        $this->assertNotNull($history);
        $this->assertEquals(1500, (float) $history->old_price);
        $this->assertEquals(1700, (float) $history->new_price);
        $this->assertEquals('IMPORT', $history->source);
        $this->assertEquals($this->adminDevice->id, $history->admin_device_id);
    }

    /**
     * Test 9: Import with identical price does NOT create history.
     */
    public function test_import_price_no_change_does_not_create_history(): void
    {
        $price = WastePrice::create([
            'collector_id'      => $this->collectorA->id,
            'waste_category_id' => $this->catKertas->id,
            'collector_price'   => 1500,
        ]);

        $file = $this->createPriceExcelFile([
            ['Kode Grup', 'Nama Grup', 'Kode Kategori', 'Nama Kategori', 'Satuan', 'Harga Nasabah', 'Harga Agregator', 'Tanggal Berlaku'],
            ['KRT', 'Kertas', 'KRT.01', 'Kertas Jelek', 'kg', '1000', '1500', '2026-09-30'],
        ]);

        $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->adminDevice->device_token)
            ->post(route('bank-sampah.waste-prices.import.store'), [
                'collector_id' => $this->collectorA->id,
                'file'         => $file,
            ]);

        $this->assertEquals(0, WastePriceHistory::where('waste_price_id', $price->id)->count());
    }

    /**
     * Test 10: Initial price creation creates history with old_price = null.
     */
    public function test_initial_price_creation_records_history(): void
    {
        $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->adminDevice->device_token)
            ->post(route('bank-sampah.waste-prices.store'), [
                'collector_id'      => $this->collectorA->id,
                'waste_category_id' => $this->catBotol->id,
                'collector_price'   => 2500,
            ]);

        $price = WastePrice::where('collector_id', $this->collectorA->id)
            ->where('waste_category_id', $this->catBotol->id)
            ->first();

        $this->assertNotNull($price);
        $this->assertEquals(2500, (float) $price->collector_price);

        $history = WastePriceHistory::where('waste_price_id', $price->id)->first();
        $this->assertNotNull($history);
        $this->assertNull($history->old_price);
        $this->assertEquals(2500, (float) $history->new_price);
        $this->assertEquals('MANUAL', $history->source);
    }

    /**
     * Test 11: Historical immutability: Price changes over time maintain immutable records.
     */
    public function test_historical_records_are_immutable(): void
    {
        $price = WastePrice::create([
            'collector_id'      => $this->collectorA->id,
            'waste_category_id' => $this->catKertas->id,
            'collector_price'   => 1500,
        ]);

        // Step 1: Change to 1700
        $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->adminDevice->device_token)
            ->put(route('bank-sampah.waste-prices.update', $price), [
                'collector_id'      => $this->collectorA->id,
                'waste_category_id' => $this->catKertas->id,
                'collector_price'   => 1700,
            ]);

        // Step 2: Change to 1900
        $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->adminDevice->device_token)
            ->put(route('bank-sampah.waste-prices.update', $price), [
                'collector_id'      => $this->collectorA->id,
                'waste_category_id' => $this->catKertas->id,
                'collector_price'   => 1900,
            ]);

        $histories = WastePriceHistory::where('waste_price_id', $price->id)->oldest('id')->get();
        $this->assertCount(2, $histories);

        // History 1 must still be 1500 -> 1700
        $this->assertEquals(1500, (float) $histories[0]->old_price);
        $this->assertEquals(1700, (float) $histories[0]->new_price);

        // History 2 must be 1700 -> 1900
        $this->assertEquals(1700, (float) $histories[1]->old_price);
        $this->assertEquals(1900, (float) $histories[1]->new_price);
    }

    /**
     * Test 12: Device is linked to history and can resolve device name.
     */
    public function test_device_is_linked_to_history(): void
    {
        $price = WastePrice::create([
            'collector_id'      => $this->collectorA->id,
            'waste_category_id' => $this->catKertas->id,
            'collector_price'   => 1500,
        ]);

        $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->adminDevice->device_token)
            ->put(route('bank-sampah.waste-prices.update', $price), [
                'collector_id'      => $this->collectorA->id,
                'waste_category_id' => $this->catKertas->id,
                'collector_price'   => 1800,
            ]);

        $history = WastePriceHistory::where('waste_price_id', $price->id)->first();
        $this->assertNotNull($history->adminDevice);
        $this->assertEquals('PC Admin Gudang', $history->adminDevice->device_name);
    }

    /**
     * Test 13: Cross aggregator price histories are isolated and do not collide.
     */
    public function test_cross_aggregator_price_history_isolation(): void
    {
        // Aggregator A: 1500 -> 1700
        $priceA = WastePrice::create([
            'collector_id'      => $this->collectorA->id,
            'waste_category_id' => $this->catKertas->id,
            'collector_price'   => 1500,
        ]);

        $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->adminDevice->device_token)
            ->put(route('bank-sampah.waste-prices.update', $priceA), [
                'collector_id'      => $this->collectorA->id,
                'waste_category_id' => $this->catKertas->id,
                'collector_price'   => 1700,
            ]);

        // Aggregator B: 2000 -> 2200
        $priceB = WastePrice::create([
            'collector_id'      => $this->collectorB->id,
            'waste_category_id' => $this->catKertas->id,
            'collector_price'   => 2000,
        ]);

        $this->actingAs($this->adminUser)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->adminDevice->device_token)
            ->put(route('bank-sampah.waste-prices.update', $priceB), [
                'collector_id'      => $this->collectorB->id,
                'waste_category_id' => $this->catKertas->id,
                'collector_price'   => 2200,
            ]);

        $historyA = WastePriceHistory::where('collector_id', $this->collectorA->id)->first();
        $historyB = WastePriceHistory::where('collector_id', $this->collectorB->id)->first();

        $this->assertEquals(1500, (float) $historyA->old_price);
        $this->assertEquals(1700, (float) $historyA->new_price);

        $this->assertEquals(2000, (float) $historyB->old_price);
        $this->assertEquals(2200, (float) $historyB->new_price);
    }
}
