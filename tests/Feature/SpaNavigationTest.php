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
        $perm1 = Permission::firstOrCreate(['name' => 'view_waste_bank', 'guard_name' => 'web']);
        $perm2 = Permission::firstOrCreate(['name' => 'manage_waste_customers', 'guard_name' => 'web']);
        $perm3 = Permission::firstOrCreate(['name' => 'manage_deposits', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $role->givePermissionTo([$perm1, $perm2, $perm3]);

        $admin = User::factory()->create();
        $admin->assignRole($role);

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
