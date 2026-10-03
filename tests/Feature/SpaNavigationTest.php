<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\User;
use App\Models\WasteCustomer;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SpaNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminWithDevice(): array
    {
        $perms = [
            'view_waste_bank',
            'manage_waste_customers',
            'manage_deposits',
            'manage_sales',
            'manage_waste_prices',
            'manage_withdrawals',
            'view_waste_reports',
        ];
        foreach ($perms as $p) {
            $permission = Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $role->syncPermissions($perms);

        $roleRw = Role::firstOrCreate(['name' => 'admin_rw', 'guard_name' => 'web']);
        $roleRw->syncPermissions(['view_waste_bank', 'view_waste_reports']);

        $admin = User::factory()->create();
        $admin->assignRole([$role, $roleRw]);

        $device = AdminDevice::create([
            'user_id' => $admin->id,
            'device_token' => 'spa-nav-test-token',
            'device_name' => 'Test Laptop',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        return [$admin, $device];
    }

    public function test_normal_request_returns_full_html_shell(): void
    {
        [$admin, $device] = $this->createAdminWithDevice();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.dashboard'));

        $res->assertStatus(200);
        $res->assertSee('<!DOCTYPE html>', false);
        $res->assertSee('<body class="font-sans antialiased bg-background', false);
        $res->assertSee('id="app-content"', false);
        $res->assertSee('id="app-content-slot"', false);
        $res->assertSee('id="eco-nav-progress"', false);
        $res->assertSee('id="topbar-page-title"', false);
        $res->assertDontSee('id="eco-spa-fragment"', false);
    }

    public function test_spa_request_with_x_eco_nav_header_returns_fragment_only(): void
    {
        [$admin, $device] = $this->createAdminWithDevice();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->withHeaders(['X-Eco-Nav' => '1'])
            ->get(route('bank-sampah.dashboard'));

        $res->assertStatus(200);
        $res->assertSee('id="eco-spa-fragment"', false);
        $res->assertSee('data-title="Dashboard Bank Sampah"', false);
        $res->assertSee('id="app-content-slot"', false);
        // Shell must NOT be returned in fragment
        $res->assertDontSee('<!DOCTYPE html>', false);
        $res->assertDontSee('<body', false);
        $res->assertDontSee('id="eco-nav-progress"', false);
    }

    public function test_spa_navigation_to_customers_returns_customer_fragment(): void
    {
        [$admin, $device] = $this->createAdminWithDevice();
        WasteCustomer::create([
            'name' => 'Budi Santoso',
            'customer_code' => 'NSB-000099',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->withHeaders(['X-Eco-Nav' => '1'])
            ->get(route('bank-sampah.customers.index'));

        $res->assertStatus(200);
        $res->assertSee('id="eco-spa-fragment"', false);
        $res->assertSee('data-title="Data Nasabah Bank Sampah"', false);
        $res->assertSee('Budi Santoso', false);
        $res->assertSee('NSB-000099', false);
        $res->assertDontSee('<!DOCTYPE html>', false);
    }

    public function test_spa_navigation_to_deposits_returns_fragment(): void
    {
        [$admin, $device] = $this->createAdminWithDevice();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->withHeaders(['X-Eco-Nav' => '1'])
            ->get(route('bank-sampah.deposits.index'));

        $res->assertStatus(200);
        $res->assertSee('id="eco-spa-fragment"', false);
        $res->assertSee('id="app-content-slot"', false);
        $res->assertDontSee('<!DOCTYPE html>', false);
        $res->assertDontSee('id="eco-nav-progress"', false);
    }

    public function test_spa_navigation_to_sales_returns_fragment(): void
    {
        [$admin, $device] = $this->createAdminWithDevice();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->withHeaders(['X-Eco-Nav' => '1'])
            ->get(route('bank-sampah.sales.index'));

        $res->assertStatus(200);
        $res->assertSee('id="eco-spa-fragment"', false);
        $res->assertSee('id="app-content-slot"', false);
        $res->assertDontSee('<!DOCTYPE html>', false);
    }

    public function test_spa_navigation_across_other_modules_returns_fragment(): void
    {
        [$admin, $device] = $this->createAdminWithDevice();

        $routesToTest = [
            route('bank-sampah.waste-prices.index'),
            route('bank-sampah.waste-categories.index'),
            route('bank-sampah.collectors.index'),
            route('bank-sampah.withdrawals.index'),
            route('bank-sampah.expenses.index'),
            route('bank-sampah.savings'),
            route('bank-sampah.cash-report'),
            route('bank-sampah.devices.index'),
            route('admin.audit-logs'),
            route('profile.edit'),
        ];

        foreach ($routesToTest as $url) {
            $res = $this->actingAs($admin)
                ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
                ->withHeaders(['X-Eco-Nav' => '1'])
                ->get($url);

            $res->assertStatus(200);
            $res->assertSee('id="eco-spa-fragment"', false);
            $res->assertSee('id="app-content-slot"', false);
            $res->assertDontSee('<!DOCTYPE html>', false);
            $res->assertDontSee('id="eco-nav-progress"', false);
        }
    }

    public function test_spa_request_includes_session_toasts_when_present(): void
    {
        [$admin, $device] = $this->createAdminWithDevice();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->withSession(['success' => 'Data setoran berhasil disimpan'])
            ->withHeaders(['X-Eco-Nav' => '1'])
            ->get(route('bank-sampah.dashboard'));

        $res->assertStatus(200);
        $res->assertSee('id="eco-spa-fragment"', false);
        $res->assertSee('data-toast-success="Data setoran berhasil disimpan"', false);
    }

    public function test_public_and_guest_routes_do_not_return_spa_fragment(): void
    {
        $resHome = $this->withHeaders(['X-Eco-Nav' => '1'])->get('/');
        $resHome->assertStatus(200);
        $resHome->assertDontSee('id="eco-spa-fragment"', false);

        $resCekSaldo = $this->withHeaders(['X-Eco-Nav' => '1'])->get(route('cek-saldo.index'));
        $resCekSaldo->assertStatus(200);
        $resCekSaldo->assertDontSee('id="eco-spa-fragment"', false);
    }

    public function test_unauthenticated_spa_request_still_redirects_to_login(): void
    {
        $res = $this->withHeaders(['X-Eco-Nav' => '1'])
            ->get(route('bank-sampah.dashboard'));

        // Security is preserved: Laravel redirect is unaffected
        $res->assertStatus(302);
        $res->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403_even_with_x_eco_nav(): void
    {
        // User with no permissions
        $user = User::factory()->create();
        $device = AdminDevice::create([
            'user_id' => $user->id,
            'device_token' => 'spa-nav-unauthorized-token',
            'device_name' => 'Test Laptop 2',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        $res = $this->actingAs($user)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->withHeaders(['X-Eco-Nav' => '1'])
            ->get(route('bank-sampah.customers.index'));

        // Authorization is strictly preserved: 403 Forbidden
        $res->assertStatus(403);
    }
}
