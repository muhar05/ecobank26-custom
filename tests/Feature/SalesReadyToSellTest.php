<?php

namespace Tests\Feature;

use App\Http\Controllers\SaleController;
use Database\Seeders\HistoricalCustomerSeeder;
use Database\Seeders\HistoricalDepositSeeder;
use Database\Seeders\HistoricalMasterDataSeeder;
use Database\Seeders\HistoricalSalesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Regresi: stok "siap dijual" (deposit - terjual) di page /bank-sampah/sales
 * harus tepat 3 material dari data File B (Bank Sampah NEW.xlsx).
 */
class SalesReadyToSellTest extends TestCase
{
    use RefreshDatabase;

    public function test_ready_to_sell_shows_exactly_three_leftover_materials(): void
    {
        $this->seed(HistoricalMasterDataSeeder::class);
        $this->seed(HistoricalCustomerSeeder::class);
        $this->seed(HistoricalDepositSeeder::class);
        $this->seed(HistoricalSalesSeeder::class);

        $controller = app(SaleController::class);
        $response = $controller->index(Request::create('/bank-sampah/sales', 'GET'));
        $data = $response->getData();

        $readyToSell = $data['readyToSell'];

        $this->assertCount(3, $readyToSell);

        $byName = collect($readyToSell)->mapWithKeys(fn ($r) => [
            $r['category']->name => round((float) $r['remaining_weight'], 2),
        ]);

        $this->assertSame([
            'Alumunium' => 1.00,
            'Botol & Gelas Putih (Kotor)' => 5.00,
            'Yakult' => 0.20,
        ], $byName->all());
    }
}
