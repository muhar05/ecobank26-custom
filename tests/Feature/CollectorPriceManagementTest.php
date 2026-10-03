<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Collector;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Models\WastePrice;
use App\Models\WastePriceHistory;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CollectorPriceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected AdminDevice $device;

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
            'device_token' => 'collector-test-token-' . uniqid(),
            'device_name' => 'Admin PC',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);
    }

    private function actingAdmin()
    {
        return $this->actingAs($this->admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->device->device_token);
    }

    public function test_crud_agregator(): void
    {
        // 1. Create Collector
        $resStore = $this->actingAdmin()->post(route('bank-sampah.collectors.store'), [
            'name' => 'CV Sumber Makmur',
            'phone' => '081234567890',
            'address' => 'Jl. Daur Ulang No. 10',
        ]);

        $collector = Collector::where('name', 'CV Sumber Makmur')->first();
        $this->assertNotNull($collector);
        $resStore->assertRedirect(route('bank-sampah.collectors.show', $collector));

        // 2. Read / Show Collector
        $resShow = $this->actingAdmin()->get(route('bank-sampah.collectors.show', $collector));
        $resShow->assertStatus(200);
        $resShow->assertSee('CV Sumber Makmur', false);
        $resShow->assertSee('Harga Lapak', false);

        // 3. Update Collector
        $resUpdate = $this->actingAdmin()->put(route('bank-sampah.collectors.update', $collector), [
            'name' => 'PT Sumber Makmur Sejahtera',
            'phone' => '081299999999',
            'address' => 'Jl. Industri Baru No. 15',
        ]);
        $resUpdate->assertRedirect(route('bank-sampah.collectors.index'));
        $this->assertEquals('PT Sumber Makmur Sejahtera', $collector->fresh()->name);

        // 4. Delete Collector
        $resDelete = $this->actingAdmin()->delete(route('bank-sampah.collectors.destroy', $collector));
        $resDelete->assertRedirect(route('bank-sampah.collectors.index'));
        $this->assertSoftDeleted('collectors', ['id' => $collector->id]);
    }

    public function test_tambah_harga_agregator_dan_mapping_material(): void
    {
        $collector = Collector::create(['name' => 'Agregator Barokah']);
        $cat = WasteCategory::create(['name' => 'Kardus Tebal', 'unit' => 'kg', 'member_price' => 2000]);

        $res = $this->actingAdmin()->post(route('bank-sampah.collectors.prices.store', $collector), [
            'waste_category_id' => $cat->id,
            'collector_price' => 2600,
        ]);

        $res->assertSessionHas('success');

        $price = WastePrice::where('collector_id', $collector->id)
            ->where('waste_category_id', $cat->id)
            ->first();

        $this->assertNotNull($price);
        $this->assertEquals(2600, $price->collector_price);

        // Mapping relations
        $this->assertEquals($cat->id, $collector->wastePrices->first()->waste_category_id);
        $this->assertEquals('Kardus Tebal', $collector->wastePrices->first()->wasteCategory->name);
    }

    public function test_edit_harga_agregator(): void
    {
        $collector = Collector::create(['name' => 'Agregator Barokah']);
        $cat = WasteCategory::create(['name' => 'Besi Tua', 'unit' => 'kg', 'member_price' => 4000]);

        $price = WastePrice::create([
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => 4500,
        ]);

        $res = $this->actingAdmin()->put(route('bank-sampah.collectors.prices.update', [$collector, $price]), [
            'collector_price' => 4800,
        ]);

        $res->assertSessionHas('success');
        $this->assertEquals(4800, $price->fresh()->collector_price);
    }

    public function test_satu_material_memiliki_harga_berbeda_pada_beberapa_agregator(): void
    {
        $collectorA = Collector::create(['name' => 'Agregator A']);
        $collectorB = Collector::create(['name' => 'Agregator B']);
        $catKardus = WasteCategory::create(['name' => 'Kardus', 'unit' => 'kg', 'member_price' => 2000]);

        // Agregator A sets 2500
        WastePrice::create([
            'collector_id' => $collectorA->id,
            'waste_category_id' => $catKardus->id,
            'collector_price' => 2500,
        ]);

        // Agregator B sets 2800
        WastePrice::create([
            'collector_id' => $collectorB->id,
            'waste_category_id' => $catKardus->id,
            'collector_price' => 2800,
        ]);

        $this->assertEquals(2500, WastePrice::where('collector_id', $collectorA->id)->where('waste_category_id', $catKardus->id)->value('collector_price'));
        $this->assertEquals(2800, WastePrice::where('collector_id', $collectorB->id)->where('waste_category_id', $catKardus->id)->value('collector_price'));
    }

    public function test_harga_pertama_kali_dibuat_menghasilkan_history(): void
    {
        $collector = Collector::create(['name' => 'Agregator Jaya']);
        $cat = WasteCategory::create(['name' => 'Plastik Bening', 'unit' => 'kg', 'member_price' => 3000]);

        $this->actingAdmin()->post(route('bank-sampah.collectors.prices.store', $collector), [
            'waste_category_id' => $cat->id,
            'collector_price' => 3500,
        ]);

        $history = WastePriceHistory::where('collector_id', $collector->id)
            ->where('waste_category_id', $cat->id)
            ->first();

        $this->assertNotNull($history);
        $this->assertNull($history->old_price);
        $this->assertEquals(3500, $history->new_price);
        $this->assertEquals('kg', $history->unit);
        $this->assertNotNull($history->changed_at);
    }

    public function test_perubahan_harga_menghasilkan_old_price_ke_new_price(): void
    {
        $collector = Collector::create(['name' => 'Agregator Sejahtera']);
        $cat = WasteCategory::create(['name' => 'Tembaga Super', 'unit' => 'kg', 'member_price' => 80000]);

        $price = WastePrice::create([
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => 85000,
        ]);

        // Initial history
        WastePriceHistory::create([
            'waste_price_id' => $price->id,
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'old_price' => null,
            'new_price' => 85000,
            'unit' => 'kg',
            'changed_at' => now()->subDay(),
        ]);

        // Update price to 92000
        $this->actingAdmin()->put(route('bank-sampah.collectors.prices.update', [$collector, $price]), [
            'collector_price' => 92000,
        ]);

        $latestHistory = WastePriceHistory::where('waste_price_id', $price->id)
            ->latest('changed_at')
            ->first();

        $this->assertNotNull($latestHistory);
        $this->assertEquals(85000, $latestHistory->old_price);
        $this->assertEquals(92000, $latestHistory->new_price);
        $this->assertEquals('kg', $latestHistory->unit);
    }

    public function test_save_harga_yang_sama_tidak_membuat_history_baru(): void
    {
        $collector = Collector::create(['name' => 'Agregator Sentosa']);
        $cat = WasteCategory::create(['name' => 'Kertas Koran', 'unit' => 'kg', 'member_price' => 1500]);

        $price = WastePrice::create([
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => 2000,
        ]);

        WastePriceHistory::create([
            'waste_price_id' => $price->id,
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'old_price' => null,
            'new_price' => 2000,
            'unit' => 'kg',
            'changed_at' => now(),
        ]);

        $this->assertCount(1, WastePriceHistory::where('waste_price_id', $price->id)->get());

        // Save again with same price: 2000
        $this->actingAdmin()->put(route('bank-sampah.collectors.prices.update', [$collector, $price]), [
            'collector_price' => 2000,
        ]);

        // MUST NOT create a new history record
        $this->assertCount(1, WastePriceHistory::where('waste_price_id', $price->id)->get());
    }

    public function test_modified_at_berubah_ketika_harga_berubah(): void
    {
        $collector = Collector::create(['name' => 'Agregator Amanah']);
        $cat = WasteCategory::create(['name' => 'Kaleng Bekas', 'unit' => 'kg', 'member_price' => 10000]);

        $price = WastePrice::create([
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => 12000,
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subHours(5),
        ]);

        $oldModifiedAt = $price->fresh()->updated_at;

        // Sleep/Travel forward to ensure timestamp difference
        $this->travel(1)->hours();

        $this->actingAdmin()->put(route('bank-sampah.collectors.prices.update', [$collector, $price]), [
            'collector_price' => 13500,
        ]);

        $newModifiedAt = $price->fresh()->updated_at;

        $this->assertTrue($newModifiedAt->isAfter($oldModifiedAt), 'updated_at (Modified At) must be updated');
    }

    public function test_historical_sale_detail_tidak_berubah_ketika_harga_master_berubah(): void
    {
        $collector = Collector::create(['name' => 'Agregator Utama']);
        $cat = WasteCategory::create(['name' => 'Kardus Super', 'unit' => 'kg', 'member_price' => 2000]);

        $price = WastePrice::create([
            'collector_id' => $collector->id,
            'waste_category_id' => $cat->id,
            'collector_price' => 2500,
        ]);

        // Historical Sale on 10 Sep
        $sale = Sale::create([
            'collector_id' => $collector->id,
            'date' => '2026-09-10',
            'total_amount' => 125000,
        ]);

        $saleDetail = SaleDetail::create([
            'sale_id' => $sale->id,
            'waste_category_id' => $cat->id,
            'weight' => 50,
            'price_per_unit' => 2500, // Historical snapshot
            'member_price' => 2000,
            'subtotal' => 125000,
            'margin' => 25000,
        ]);

        // Master price updated on 12 Sep to 3500
        $this->actingAdmin()->put(route('bank-sampah.collectors.prices.update', [$collector, $price]), [
            'collector_price' => 3500,
        ]);

        // Assert master price is updated
        $this->assertEquals(3500, $price->fresh()->collector_price);

        // Assert historical SaleDetail snapshot remains strictly UNCHANGED
        $saleDetailFresh = $saleDetail->fresh();
        $this->assertEquals(2500, $saleDetailFresh->price_per_unit, 'Historical price_per_unit in SaleDetail must remain 2500');
        $this->assertEquals(2000, $saleDetailFresh->member_price, 'Historical member_price in SaleDetail must remain 2000');
        $this->assertEquals(125000, $saleDetailFresh->subtotal, 'Historical subtotal in SaleDetail must remain 125000');
        $this->assertEquals(25000, $saleDetailFresh->margin, 'Historical margin in SaleDetail must remain 25000');
    }
}
