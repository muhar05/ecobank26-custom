<?php

namespace Database\Seeders;

use App\Models\Collector;
use App\Models\Deposit;
use App\Models\Sale;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Models\WastePrice;
use App\Services\BankSampahService;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        // Waste categories with master member_price
        $wasteCategories = [
            ['name' => 'Botol Putih Bersih', 'unit' => 'kg', 'member_price' => 2800],
            ['name' => 'Botol Putih Kotor', 'unit' => 'kg', 'member_price' => 1500],
            ['name' => 'Kardus Bersih', 'unit' => 'kg', 'member_price' => 1200],
            ['name' => 'Gelas Plastik Bersih', 'unit' => 'kg', 'member_price' => 1800],
            ['name' => 'Kertas Campur', 'unit' => 'kg', 'member_price' => 800],
        ];

        foreach ($wasteCategories as $wc) {
            WasteCategory::updateOrCreate(['name' => $wc['name']], $wc);
        }

        // Collector
        $collector = Collector::firstOrCreate(['name' => 'Bu Erta'], ['phone' => '081234567890', 'address' => 'Jl. Pasar No. 10']);

        // Bank Sampah demo
        $this->seedBankSampah($collector->id);
    }

    private function seedBankSampah(int $collectorId): void
    {
        // Seed waste prices (collector_price only)
        $this->seedWastePrices($collectorId);

        // Skip if data already exists beyond initial
        if (Deposit::count() > 10) {
            return;
        }

        $service = new BankSampahService();
        $categories = WasteCategory::all();

        if ($categories->isEmpty()) {
            return;
        }

        // Create demo customers
        $customerNames = [
            ['name' => 'Warga Demo', 'phone' => '0855555555'],
            ['name' => 'Budi Santoso', 'phone' => '081234567001'],
            ['name' => 'Andi Pratama', 'phone' => '081234567003'],
            ['name' => 'Dewi Lestari', 'phone' => '081234567004'],
        ];

        $customers = [];
        foreach ($customerNames as $c) {
            $customers[] = WasteCustomer::firstOrCreate(
                ['phone' => $c['phone']],
                [
                    'customer_code' => WasteCustomer::generateNextCustomerCode(),
                    'name' => $c['name'],
                    'address' => 'Jl. Kebersihan No. 26',
                    'status' => 'active',
                    'joined_at' => now(),
                ]
            );
        }

        // 1. Seed specific deposits for Sale dates so remaining_weight is available
        $saleDate1 = now()->subDays(10)->format('Y-m-d');
        $saleDate2 = now()->subDays(3)->format('Y-m-d');

        $catBotol = $categories->firstWhere('name', 'Botol Putih Bersih');
        $catKardus = $categories->firstWhere('name', 'Kardus Bersih');
        $catGelas = $categories->firstWhere('name', 'Gelas Plastik Bersih');

        // Deposits on saleDate1
        if ($catBotol && $catKardus) {
            $service->recordDeposit([
                'waste_customer_id' => $customers[0]->id,
                'date' => $saleDate1,
                'notes' => 'Setoran kardus dan botol',
                'details' => [
                    ['waste_category_id' => $catBotol->id, 'weight' => 15.0, 'price_per_unit' => (float)$catBotol->member_price, 'subtotal' => 15.0 * (float)$catBotol->member_price],
                    ['waste_category_id' => $catKardus->id, 'weight' => 20.0, 'price_per_unit' => (float)$catKardus->member_price, 'subtotal' => 20.0 * (float)$catKardus->member_price],
                ],
            ]);
        }

        // Deposits on saleDate2
        if ($catBotol && $catKardus && $catGelas) {
            $service->recordDeposit([
                'waste_customer_id' => $customers[1]->id,
                'date' => $saleDate2,
                'notes' => 'Setoran plastik dan kardus',
                'details' => [
                    ['waste_category_id' => $catBotol->id, 'weight' => 10.0, 'price_per_unit' => (float)$catBotol->member_price, 'subtotal' => 10.0 * (float)$catBotol->member_price],
                    ['waste_category_id' => $catGelas->id, 'weight' => 12.0, 'price_per_unit' => (float)$catGelas->member_price, 'subtotal' => 12.0 * (float)$catGelas->member_price],
                    ['waste_category_id' => $catKardus->id, 'weight' => 15.0, 'price_per_unit' => (float)$catKardus->member_price, 'subtotal' => 15.0 * (float)$catKardus->member_price],
                ],
            ]);
        }

        // 2. Additional general deposits for other customers
        foreach ($customers as $customer) {
            for ($i = 0; $i < 2; $i++) {
                $cat = $categories->random();
                $weight = rand(10, 50) / 10;
                $memberPrice = (float) $cat->member_price;

                $service->recordDeposit([
                    'waste_customer_id' => $customer->id,
                    'date' => now()->subDays(rand(1, 40))->format('Y-m-d'),
                    'notes' => "Setoran sampah {$cat->name}",
                    'details' => [
                        [
                            'waste_category_id' => $cat->id,
                            'weight' => $weight,
                            'price_per_unit' => $memberPrice,
                            'subtotal' => $weight * $memberPrice,
                        ],
                    ],
                ]);
            }
        }

        // Withdrawals
        foreach ($customers as $customer) {
            try {
                $service->recordWithdrawal([
                    'waste_customer_id' => $customer->id,
                    'amount' => rand(1, 2) * 5000,
                    'date' => now()->subDays(rand(1, 15))->format('Y-m-d'),
                    'notes' => 'Penarikan saldo tabungan',
                ]);
            } catch (\Exception $e) {
                // Skip if balance is insufficient
            }
        }

        // 3. Sales on the specific dates
        $this->seedSales($collectorId, $categories, $saleDate1, $saleDate2);
    }

    private function seedWastePrices(int $collectorId): void
    {
        $prices = [
            ['name' => 'Botol Putih Bersih', 'collector_price' => 3100],
            ['name' => 'Botol Putih Kotor', 'collector_price' => 1800],
            ['name' => 'Kardus Bersih', 'collector_price' => 1500],
            ['name' => 'Gelas Plastik Bersih', 'collector_price' => 2200],
            ['name' => 'Kertas Campur', 'collector_price' => 1000],
        ];

        foreach ($prices as $p) {
            $category = WasteCategory::where('name', $p['name'])->first();
            if ($category) {
                WastePrice::updateOrCreate(
                    ['collector_id' => $collectorId, 'waste_category_id' => $category->id],
                    ['collector_price' => $p['collector_price']]
                );
            }
        }
    }

    private function seedSales(int $collectorId, $categories, string $date1, string $date2): void
    {
        if (Sale::count() > 0) {
            return;
        }

        $service = new BankSampahService();
        $prices = WastePrice::where('collector_id', $collectorId)->get()->keyBy('waste_category_id');

        $cat1 = $categories->firstWhere('name', 'Botol Putih Bersih');
        $cat2 = $categories->firstWhere('name', 'Kardus Bersih');

        if ($cat1 && $cat2) {
            $service->recordSale([
                'collector_id' => $collectorId,
                'date' => $date1,
                'notes' => 'Penjualan rutin minggu 1',
                'details' => [
                    ['waste_category_id' => $cat1->id, 'weight' => 8.5, 'price_per_unit' => $prices[$cat1->id]->collector_price ?? 3100],
                    ['waste_category_id' => $cat2->id, 'weight' => 12.0, 'price_per_unit' => $prices[$cat2->id]->collector_price ?? 1500],
                ],
            ]);
        }

        $cat3 = $categories->firstWhere('name', 'Gelas Plastik Bersih');
        if ($cat1 && $cat2 && $cat3) {
            $service->recordSale([
                'collector_id' => $collectorId,
                'date' => $date2,
                'notes' => 'Penjualan rutin minggu 2',
                'details' => [
                    ['waste_category_id' => $cat1->id, 'weight' => 5.0, 'price_per_unit' => $prices[$cat1->id]->collector_price ?? 3100],
                    ['waste_category_id' => $cat3->id, 'weight' => 6.5, 'price_per_unit' => $prices[$cat3->id]->collector_price ?? 2200],
                    ['waste_category_id' => $cat2->id, 'weight' => 9.0, 'price_per_unit' => $prices[$cat2->id]->collector_price ?? 1500],
                ],
            ]);
        }
    }
}
