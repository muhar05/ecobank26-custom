<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteBankCashLedger;
use App\Models\WasteBankExpense;
use App\Services\BankSampahService;
use Database\Seeders\HistoricalCashLedgerSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalCashLedgerSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        User::factory()->create()->assignRole('admin_bank_sampah');
    }

    public function test_cash_ledger_seeder_creates_records_and_accurate_balance(): void
    {
        $this->seed(HistoricalCashLedgerSeeder::class);

        $this->assertEquals(57, WasteBankCashLedger::count());
        $this->assertEquals(41, WasteBankExpense::count());

        $service = app(BankSampahService::class);
        $this->assertEquals(162953.50, $service->getWasteBankBalance());

        // Check specific expense linking
        $firstExpense = WasteBankExpense::where('expense_code', 'EXP-H-0005')->first();
        $this->assertNotNull($firstExpense);
        $this->assertEquals(40000.00, (float) $firstExpense->amount);
        $this->assertStringContainsString('Bubur Kacang Ijo', $firstExpense->description);

        $linkedLedger = WasteBankCashLedger::where('reference_type', WasteBankExpense::class)
            ->where('reference_id', $firstExpense->id)
            ->first();

        $this->assertNotNull($linkedLedger);
        $this->assertEquals('out', $linkedLedger->type);
        $this->assertEquals(40000.00, (float) $linkedLedger->amount);
    }

    public function test_cash_ledger_seeder_is_idempotent(): void
    {
        $this->seed(HistoricalCashLedgerSeeder::class);
        $this->seed(HistoricalCashLedgerSeeder::class);

        $this->assertEquals(57, WasteBankCashLedger::count());
        $this->assertEquals(41, WasteBankExpense::count());

        $service = app(BankSampahService::class);
        $this->assertEquals(162953.50, $service->getWasteBankBalance());
    }

    public function test_sync_command_dry_run_and_execution(): void
    {
        // Dry-run
        $this->artisan('bank-sampah:sync-cash-ledger', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('[DRY-RUN]');

        $this->assertEquals(0, WasteBankCashLedger::count());
        $this->assertEquals(0, WasteBankExpense::count());

        // Full execution
        $this->artisan('bank-sampah:sync-cash-ledger')
            ->assertSuccessful()
            ->expectsOutputToContain('Saldo kas aktif saat ini: Rp 162.953,50');

        $this->assertEquals(57, WasteBankCashLedger::count());
        $this->assertEquals(41, WasteBankExpense::count());
    }
}
