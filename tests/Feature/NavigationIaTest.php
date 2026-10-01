<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\User;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NavigationIaTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminClient(): array
    {
        foreach (['view_waste_bank', 'manage_waste_customers', 'manage_deposits', 'manage_waste_prices', 'manage_sales', 'view_waste_reports'] as $permName) {
            $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web'])->givePermissionTo($perm);
        }
        $role = Role::where('name', 'admin_bank_sampah')->first();

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $device = AdminDevice::create([
            'user_id' => $admin->id,
            'device_token' => 'nav-ia-test-token-' . uniqid(),
            'device_name' => 'Test Laptop',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        return [$admin, $device];
    }

    private function getAsAdmin(string $route, array $headers = [])
    {
        [$admin, $device] = $this->createAdminClient();

        return $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->withHeaders($headers)
            ->get($route);
    }

    public function test_sidebar_uses_new_ia_grouping_with_master_data_submenu(): void
    {
        $res = $this->getAsAdmin(route('bank-sampah.dashboard'));
        $res->assertStatus(200);

        // New section hierarchy
        $res->assertSee('>Operasional<', false);
        $res->assertSee('>Nasabah<', false);
        $res->assertSee('>Laporan<', false);
        $res->assertSee('>Lainnya<', false);
        $res->assertSee('Master Data', false);
        $res->assertSee('data-master-toggle', false);

        // Old flat sections are gone
        $res->assertDontSee('>Transaksi<', false);
        $res->assertDontSee('>Data Master<', false);

        // All business labels preserved
        foreach (['Setoran Sampah', 'Penjualan', 'Penarikan Saldo', 'Pengeluaran Operasional', 'Data Nasabah', 'Saldo Nasabah', 'Kas Bank Sampah', 'Kategori Sampah', 'Agregator', 'Harga Sampah', 'Perangkat Saya'] as $label) {
            $res->assertSee($label, false);
        }
    }

    public function test_master_data_parent_and_child_share_active_state(): void
    {
        $res = $this->getAsAdmin(route('bank-sampah.waste-prices.index'));
        $res->assertStatus(200);

        // Submenu auto-expands server-side on child routes
        $res->assertSee('x-data="{ masterOpen: true }"', false);
    }

    public function test_plus_jakarta_sans_is_selfhosted_without_cdn(): void
    {
        $res = $this->getAsAdmin(route('bank-sampah.dashboard'));
        $res->assertStatus(200);
        $res->assertDontSee('fonts.bunny.net', false);

        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $cssFile = $manifest['resources/css/app.css']['file'];
        $css = file_get_contents(public_path('build/' . $cssFile));

        $this->assertStringContainsString('Plus Jakarta Sans', $css);
        foreach ([400, 500, 600, 700, 800] as $weight) {
            $this->assertStringContainsString("plus-jakarta-sans-{$weight}-latin", $css);
        }
    }

    public function test_spa_fragments_cover_setoran_nodes(): void
    {
        foreach (['bank-sampah.deposits.index' => route('bank-sampah.deposits.index'), 'bank-sampah.deposits.create' => route('bank-sampah.deposits.create')] as $name => $url) {
            $res = $this->getAsAdmin($url, ['X-Eco-Nav' => '1']);
            $res->assertStatus(200, "SPA fragment failed for {$name}");
            $res->assertSee('id="eco-spa-fragment"', false);
            $res->assertDontSee('<!DOCTYPE html>', false);
        }
    }

    public function test_contextual_links_connect_related_pages(): void
    {
        // Setoran index -> Data Nasabah
        $res = $this->getAsAdmin(route('bank-sampah.deposits.index'));
        $res->assertStatus(200);
        $res->assertSee(route('bank-sampah.customers.index'), false);

        // Penjualan index -> Agregator + Harga Sampah
        $res = $this->getAsAdmin(route('bank-sampah.sales.index'));
        $res->assertStatus(200);
        $res->assertSee(route('bank-sampah.collectors.index'), false);
        $res->assertSee(route('bank-sampah.waste-prices.index'), false);
    }
}
