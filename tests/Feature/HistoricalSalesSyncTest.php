<?php

namespace Tests\Feature;

use App\Models\Collector;
use App\Models\Sale;
use App\Models\SaleDetail;
use Database\Seeders\HistoricalMasterDataSeeder;
use Database\Seeders\HistoricalSalesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalSalesSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HistoricalMasterDataSeeder::class);
    }

    public function test_sales_seeder_creates_records_and_details(): void
    {
        $this->seed(HistoricalSalesSeeder::class);

        $this->assertEquals(12, Sale::count());
        $this->assertEquals(190, SaleDetail::count());

        $totalSubtotal = (float) SaleDetail::sum('subtotal');
        $this->assertGreaterThan(10000000, $totalSubtotal);

        // Verify historical exceptions (null waste_category_id and filled material_label)
        $exceptions = SaleDetail::whereNull('waste_category_id')->get();
        $this->assertNotEmpty($exceptions);

        foreach ($exceptions as $ex) {
            $this->assertNotNull($ex->material_label);
            $this->assertTrue(in_array($ex->material_label, [
                'Buku, majalah, brosur, koran',
                'Seng/Kawat',
            ]));
        }

        // Verify canonical sales have valid waste_category_id
        $canonical = SaleDetail::whereNotNull('waste_category_id')->first();
        $this->assertNotNull($canonical);
        $this->assertNotNull($canonical->wasteCategory);
        $this->assertGreaterThan(0, (float) $canonical->margin);
    }

    public function test_sales_seeder_is_idempotent(): void
    {
        $this->seed(HistoricalSalesSeeder::class);
        $this->seed(HistoricalSalesSeeder::class);

        $this->assertEquals(12, Sale::count());
        $this->assertEquals(190, SaleDetail::count());
    }

    public function test_sync_command_dry_run_and_execution(): void
    {
        // Dry-run
        $this->artisan('bank-sampah:sync-sales', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('[DRY-RUN]');

        $this->assertEquals(0, Sale::count());
        $this->assertEquals(0, SaleDetail::count());

        // Full execution
        $this->artisan('bank-sampah:sync-sales')
            ->assertSuccessful()
            ->expectsOutputToContain('Transaksi penjualan (Sale): 12');

        $this->assertEquals(12, Sale::count());
        $this->assertEquals(190, SaleDetail::count());
    }
}
