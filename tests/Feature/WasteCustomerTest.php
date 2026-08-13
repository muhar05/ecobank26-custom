<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WasteCustomerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminBankSampah;
    private User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Spatie Roles & Permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Create Admin Bank Sampah User
        $this->adminBankSampah = User::factory()->create();
        $this->adminBankSampah->assignRole('admin_bank_sampah');

        // Create Warga User
        $this->warga = User::factory()->create();
        $this->warga->assignRole('warga');
    }

    /**
     * Test Admin Bank Sampah can access customers index.
     */
    public function test_admin_bank_sampah_can_access_customers_index(): void
    {
        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.customers.index'));

        $response->assertStatus(200);
    }

    /**
     * Test Warga cannot access customers index.
     */
    public function test_warga_cannot_access_customers_index(): void
    {
        $response = $this->actingAs($this->warga)
            ->get(route('bank-sampah.customers.index'));

        $response->assertStatus(403);
    }

    /**
     * Test Admin Bank Sampah can create manual customer.
     */
    public function test_admin_bank_sampah_can_create_manual_customer(): void
    {
        $response = $this->actingAs($this->adminBankSampah)
            ->post(route('bank-sampah.customers.store'), [
                'mode' => 'manual',
                'name' => 'Manual Customer',
                'phone' => '089999999',
                'address' => 'Manual Street',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('bank-sampah.customers.index'));
        $this->assertDatabaseHas('waste_customers', [
            'name' => 'Manual Customer',
            'user_id' => null,
        ]);
    }

    /**
     * Test customer with transactions cannot be deleted.
     */
    public function test_customer_with_transactions_cannot_be_deleted(): void
    {
        $customer = WasteCustomer::create([
            'user_id' => null,
            'customer_code' => 'NSB002',
            'name' => 'Transaction Customer',
            'status' => 'active',
        ]);

        $collector = \App\Models\Collector::create([
            'name' => 'Test Collector',
        ]);

        // Create a deposit for this customer
        $customer->deposits()->create([
            'collector_id' => $collector->id,
            'date' => now(),
            'total_amount' => 50000,
        ]);

        // Attempt deletion
        $response = $this->actingAs($this->adminBankSampah)
            ->delete(route('bank-sampah.customers.destroy', $customer));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('waste_customers', [
            'id' => $customer->id,
        ]);
    }

    /**
     * Test creating a deposit for a manual customer (no member) now successfully works.
     */
    public function test_deposit_creation_for_manual_customer_succeeds(): void
    {
        $customer = WasteCustomer::create([
            'user_id' => null,
            'customer_code' => 'NSB102',
            'name' => 'Manual Customer',
            'status' => 'active',
        ]);

        $collector = \App\Models\Collector::create(['name' => 'Collector B']);
        $category = \App\Models\WasteCategory::create(['name' => 'Kertas', 'unit' => 'kg']);

        $response = $this->actingAs($this->adminBankSampah)
            ->post(route('bank-sampah.deposits.store'), [
                'waste_customer_id' => $customer->id,
                'collector_id' => $collector->id,
                'date' => date('Y-m-d'),
                'notes' => 'Test manual deposit',
                'details' => [
                    [
                        'waste_category_id' => $category->id,
                        'price_per_unit' => 1500,
                        'weight' => 10,
                    ]
                ]
            ]);

        $response->assertRedirect(route('bank-sampah.deposits.index'));

        // Verify write in database (waste_customer_id is set)
        $this->assertDatabaseHas('deposits', [
            'waste_customer_id' => $customer->id,
            'total_amount' => 15000,
        ]);

        $this->assertDatabaseHas('savings_ledgers', [
            'waste_customer_id' => $customer->id,
            'amount' => 15000,
        ]);
    }

    /**
     * Test manual customer can successfully withdraw savings when balance is sufficient.
     */
    public function test_withdrawal_creation_for_manual_customer_succeeds(): void
    {
        $customer = WasteCustomer::create([
            'user_id' => null,
            'customer_code' => 'NSB103',
            'name' => 'Manual Customer C',
            'status' => 'active',
        ]);

        $collector = \App\Models\Collector::create(['name' => 'Collector C']);
        $category = \App\Models\WasteCategory::create(['name' => 'Besi', 'unit' => 'kg']);

        // Give them 2 deposits first to satisfy the 2-deposit minimum withdrawal condition
        $customer->deposits()->create([
            'collector_id' => $collector->id,
            'date' => now(),
            'total_amount' => 30000,
        ]);
        $customer->savingsLedgers()->create([
            'type' => 'credit',
            'amount' => 30000,
            'description' => 'Deposit 1',
        ]);

        $customer->deposits()->create([
            'collector_id' => $collector->id,
            'date' => now(),
            'total_amount' => 20000,
        ]);
        $customer->savingsLedgers()->create([
            'type' => 'credit',
            'amount' => 20000,
            'description' => 'Deposit 2',
        ]);

        // Attempt withdrawal
        $response = $this->actingAs($this->adminBankSampah)
            ->post(route('bank-sampah.withdrawals.store'), [
                'waste_customer_id' => $customer->id,
                'amount' => 15000,
                'date' => date('Y-m-d'),
                'notes' => 'Manual withdraw test',
            ]);

        $response->assertRedirect(route('bank-sampah.withdrawals.index'));

        // Verify withdrawal record
        $this->assertDatabaseHas('withdrawals', [
            'waste_customer_id' => $customer->id,
            'amount' => 15000,
        ]);

        $this->assertDatabaseHas('savings_ledgers', [
            'waste_customer_id' => $customer->id,
            'type' => 'debit',
            'amount' => 15000,
        ]);
    }

    /**
     * Test warga with linked waste customer profile can access their savings dashboard and view correct balance.
     */
    public function test_linked_warga_can_access_savings_dashboard(): void
    {
        $customer = WasteCustomer::create([
            'user_id' => $this->warga->id,
            'customer_code' => 'NSB201',
            'name' => 'Warga Test',
            'status' => 'active',
        ]);

        $customer->savingsLedgers()->create([
            'type' => 'credit',
            'amount' => 45000,
            'description' => 'Setoran Awal',
        ]);

        $response = $this->actingAs($this->warga)
            ->get(route('warga.savings'));

        $response->assertStatus(200);
        $response->assertSee('Rp 45.000');
    }

    /**
     * Test warga with linked waste customer profile can access their savings history.
     */
    public function test_linked_warga_can_access_savings_history(): void
    {
        $customer = WasteCustomer::create([
            'user_id' => $this->warga->id,
            'customer_code' => 'NSB201',
            'name' => 'Warga Test',
            'status' => 'active',
        ]);

        $customer->savingsLedgers()->create([
            'type' => 'credit',
            'amount' => 45000,
            'description' => 'Setoran Awal',
        ]);

        $response = $this->actingAs($this->warga)
            ->get(route('warga.savings.history'));

        $response->assertStatus(200);
        $response->assertSee('Setoran Awal');
    }

    /**
     * Test warga without waste customer profile gets redirected or shown the empty state.
     */
    public function test_warga_without_waste_customer_profile_sees_empty_state(): void
    {
        $wargaWithoutProfile = User::factory()->create();
        $wargaWithoutProfile->assignRole('warga');

        $response = $this->actingAs($wargaWithoutProfile)
            ->get(route('warga.savings'));

        $response->assertStatus(200);
        $response->assertSee('Akun Belum Terhubung');
    }

    /**
     * Test consistency audit command outputs 100% health score under normal/consistent data conditions.
     */
    public function test_audit_normal_data_outputs_100_percent_health(): void
    {
        $customer = WasteCustomer::create([
            'user_id' => null,
            'customer_code' => 'NSB301',
            'name' => 'Audit Normal',
            'status' => 'active',
        ]);

        $this->artisan('bank-sampah:audit')
            ->assertExitCode(0)
            ->expectsOutputToContain('Health Score: 100%');
    }

    /**
     * Test audit command detects missing savings ledger (orphan transaction).
     */
    public function test_audit_detects_missing_ledger(): void
    {
        $customer = WasteCustomer::create([
            'user_id' => null,
            'customer_code' => 'NSB302',
            'name' => 'Audit Missing Ledger',
            'status' => 'active',
        ]);

        $collector = \App\Models\Collector::create(['name' => 'Collector D']);

        // Create a deposit with NO corresponding savings ledger record
        $customer->deposits()->create([
            'collector_id' => $collector->id,
            'date' => now(),
            'total_amount' => 12000,
        ]);

        // Health score should drop because of mismatch/missing ledger
        $this->artisan('bank-sampah:audit')
            ->assertExitCode(2)
            ->expectsOutputToContain('Balance Mismatches [CRT]: 1')
            ->expectsOutputToContain('Orphan Transactions[HI] : 1');
    }

    /**
     * Test audit command detects duplicate ledger record.
     */
    public function test_audit_detects_duplicate_ledger(): void
    {
        $customer = WasteCustomer::create([
            'user_id' => null,
            'customer_code' => 'NSB303',
            'name' => 'Audit Duplicate Ledger',
            'status' => 'active',
        ]);

        $collector = \App\Models\Collector::create(['name' => 'Collector E']);

        // Create one deposit
        $deposit = $customer->deposits()->create([
            'collector_id' => $collector->id,
            'date' => now(),
            'total_amount' => 15000,
        ]);

        // Create two matching ledgers for the same reference
        $customer->savingsLedgers()->create([
            'waste_customer_id' => $customer->id,
            'type' => 'credit',
            'amount' => 15000,
            'description' => 'Duplicate Setoran 1',
            'reference_type' => Deposit::class,
            'reference_id' => $deposit->id,
        ]);

        $customer->savingsLedgers()->create([
            'waste_customer_id' => $customer->id,
            'type' => 'credit',
            'amount' => 15000,
            'description' => 'Duplicate Setoran 2',
            'reference_type' => Deposit::class,
            'reference_id' => $deposit->id,
        ]);

        $this->artisan('bank-sampah:audit')
            ->assertExitCode(2)
            ->expectsOutputToContain('Duplicate Ledgers  [CRT]: 1');
    }

    /**
     * Test audit command detects legacy unmapped transaction returning exit code 1 (Warning).
     */
    public function test_audit_detects_legacy_unmapped_transaction_warning(): void
    {
        $collector = \App\Models\Collector::create(['name' => 'Collector G']);

        // Create a customer to ensure checker proceeds and checked > 0
        WasteCustomer::create([
            'user_id' => null,
            'customer_code' => 'NSB399',
            'name' => 'Audit Legacy Warning Target',
            'status' => 'active',
        ]);

        // Create a deposit without waste_customer_id
        \App\Models\Deposit::create([
            'collector_id' => $collector->id,
            'date' => now(),
            'total_amount' => 5000,
            'waste_customer_id' => null
        ]);

        $this->artisan('bank-sampah:audit')
            ->assertExitCode(1)
            ->expectsOutputToContain('Legacy Unmapped    [WRN]: 1');
    }

    /**
     * Test audit command works successfully with --json flag option.
     */
    public function test_audit_with_json_flag_outputs_valid_json(): void
    {
        $this->artisan('bank-sampah:audit', ['--json' => true])
            ->expectsOutputToContain('"health_score":');
    }

    /**
     * Test audit command with --summary-only flag excludes details.
     */
    public function test_audit_summary_only_excludes_details(): void
    {
        $this->artisan('bank-sampah:audit', ['--json' => true, '--summary-only' => true])
            ->expectsOutputToContain('"severity_summary":')
            ->doesntExpectOutput('"anomalies":');
    }

    /**
     * Test Admin Bank Sampah can access the monitoring dashboard.
     */
    public function test_admin_bank_sampah_can_access_monitoring_dashboard(): void
    {
        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.monitoring'));

        $response->assertStatus(200);
        $response->assertSee('Monitoring');
        $response->assertSee('Audit Konsistensi');
    }

    /**
     * Test Admin RW can access the monitoring dashboard.
     */
    public function test_admin_rw_can_access_monitoring_dashboard(): void
    {
        $adminRw = User::factory()->create();
        $adminRw->assignRole('admin_rw');

        $response = $this->actingAs($adminRw)
            ->get(route('bank-sampah.monitoring'));

        $response->assertStatus(200);
        $response->assertSee('Monitoring');
        $response->assertSee('Audit Konsistensi');
    }

    /**
     * Test Warga cannot access the monitoring dashboard.
     */
    public function test_warga_cannot_access_monitoring_dashboard(): void
    {
        $response = $this->actingAs($this->warga)
            ->get(route('bank-sampah.monitoring'));

        $response->assertStatus(403);
    }

    /**
     * Test log created on deposit.
     */
    public function test_log_created_on_deposit(): void
    {
        $customer = WasteCustomer::create([
            'user_id' => $this->warga->id,
            'customer_code' => 'NSB401',
            'name' => 'Warga Test',
            'status' => 'active',
        ]);

        $collector = \App\Models\Collector::create(['name' => 'Collector Log Test']);
        $category = \App\Models\WasteCategory::create(['name' => 'Kardus', 'unit' => 'kg']);

        // Perform deposit via controller
        $this->actingAs($this->adminBankSampah)
            ->post(route('bank-sampah.deposits.store'), [
                'waste_customer_id' => $customer->id,
                'collector_id' => $collector->id,
                'date' => date('Y-m-d'),
                'notes' => 'Test deposit log creation',
                'details' => [
                    [
                        'waste_category_id' => $category->id,
                        'price_per_unit' => 1000,
                        'weight' => 10,
                    ]
                ]
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'event_type' => 'deposit.create',
            'severity' => 'info',
        ]);
    }

    /**
     * Test log created on withdrawal.
     */
    public function test_log_created_on_withdrawal(): void
    {
        $customer = WasteCustomer::create([
            'user_id' => null,
            'customer_code' => 'NSB402',
            'name' => 'Withdrawal Log Nasabah',
            'status' => 'active',
        ]);

        $collector = \App\Models\Collector::create(['name' => 'Collector Log Test 2']);

        // Satisfy 2-deposit rule
        $customer->deposits()->create([
            'collector_id' => $collector->id,
            'date' => now(),
            'total_amount' => 50000,
        ]);
        $customer->savingsLedgers()->create([
            'type' => 'credit',
            'amount' => 50000,
            'description' => 'Deposit A',
        ]);

        $customer->deposits()->create([
            'collector_id' => $collector->id,
            'date' => now(),
            'total_amount' => 50000,
        ]);
        $customer->savingsLedgers()->create([
            'type' => 'credit',
            'amount' => 50000,
            'description' => 'Deposit B',
        ]);

        // Perform withdrawal
        $this->actingAs($this->adminBankSampah)
            ->post(route('bank-sampah.withdrawals.store'), [
                'waste_customer_id' => $customer->id,
                'amount' => 20000,
                'date' => date('Y-m-d'),
                'notes' => 'Withdrawal logging test',
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'event_type' => 'withdrawal.create',
            'severity' => 'info',
        ]);
    }

    /**
     * Test logging sanitization works recursively.
     */
    public function test_logging_sanitization_works(): void
    {
        $service = app(\App\Services\ActivityLogService::class);
        $payload = [
            'username' => 'testuser',
            'password' => 'secret123',
            'token' => 'jwt-token-999',
            'nested' => [
                'secret' => 'sensitive-nested',
                'ok_field' => 'visible'
            ]
        ];

        $sanitized = $service->sanitizePayload($payload);

        $this->assertEquals('[REDACTED]', $sanitized['password']);
        $this->assertEquals('[REDACTED]', $sanitized['token']);
        $this->assertEquals('[REDACTED]', $sanitized['nested']['secret']);
        $this->assertEquals('visible', $sanitized['nested']['ok_field']);
        $this->assertEquals('testuser', $sanitized['username']);
    }

    /**
     * Test Warga cannot access audit logs dashboard.
     */
    public function test_warga_cannot_access_audit_logs(): void
    {
        $response = $this->actingAs($this->warga)
            ->get(route('admin.audit-logs'));

        $response->assertStatus(403);
    }

    /**
     * Test Admin RW can access audit logs dashboard.
     */
    public function test_admin_rw_can_access_audit_logs(): void
    {
        $adminRw = User::factory()->create();
        $adminRw->assignRole('admin_rw');

        $response = $this->actingAs($adminRw)
            ->get(route('admin.audit-logs'));

        $response->assertStatus(200);
        $response->assertSee('Log Aktivitas');
    }

    /**
     * Test ActivityLog model is immutable (append-only).
     */
    public function test_activity_logs_are_immutable(): void
    {
        $log = \App\Models\ActivityLog::create([
            'event_type' => 'test.immutable',
            'description' => 'Should remain forever',
        ]);

        $this->expectException(\Exception::class);
        $log->update(['description' => 'Try to alter']);
    }
}
