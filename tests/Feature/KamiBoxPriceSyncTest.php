<?php

namespace Tests\Feature;

use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use App\Models\WastePriceHistory;
use Database\Seeders\HistoricalMasterDataSeeder;
use Database\Seeders\KamiBoxPriceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamiBoxPriceSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed master categories
        $this->seed(HistoricalMasterDataSeeder::class);
    }

    public function test_kamibox_price_seeder_creates_prices_and_histories(): void
    {
        $collector = Collector::firstOrCreate(['name' => 'KamiBox']);

        $this->seed(KamiBoxPriceSeeder::class);

        $pricesCount = WastePrice::where('collector_id', $collector->id)->count();
        $this->assertEquals(51, $pricesCount);

        $historyCount = WastePriceHistory::where('collector_id', $collector->id)->count();
        $this->assertEquals(51, $historyCount);

        // Verify specific known prices
        $kardus = WasteCategory::where('name', 'Kardus Baik/Kering')->first();
        $this->assertNotNull($kardus);

        $kardusPrice = WastePrice::where('collector_id', $collector->id)
            ->where('waste_category_id', $kardus->id)
            ->first();

        $this->assertNotNull($kardusPrice);
        $this->assertEquals(1800, (float) $kardusPrice->collector_price);

        $botolPutih = WasteCategory::where('name', 'Botol Putih Bersih')->first();
        $botolPrice = WastePrice::where('collector_id', $collector->id)
            ->where('waste_category_id', $botolPutih->id)
            ->first();
        $this->assertEquals(5000, (float) $botolPrice->collector_price);
    }

    public function test_kamibox_price_seeder_is_idempotent(): void
    {
        $collector = Collector::firstOrCreate(['name' => 'KamiBox']);

        // Run twice
        $this->seed(KamiBoxPriceSeeder::class);
        $this->seed(KamiBoxPriceSeeder::class);

        $this->assertEquals(51, WastePrice::where('collector_id', $collector->id)->count());
        $this->assertEquals(51, WastePriceHistory::where('collector_id', $collector->id)->count());
    }

    public function test_sync_kamibox_prices_command_dry_run_and_execution(): void
    {
        $collector = Collector::firstOrCreate(['name' => 'KamiBox']);

        // Dry run
        $this->artisan('bank-sampah:sync-kamibox-prices', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('[DRY-RUN]');

        $this->assertEquals(0, WastePrice::where('collector_id', $collector->id)->count());

        // Full execution
        $this->artisan('bank-sampah:sync-kamibox-prices')
            ->assertSuccessful()
            ->expectsOutputToContain('Total harga KamiBox di database: 51');

        $this->assertEquals(51, WastePrice::where('collector_id', $collector->id)->count());
    }
}
