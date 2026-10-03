<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\User;
use App\Models\WasteCustomer;
use App\Services\DepositImportService;
use App\Models\ImportHistory;
use Database\Seeders\BankSampahBaselineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BankSampahBaselineSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_baseline_seed_provides_minimum_masters_and_no_transactions(): void
    {
        $this->seed(BankSampahBaselineSeeder::class);

        $this->assertDatabaseCount('waste_category_groups', 7);
        $this->assertDatabaseCount('waste_categories', 5);
        $this->assertDatabaseCount('collectors', 1);
        $this->assertDatabaseCount('waste_prices', 5);

        $this->assertDatabaseHas('waste_categories', ['name' => 'Kardus Bersih', 'member_price' => 1200]);
        $this->assertDatabaseHas('app_settings', ['key' => 'app.bank_sampah_name']);

        foreach (['deposits', 'deposit_details', 'sales', 'sale_details', 'withdrawals', 'savings_ledgers', 'waste_bank_cash_ledgers', 'waste_bank_expenses', 'waste_customers'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_baseline_seed_is_idempotent(): void
    {
        $this->seed(BankSampahBaselineSeeder::class);
        $this->seed(BankSampahBaselineSeeder::class);

        $this->assertDatabaseCount('waste_category_groups', 7);
        $this->assertDatabaseCount('waste_categories', 5);
        $this->assertDatabaseCount('collectors', 1);
        $this->assertDatabaseCount('waste_prices', 5);
    }

    public function test_baseline_seed_preserves_existing_users(): void
    {
        $user = User::factory()->create();

        $this->seed(BankSampahBaselineSeeder::class);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_reset_command_clears_domain_but_preserves_users(): void
    {
        $user = User::factory()->create();

        // Seed baseline first to prove reset deletes masters instead of
        // re-seeding them.
        $this->seed(BankSampahBaselineSeeder::class);

        // Authorization baseline: role + permission assigned to the user,
        // plus an app setting the application depends on.
        $role = Role::firstOrCreate(['name' => 'admin_bank_sampah', 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'view_waste_bank', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);
        \App\Models\AppSetting::setValue('app.bank_sampah_name', 'EcoBank026', 'string', 'bank_sampah');

        $customer = WasteCustomer::create([
            'customer_code' => 'NSB-000001',
            'name' => 'Nasabah Sementara',
            'status' => 'active',
        ]);
        Deposit::create([
            'waste_customer_id' => $customer->id,
            'date' => '2026-09-28',
            'total_amount' => 15000,
            'notes' => 'Setoran sementara',
        ]);
        ImportHistory::create([
            'filename' => 'impor-lama.xlsx',
            'import_type' => 'setoran',
            'user_id' => $user->id,
        ]);

        $this->artisan('bank-sampah:reset', ['--force' => true])->assertSuccessful();

        // 1. Users preserved as-is (with id intact).
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseCount('users', 1);

        // 2. Roles, permissions, and their relations preserved.
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'admin_bank_sampah']);
        $this->assertDatabaseHas('permissions', ['id' => $permission->id, 'name' => 'view_waste_bank']);
        $this->assertDatabaseHas('model_has_roles', ['role_id' => $role->id, 'model_id' => $user->id]);
        $this->assertDatabaseHas('role_has_permissions', ['role_id' => $role->id, 'permission_id' => $permission->id]);

        // 3. App settings preserved.
        $this->assertDatabaseHas('app_settings', ['key' => 'app.bank_sampah_name']);

        // 4. Authorization still valid: role, direct check, and a
        // permission-gated page (previously 403 after reset wiped RBAC).
        $this->assertTrue($user->fresh()->hasRole('admin_bank_sampah'));
        $this->assertTrue($user->fresh()->can('view_waste_bank'));
        $this->actingAs($user)->get(route('bank-sampah.dashboard'))->assertOk();

        // 5. All Bank Sampah domain tables empty (no baseline re-seeding).
        foreach ([
            'sale_details', 'sales', 'deposit_details', 'deposits',
            'waste_bank_cash_ledgers', 'waste_bank_expenses', 'savings_ledgers',
            'withdrawals', 'waste_price_histories', 'waste_prices',
            'material_aliases', 'waste_categories', 'waste_category_groups',
            'collectors', 'waste_customers', 'admin_devices', 'activity_logs',
            'import_histories',
        ] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_reset_clears_deposit_import_fingerprint_but_preserves_users(): void
    {
        $user = User::factory()->create();
        $customer = WasteCustomer::create([
            'customer_code' => 'NSB-000001',
            'name' => 'Nasabah Sementara',
            'status' => 'active',
        ]);

        // Simulate a completed deposit import: fingerprint in deposit notes
        // (DepositImportService::commit format) + forever cache record.
        $fingerprint = md5('import-setoran-test');
        Deposit::create([
            'waste_customer_id' => $customer->id,
            'date' => '2026-09-28',
            'total_amount' => 15000,
            'notes' => 'Import Excel: catatan.xlsx (Sheet: 28-09-2026) [Batch: IMP-1] [FP: ' . $fingerprint . ']',
        ]);
        Cache::forever("deposit_import_fp_{$fingerprint}", true);

        // Simulate a leftover staging row in the database cache store.
        DB::table('cache')->insert([
            'key' => 'deposit_import_staging-leftover',
            'value' => 'x',
            'expiration' => now()->addHour()->getTimestamp(),
        ]);

        $service = app(DepositImportService::class);
        $this->assertTrue($service->isFingerprintImported($fingerprint));

        $this->artisan('bank-sampah:reset', ['--force' => true])->assertSuccessful();

        // Users preserved, import records gone, same file importable again.
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('deposits', 0);
        $this->assertDatabaseCount('import_histories', 0);
        $this->assertFalse($service->isFingerprintImported($fingerprint));
        $this->assertFalse(Cache::has("deposit_import_fp_{$fingerprint}"));
        $this->assertDatabaseMissing('cache', ['key' => 'deposit_import_staging-leftover']);

        // No baseline seeding after reset.
        $this->assertDatabaseCount('waste_categories', 0);
        $this->assertDatabaseCount('waste_category_groups', 0);
    }
}
