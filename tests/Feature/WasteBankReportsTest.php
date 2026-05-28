<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SavingsLedger;
use App\Models\WasteBankExpense;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class WasteBankReportsTest extends TestCase
{
    use RefreshDatabase;

    protected $adminBankSampah;
    protected $adminRw;
    protected $adminRt;
    protected $warga;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->adminBankSampah = User::factory()->create();
        $this->adminBankSampah->assignRole('admin_bank_sampah');

        $this->adminRw = User::factory()->create();
        $this->adminRw->assignRole('admin_rw');

        $this->adminRt = User::factory()->create();
        $this->adminRt->assignRole('admin_rt');

        $this->warga = User::factory()->create();
        $this->warga->assignRole('warga');
    }

    public function test_unauthorized_users_are_forbidden_from_reports()
    {
        // Admin RT should be blocked
        $this->actingAs($this->adminRt)
            ->get(route('bank-sampah.reports.deposits'))
            ->assertStatus(403);

        // Warga should be blocked
        $this->actingAs($this->warga)
            ->get(route('bank-sampah.reports.deposits'))
            ->assertStatus(403);
    }

    public function test_authorized_users_can_access_reports()
    {
        $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.deposits'))
            ->assertStatus(200);

        $this->actingAs($this->adminRw)
            ->get(route('bank-sampah.reports.deposits'))
            ->assertStatus(200);
    }

    public function test_date_range_validation_exceeding_one_year()
    {
        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.deposits', [
                'start_date' => '2025-01-01',
                'end_date' => '2026-05-01'
            ]));

        $response->assertSessionHasErrors('date_range');
    }

    public function test_savings_journal_running_balance_accuracy()
    {
        $customer = WasteCustomer::create([
            'customer_code' => 'CUST-001',
            'name' => 'Nasabah A',
            'phone' => '08123',
            'address' => 'Jl. A'
        ]);

        // Truncate to make sure no other items
        SavingsLedger::truncate();

        // 1. First transaction: setoran +10.000
        SavingsLedger::create([
            'waste_customer_id' => $customer->id,
            'type' => 'credit',
            'amount' => 10000,
            'description' => 'Setoran awal',
            'created_at' => Carbon::now()->subDays(5)
        ]);

        // 2. Second transaction: penarikan -4.000
        SavingsLedger::create([
            'waste_customer_id' => $customer->id,
            'type' => 'debit',
            'amount' => 4000,
            'description' => 'Tarik dana',
            'created_at' => Carbon::now()->subDays(4)
        ]);

        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.savings-journal', [
                'waste_customer_id' => $customer->id,
                'start_date' => Carbon::now()->subDays(10)->toDateString(),
                'end_date' => Carbon::now()->toDateString()
            ]));

        $response->assertStatus(200);
        $ledgers = $response->viewData('ledgers');
        $this->assertCount(2, $ledgers);
        
        // Items are in reverse chronological order
        $this->assertEquals(6000, $ledgers[0]->running_balance); // Second (latest) = 6000
        $this->assertEquals(10000, $ledgers[1]->running_balance); // First = 10000
    }

    public function test_cashflow_report_calculation_sales_and_expenses()
    {
        $collector = Collector::create([
            'name' => 'Agregator A',
            'phone' => '0812'
        ]);

        // Create Sale (Inflow)
        Sale::create([
            'collector_id' => $collector->id,
            'date' => Carbon::now()->subDays(2),
            'total_amount' => 150000,
            'notes' => 'Penjualan kertas'
        ]);

        // Create Expense (Outflow)
        WasteBankExpense::create([
            'expense_code' => 'EXP-202605-0001',
            'description' => 'Beli air minum',
            'amount' => 50000,
            'expense_date' => Carbon::now()->subDays(1),
            'recorded_by' => $this->adminBankSampah->id,
        ]);

        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.cashflow', [
                'start_date' => Carbon::now()->subDays(5)->toDateString(),
                'end_date' => Carbon::now()->toDateString()
            ]));

        $response->assertStatus(200);
        $this->assertEquals(150000, $response->viewData('totalPemasukan'));
        $this->assertEquals(50000, $response->viewData('totalPengeluaran'));
        $this->assertEquals(100000, $response->viewData('saldoAkhir'));
    }

    public function test_print_views_render_properly()
    {
        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.deposits.print'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.sales.print'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.savings-journal.print'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.cashflow.print'));
        $response->assertStatus(200);
    }

    public function test_excel_exports_are_downloadable_and_logged()
    {
        // Clear activity log first
        ActivityLog::truncate();

        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.deposits.excel'));
        
        $response->assertStatus(200);
        $this->assertNotEmpty($response->headers->get('content-type'));

        // Check audit logs
        $this->assertDatabaseHas('activity_logs', [
            'event_type' => 'report.export',
            'description' => 'Export Excel Laporan Setoran Sampah'
        ]);
    }

    public function test_multi_page_running_balance_keeps_consistency()
    {
        $customer = WasteCustomer::create([
            'customer_code' => 'CUST-MP-01',
            'name' => 'Nasabah MultiPage',
            'phone' => '0812',
            'address' => 'Jl. MP'
        ]);

        SavingsLedger::truncate();

        // Let's create 30 transactions to trigger pagination (since limit is 25)
        for ($i = 1; $i <= 30; $i++) {
            SavingsLedger::create([
                'waste_customer_id' => $customer->id,
                'type' => 'credit',
                'amount' => 1000,
                'description' => 'Setoran ' . $i,
                'created_at' => Carbon::now()->subDays(40)->addMinutes($i)
            ]);
        }

        // View page 1 (which gets latest 25, i.e., setoran 6 to 30)
        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.savings-journal', [
                'waste_customer_id' => $customer->id,
                'page' => 1
            ]));

        $response->assertStatus(200);
        $ledgers = $response->viewData('ledgers');
        $this->assertCount(25, $ledgers);
        
        // Page 1 opening balance should carry forward from transaction 5 (balance = 5000)
        $this->assertEquals(5000, $response->viewData('pageOpeningBalance'));
        // Latest transaction (30th) should have running balance 30000
        $this->assertEquals(30000, $ledgers[0]->running_balance);

        // View page 2 (which gets first 5, i.e., setoran 1 to 5)
        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.savings-journal', [
                'waste_customer_id' => $customer->id,
                'page' => 2
            ]));

        $response->assertStatus(200);
        $ledgers2 = $response->viewData('ledgers');
        $this->assertCount(5, $ledgers2);
        
        // Page 2 opening balance should be 0 (since it's the start of chronological time)
        $this->assertEquals(0, $response->viewData('pageOpeningBalance'));
        // Earliest transaction (1st) should have running balance 1000
        $this->assertEquals(1000, $ledgers2[4]->running_balance);
        // 5th transaction should have running balance 5000
        $this->assertEquals(5000, $ledgers2[0]->running_balance);
    }

    public function test_mixed_legacy_linked_and_mandiri_ledger_resolution()
    {
        // Create user/member for legacy
        $member = \App\Models\Member::create([
            'member_code' => 'WRG-LEG-01',
            'name' => 'Warga Legacy',
            'phone' => '0812345678',
            'address' => 'Jl. Legacy No. 1'
        ]);

        $customer = WasteCustomer::create([
            'customer_code' => 'CUST-LNK-01',
            'name' => 'Nasabah Linked Warga',
            'member_id' => $member->id,
            'phone' => '08123',
            'address' => 'RT 01'
        ]);

        SavingsLedger::truncate();

        // 1. Legacy ledger (only member_id)
        SavingsLedger::create([
            'member_id' => $member->id,
            'waste_customer_id' => null,
            'type' => 'credit',
            'amount' => 15000,
            'description' => 'Legacy setoran warga',
            'created_at' => Carbon::now()->subDays(10)
        ]);

        // 2. Linked/new ledger (has both waste_customer_id and member_id)
        SavingsLedger::create([
            'member_id' => $member->id,
            'waste_customer_id' => $customer->id,
            'type' => 'credit',
            'amount' => 5000,
            'description' => 'Linked setoran baru',
            'created_at' => Carbon::now()->subDays(5)
        ]);

        // Get report for this hybrid customer
        $response = $this->actingAs($this->adminBankSampah)
            ->get(route('bank-sampah.reports.savings-journal', [
                'waste_customer_id' => $customer->id
            ]));

        $response->assertStatus(200);
        $ledgers = $response->viewData('ledgers');
        $this->assertCount(2, $ledgers);
        
        // Total balance should be 20,000 (legacy 15k + linked 5k)
        $this->assertEquals(20000, $response->viewData('totalSaldo'));
        // Running balance for latest should be 20000
        $this->assertEquals(20000, $ledgers[0]->running_balance);
        // Running balance for legacy should be 15000
        $this->assertEquals(15000, $ledgers[1]->running_balance);
    }
}
