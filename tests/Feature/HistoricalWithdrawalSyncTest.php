<?php

namespace Tests\Feature;

use App\Models\SavingsLedger;
use App\Models\WasteCustomer;
use App\Models\Withdrawal;
use App\Services\BankSampahService;
use Database\Seeders\HistoricalCustomerSeeder;
use Database\Seeders\HistoricalWithdrawalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalWithdrawalSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HistoricalCustomerSeeder::class);
    }

    public function test_withdrawal_seeder_creates_records_and_accurate_amounts(): void
    {
        $this->seed(HistoricalWithdrawalSeeder::class);

        $this->assertEquals(20, Withdrawal::count());
        $this->assertEquals(20, SavingsLedger::where("type", "debit")->count());

        $totalWithdrawn = (float) Withdrawal::sum('amount');
        $this->assertEquals(3238395.00, $totalWithdrawn);

        // Verify specific known customer withdrawal
        $betria = WasteCustomer::where('name', 'Bu Betria')->first();
        $this->assertNotNull($betria);

        $betriaWithdrawal = Withdrawal::where('waste_customer_id', $betria->id)->first();
        $this->assertNotNull($betriaWithdrawal);
        $this->assertEquals(300000.00, (float) $betriaWithdrawal->amount);
        $this->assertEquals('2026-09-12', $betriaWithdrawal->date->format('Y-m-d'));

        // Verify polymorphic linking in SavingsLedger
        $linkedLedger = SavingsLedger::where('reference_type', Withdrawal::class)
            ->where('reference_id', $betriaWithdrawal->id)
            ->first();

        $this->assertNotNull($linkedLedger);
        $this->assertEquals('debit', $linkedLedger->type);
        $this->assertEquals(300000.00, (float) $linkedLedger->amount);
    }

    public function test_withdrawal_seeder_is_idempotent(): void
    {
        $this->seed(HistoricalWithdrawalSeeder::class);
        $this->seed(HistoricalWithdrawalSeeder::class);

        $this->assertEquals(20, Withdrawal::count());
        $this->assertEquals(20, SavingsLedger::where("type", "debit")->count());
        $this->assertEquals(3238395.00, (float) Withdrawal::sum('amount'));
    }

    public function test_sync_command_dry_run_and_execution(): void
    {
        // Dry-run
        $this->artisan('bank-sampah:sync-withdrawals', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('[DRY-RUN]');

        $this->assertEquals(0, Withdrawal::count());
        $this->assertEquals(0, SavingsLedger::where('type', 'debit')->count());

        // Full execution
        $this->artisan('bank-sampah:sync-withdrawals')
            ->assertSuccessful()
            ->expectsOutputToContain('Data penarikan (Withdrawal): 20');

        $this->assertEquals(20, Withdrawal::count());
        $this->assertEquals(20, SavingsLedger::where("type", "debit")->count());
    }
}
