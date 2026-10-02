<?php

namespace Tests\Feature;

use App\Models\AdminDevice;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\SavingsLedger;
use App\Models\User;
use App\Models\WasteBankCashLedger;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use App\Models\WasteCustomer;
use App\Services\DeviceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardReferenceTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminClient(): array
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
            'device_token' => 'dash-test-device-token',
            'device_name' => 'Admin PC',
            'browser' => 'Chrome',
            'platform' => 'Darwin',
            'is_revoked' => false,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
        ]);

        return [$admin, $device];
    }

    public function test_dashboard_renders_with_official_logo_and_semantic_tokens(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.dashboard'));

        $res->assertStatus(200);

        // 1. Official Logo Requirement
        $res->assertSee('images/logo.png', false);

        // 1b. Typography: self-hosted Plus Jakarta Sans, zero external font CDN
        $res->assertDontSee('fonts.bunny.net', false);
        $res->assertDontSee('family=figtree:', false);
        $res->assertDontSee('family=inter:', false);

        // 1c. Phase 3.1: official hero illustration (PNG provided) present
        $res->assertSee('images/dashboard-bank-sampah-illustration.png', false);
        $res->assertSee('hero-illustration', false);
        $res->assertDontSee('dashboard-bank-sampah-illustration.svg', false);

        // 2. Action-First Hero
        $res->assertSee('Pusat Kendali Bank Sampah', false);
        $res->assertSee('Bank Sampah EcoBank026', false);
        $res->assertSee('Setoran Baru', false);
        $res->assertSee('Jual ke Agregator', false);

        // 3. Core KPI Titles
        $res->assertSee('Kas Bank Sampah', false);
        $res->assertSee('Total Saldo Nasabah', false);
        $res->assertSee('Nasabah Terdaftar', false);
        $res->assertSee('Total Setoran', false);

        // 4. Secondary Volume Metrics
        $res->assertSee('Total Penarikan Saldo', false);
        $res->assertSee('Total Penjualan Agregator', false);

        // 5. Honest Empty States (when database is fresh/empty)
        $res->assertSee('Belum Ada Arus Timbangan', false);
        $res->assertSee('Belum Ada Data Setoran', false);
        $res->assertSee('Belum ada riwayat mutasi tabungan nasabah.', false);
        $res->assertSee('Belum ada catatan arus kas operasional.', false);

        // 6. Anti-Slop Guardrails: Zero generic templates / delays / hardcoded slate
        $res->assertDontSee('rounded-[2rem]', false);
        $res->assertDontSee('blur-3xl', false);
        $res->assertDontSee('delay-[200ms]', false);
        $res->assertDontSee('bg-slate-900', false);
    }

    public function test_dashboard_renders_charts_and_activities_when_data_exists(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $group = WasteCategoryGroup::create(['name' => 'Plastik', 'code' => 'PLS']);
        $cat = WasteCategory::create(['name' => 'Botol PET', 'code' => 'PET', 'unit' => 'kg', 'member_price' => 2000, 'waste_category_group_id' => $group->id]);
        $cust = WasteCustomer::create(['name' => 'Pak Joko', 'customer_code' => 'NSB-000101', 'phone' => '0812345', 'status' => 'active']);

        // Record a deposit
        SavingsLedger::create([
            'waste_customer_id' => $cust->id,
            'type' => 'credit',
            'amount' => 50000,
            'description' => 'Setoran Sampah #001',
        ]);

        // Record cash
        WasteBankCashLedger::create([
            'type' => 'in',
            'amount' => 50000,
            'balance' => 50000,
            'description' => 'Penerimaan Kas Setoran',
            'date' => now()->toDateString(),
        ]);

        // Record a deposit detail so the per-material chart has real data
        $deposit = Deposit::create([
            'waste_customer_id' => $cust->id,
            'date' => now()->toDateString(),
            'total_amount' => 50000,
        ]);
        DepositDetail::create([
            'deposit_id' => $deposit->id,
            'waste_category_id' => $cat->id,
            'weight' => 25,
            'price_per_unit' => 2000,
            'subtotal' => 50000,
        ]);

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->get(route('bank-sampah.dashboard'));

        $res->assertStatus(200);

        // Chart container is rendered when data exists
        $res->assertSee('id="chart-bs-flow"', false);
        $res->assertSee('id="chart-bs-material"', false);
        $res->assertSee('Setoran per Material', false);
        $res->assertSee('Botol PET', false);
        $res->assertSee('25,00 kg', false);

        // Recent activity item is rendered
        $res->assertSee('Pak Joko', false);
        $res->assertSee('Setoran Sampah #001', false);
        $res->assertSee('Penerimaan Kas Setoran', false);
        $res->assertSee('Rp 50.000', false);
    }

    public function test_dashboard_spa_fragment_contains_no_micro_text_or_slate(): void
    {
        [$admin, $device] = $this->createAdminClient();

        $res = $this->actingAs($admin)
            ->withUnencryptedCookie(DeviceTrackingService::COOKIE_NAME, $device->device_token)
            ->withHeaders(['X-Eco-Nav' => '1'])
            ->get(route('bank-sampah.dashboard'));

        $res->assertStatus(200);
        $res->assertSee('id="eco-spa-fragment"', false);
        $res->assertDontSee('text-[10px]', false);
        $res->assertDontSee('text-[11px]', false);
        $res->assertDontSee('text-xs', false);
        $res->assertDontSee('bg-slate-900', false);
    }
}
