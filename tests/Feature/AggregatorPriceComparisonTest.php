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

class AggregatorPriceComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected AdminDevice $device;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        foreach (['view_waste_bank', 'manage_waste_prices'] as $p) {
            $perm = Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            $role->givePermissionTo($perm);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);

        $this->device = AdminDevice::create([
            'user_id' => $this->admin->id,
            'device_token' => 'aggregator-compare-test-' . uniqid(),
            'device_name' => 'Test Laptop',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);
    }

    private function getAsAdmin(string $url)
    {
        return $this->actingAs($this->admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $this->device->device_token)
            ->get($url);
    }

    public function test_aggregator_selector_and_comparison_views_render_data(): void
    {
        $group = WasteCategoryGroup::create(['name' => 'Plastik', 'code' => 'PLS', 'is_active' => true]);

        // Category 1: Both A and B have prices
        $cat1 = WasteCategory::create([
            'name' => 'Botol PET Bening',
            'code' => 'PLS.01',
            'unit' => 'kg',
            'member_price' => 3000,
            'waste_category_group_id' => $group->id,
        ]);

        // Category 2: Only A has price
        $cat2 = WasteCategory::create([
            'name' => 'Kardus Box Coklat',
            'code' => 'KRT.01',
            'unit' => 'kg',
            'member_price' => 1500,
        ]);

        // Category 3: Only B has price
        $cat3 = WasteCategory::create([
            'name' => 'Gelas Plastik Cup',
            'code' => 'PLS.02',
            'unit' => 'kg',
            'member_price' => 2000,
        ]);

        // Category 4: Neither has price
        $cat4 = WasteCategory::create([
            'name' => 'Minyak Jelantah',
            'code' => 'ORG.01',
            'unit' => 'liter',
            'member_price' => 5000,
        ]);

        $colA = Collector::create(['name' => 'KamiBox']);
        $colB = Collector::create(['name' => 'Pengepul Mitra Jaya']);

        // Prices:
        // Cat 1: A = 4000, B = 4500 (B is higher by 500)
        WastePrice::create(['waste_category_id' => $cat1->id, 'collector_id' => $colA->id, 'collector_price' => 4000]);
        WastePrice::create(['waste_category_id' => $cat1->id, 'collector_id' => $colB->id, 'collector_price' => 4500]);

        // Cat 2: A = 2000, B = null (Only A)
        WastePrice::create(['waste_category_id' => $cat2->id, 'collector_id' => $colA->id, 'collector_price' => 2000]);

        // Cat 3: A = null, B = 2600 (Only B)
        WastePrice::create(['waste_category_id' => $cat3->id, 'collector_id' => $colB->id, 'collector_price' => 2600]);

        // 1. Visit waste-prices index
        $res = $this->getAsAdmin(route('bank-sampah.waste-prices.index'));
        $res->assertStatus(200);

        // Header & Core Concepts
        $res->assertSee('Harga Agregator', false);
        $res->assertSee('Bandingkan dan kelola harga jual material ke setiap agregator.', false);
        $res->assertSee('Katalog per Agregator', false);
        $res->assertSee('Bandingkan Agregator', false);

        // Agregator A and B present in selector
        $res->assertSee('KamiBox', false);
        $res->assertSee('Pengepul Mitra Jaya', false);

        // Materials present
        $res->assertSee('Botol PET Bening', false);
        $res->assertSee('Kardus Box Coklat', false);
        $res->assertSee('Gelas Plastik Cup', false);
        $res->assertSee('Minyak Jelantah', false);

        // Formatted Prices rendered
        $res->assertSee('Rp 4.000', false);
        $res->assertSee('Rp 4.500', false);
        $res->assertSee('Rp 2.000', false);
        $res->assertSee('Rp 2.600', false);

        // Status badges & CTA
        $res->assertSee('Sudah Ada Harga', false);
        $res->assertSee('Belum Ada Harga', false);
        $res->assertSee('Tambah Harga', false);

        // Comparison mode elements
        $res->assertSee('Agregator A (Dasar)', false);
        $res->assertSee('Agregator B (Pembanding)', false);
        $res->assertSee('Material Dibandingkan', false);
        $res->assertSee('Selisih', false);
        $res->assertSee('Status Perbandingan', false);

        // 2. Querying with specific collector_id
        $resColA = $this->getAsAdmin(route('bank-sampah.waste-prices.index', ['collector_id' => $colA->id]));
        $resColA->assertStatus(200);
        $resColA->assertSee('KamiBox', false);

        // 3. Querying with search
        $resSearch = $this->getAsAdmin(route('bank-sampah.waste-prices.index', ['search' => 'Jelantah']));
        $resSearch->assertStatus(200);
        $resSearch->assertSee('Minyak Jelantah', false);
        $resSearch->assertDontSee('Botol PET Bening', false);
    }
}
