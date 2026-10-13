<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\SavingsLedger;
use Database\Seeders\HistoricalCustomerSeeder;
use Database\Seeders\HistoricalDepositSeeder;
use Database\Seeders\HistoricalMasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalDepositSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HistoricalMasterDataSeeder::class);
        $this->seed(HistoricalCustomerSeeder::class);
    }

    public function test_deposit_seeder_imports_all_historical_deposits(): void
    {
        $this->seed(HistoricalDepositSeeder::class);

        $this->assertEquals(343, Deposit::count());
        $this->assertEquals(2317, DepositDetail::count());
        $this->assertEquals(343, SavingsLedger::where('type', 'credit')->count());

        // 46 baris komposit disimpan sebagai historical exception.
        $this->assertEquals(46, DepositDetail::whereNull('waste_category_id')->count());
    }

    public function test_deposit_seeder_is_idempotent(): void
    {
        $this->seed(HistoricalDepositSeeder::class);
        $this->seed(HistoricalDepositSeeder::class);

        $this->assertEquals(343, Deposit::count());
        $this->assertEquals(2317, DepositDetail::count());
        $this->assertEquals(343, SavingsLedger::where('type', 'credit')->count());
    }
}
