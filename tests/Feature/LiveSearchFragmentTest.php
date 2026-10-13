<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Memastikan setiap halaman dengan search:
 * 1. Form pencariannya memiliki atribut `data-live-search` (live search).
 * 2. Request fragmen (X-Eco-Nav) selalu memuat region `#table-section`
 *    (termasuk saat hasil kosong), sehingga penukaran region selalu bisa dilakukan.
 */
class LiveSearchFragmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
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
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $bank = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $bank->syncPermissions($perms);
        $rw = Role::firstOrCreate(['name' => 'admin_rw', 'guard_name' => 'web']);
        $rw->syncPermissions($perms);

        $user = User::factory()->create();
        $user->assignRole([$bank, $rw]);

        AdminDevice::create([
            'user_id' => $user->id,
            'device_token' => 'live-search-test-token',
            'device_name' => 'Test Laptop',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        return $user;
    }

    /**
     * @return array<string, array{0: string, 1: array<string, mixed>}>
     */
    public static function pages(): array
    {
        return [
            'deposits' => ['bank-sampah.deposits.index', []],
            'deposits search' => ['bank-sampah.deposits.index', ['search' => 'zzz']],
            'customers' => ['bank-sampah.customers.index', []],
            'customers search' => ['bank-sampah.customers.index', ['search' => 'zzz']],
            'savings' => ['bank-sampah.savings', []],
            'sales' => ['bank-sampah.sales.index', []],
            'collectors' => ['bank-sampah.collectors.index', []],
            'waste-categories' => ['bank-sampah.waste-categories.index', []],
            'waste-category-groups' => ['bank-sampah.waste-category-groups.index', []],
            'withdrawals' => ['bank-sampah.withdrawals.index', []],
            'waste-prices-history' => ['bank-sampah.waste-prices.history', []],
            'expenses' => ['bank-sampah.expenses.index', []],
            'audit-logs' => ['admin.audit-logs', []],
        ];
    }

    public function test_full_page_marks_forms_as_live_search(): void
    {
        $user = $this->admin();
        WasteCustomer::create([
            'customer_code' => 'NSB-000001',
            'name' => 'Budi Santoso',
            'status' => 'active',
        ]);
        WasteCategory::create(['code' => 'PLS.99', 'name' => 'Kardus Uji', 'unit' => 'kg', 'member_price' => 1000]);

        foreach (self::pages() as $label => [$route, $params]) {
            $res = $this->actingAs($user)
                ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, 'live-search-test-token')
                ->get(route($route, $params));

            $res->assertStatus(200, "Halaman {$label} gagal dimuat");
            $res->assertSee('data-live-search', false);
        }
    }

    public function test_spa_fragment_always_contains_table_section_region(): void
    {
        $user = $this->admin();
        WasteCustomer::create([
            'customer_code' => 'NSB-000001',
            'name' => 'Budi Santoso',
            'status' => 'active',
        ]);
        WasteCategory::create(['code' => 'PLS.99', 'name' => 'Kardus Uji', 'unit' => 'kg', 'member_price' => 1000]);

        foreach (self::pages() as $label => [$route, $params]) {
            $res = $this->actingAs($user)
                ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, 'live-search-test-token')
                ->withHeaders(['X-Eco-Nav' => '1'])
                ->get(route($route, $params));

            $res->assertStatus(200, "Fragmen {$label} gagal dimuat");
            $res->assertSee('id="eco-spa-fragment"', false);
            $res->assertSee('id="table-section"', false);
            $res->assertDontSee('<!DOCTYPE', false);
        }
    }
}
