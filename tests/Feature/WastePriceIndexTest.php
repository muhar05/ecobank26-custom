<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Collector;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use App\Models\WastePrice;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WastePriceIndexTest extends TestCase
{
    use RefreshDatabase;

    private function getAsAdmin(string $url)
    {
        $perm = Permission::firstOrCreate(['name' => 'manage_waste_prices', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $role->givePermissionTo($perm);

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $device = AdminDevice::create([
            'user_id' => $admin->id,
            'device_token' => 'waste-price-index-token-' . uniqid(),
            'device_name' => 'Test Laptop',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        return $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get($url);
    }

    public function test_categories_without_prices_are_listed_with_dashes(): void
    {
        WasteCategory::create(['name' => 'Kardus Bersih', 'code' => 'KRT.01', 'unit' => 'kg', 'member_price' => 1200]);

        $response = $this->getAsAdmin(route('bank-sampah.waste-prices.index'));

        $response->assertStatus(200);
        $response->assertSee('Kardus Bersih', false);
        $response->assertSee('Rp 1.200', false);
        $response->assertSee('Tambah Harga', false);
        $response->assertDontSee('Belum ada data harga sampah yang ditambahkan.', false);
        $this->assertDatabaseCount('waste_prices', 0);
    }

    public function test_category_with_price_shows_aggregator_and_margin(): void
    {
        $category = WasteCategory::create(['name' => 'Botol Bersih', 'code' => 'PLS.01', 'unit' => 'kg', 'member_price' => 2800]);
        $collector = Collector::create(['name' => 'Pengepul Maju']);
        WastePrice::create(['waste_category_id' => $category->id, 'collector_id' => $collector->id, 'collector_price' => 3100]);

        $response = $this->getAsAdmin(route('bank-sampah.waste-prices.index'));

        $response->assertStatus(200);
        $response->assertSee('Botol Bersih', false);
        $response->assertSee('Pengepul Maju', false);
        $response->assertSee('Rp 2.800', false);
        $response->assertSee('Rp 3.100', false);
        $response->assertSee('Rp 300', false);
    }

    public function test_category_with_multiple_collectors_shows_each_price(): void
    {
        $category = WasteCategory::create(['name' => 'Kertas Campur', 'code' => 'KRT.02', 'unit' => 'kg', 'member_price' => 800]);
        $collectorA = Collector::create(['name' => 'Pengepul A']);
        $collectorB = Collector::create(['name' => 'Pengepul B']);
        WastePrice::create(['waste_category_id' => $category->id, 'collector_id' => $collectorA->id, 'collector_price' => 1000]);
        WastePrice::create(['waste_category_id' => $category->id, 'collector_id' => $collectorB->id, 'collector_price' => 1100]);

        $response = $this->getAsAdmin(route('bank-sampah.waste-prices.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengepul A', false);
        $response->assertSee('Pengepul B', false);
        $response->assertSee('Rp 1.000', false);
        $response->assertSee('Rp 1.100', false);
    }

    public function test_group_filter_and_search_still_work(): void
    {
        $group = WasteCategoryGroup::create(['code' => 'KRT', 'name' => 'Kertas', 'is_active' => true]);
        WasteCategory::create(['name' => 'Kardus Bersih', 'code' => 'KRT.01', 'unit' => 'kg', 'member_price' => 1200, 'waste_category_group_id' => $group->id]);
        WasteCategory::create(['name' => 'Botol Bersih', 'code' => 'PLS.01', 'unit' => 'kg', 'member_price' => 2800]);

        $filtered = $this->getAsAdmin(route('bank-sampah.waste-prices.index', ['waste_category_group_id' => $group->id]));
        $filtered->assertStatus(200);
        $filtered->assertSee('Kardus Bersih', false);
        $filtered->assertDontSee('Botol Bersih', false);

        $searched = $this->getAsAdmin(route('bank-sampah.waste-prices.index', ['search' => 'Botol']));
        $searched->assertStatus(200);
        $searched->assertSee('Botol Bersih', false);
        $searched->assertDontSee('Kardus Bersih', false);
    }
}
