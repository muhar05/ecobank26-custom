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

        // Waste categories
        $wasteCategories = [
            ['name' => 'Botol Putih Bersih', 'unit' => 'kg'],
            ['name' => 'Botol Putih Kotor', 'unit' => 'kg'],
            ['name' => 'Kardus Bersih', 'unit' => 'kg'],
            ['name' => 'Gelas Plastik Bersih', 'unit' => 'kg'],
            ['name' => 'Kertas Campur', 'unit' => 'kg'],
        ];

        foreach ($wasteCategories as $wc) {
            WasteCategory::firstOrCreate(['name' => $wc['name']], $wc);
        }

        // Collector
        $collector = Collector::firstOrCreate(['name' => 'Bu Erta'], ['phone' => '081234567890', 'address' => 'Jl. Pasar No. 10']);

        // Bank Sampah demo
        $this->seedBankSampah($collector->id);
    }

    private function seedBankSampah(int $collectorId): void
    {
        // Seed waste prices (idempotent)
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

        // Create demo customers (no member linkage)
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

        $prices = WastePrice::where('collector_id', $collectorId)->get()->keyBy('waste_category_id');

        foreach ($customers as $customer) {
            for ($i = 0; $i < 3; $i++) {
                $cat = $categories->random();
                $weight = rand(10, 50) / 10; // 1.0 - 5.0 kg
                $memberPrice = $prices[$cat->id]->member_price ?? rand(2, 8) * 1000;

                $service->recordDeposit([
                    'waste_customer_id' => $customer->id,
                    'collector_id' => $collectorId,
                    'date' => now()->subDays(rand(1, 45))->format('Y-m-d'),
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

        // Withdrawals (small safe amounts)
        foreach ($customers as $customer) {
            try {
                $service->recordWithdrawal([
                    'waste_customer_id' => $customer->id,
                    'amount' => rand(1, 3) * 5000,
                    'date' => now()->subDays(rand(1, 15))->format('Y-m-d'),
                    'notes' => 'Penarikan saldo',
                ]);
            } catch (\Exception $e) {
                // Skip if insufficient balance or min deposit not met
            }
        }

        // Sales (idempotent: skip if already exist)
        $this->seedSales($collectorId, $categories);
    }

    private function seedWastePrices(int $collectorId): void
    {
        $prices = [
            ['name' => 'Botol Putih Bersih', 'member_price' => 2800, 'collector_price' => 3100],
            ['name' => 'Botol Putih Kotor', 'member_price' => 1500, 'collector_price' => 1800],
            ['name' => 'Kardus Bersih', 'member_price' => 1200, 'collector_price' => 1500],
            ['name' => 'Gelas Plastik Bersih', 'member_price' => 1800, 'collector_price' => 2200],
            ['name' => 'Kertas Campur', 'member_price' => 800, 'collector_price' => 1000],
        ];

        foreach ($prices as $p) {
            $category = WasteCategory::where('name', $p['name'])->first();
            if ($category) {
                WastePrice::updateOrCreate(
                    ['collector_id' => $collectorId, 'waste_category_id' => $category->id],
                    ['member_price' => $p['member_price'], 'collector_price' => $p['collector_price'], 'price_per_unit' => $p['member_price']]
                );
            }
        }
    }

    private function seedSales(int $collectorId, $categories): void
    {
        if (Sale::count() > 0) {
            return;
        }

        $service = new BankSampahService();
        $prices = WastePrice::where('collector_id', $collectorId)->get()->keyBy('waste_category_id');

        // Sale 1: 2 items
        $cat1 = $categories->firstWhere('name', 'Botol Putih Bersih');
        $cat2 = $categories->firstWhere('name', 'Kardus Bersih');

        if ($cat1 && $cat2) {
            $service->recordSale([
                'collector_id' => $collectorId,
                'date' => now()->subDays(10)->format('Y-m-d'),
                'notes' => 'Penjualan rutin minggu 1',
                'details' => [
                    ['waste_category_id' => $cat1->id, 'weight' => 8.5, 'price_per_unit' => $prices[$cat1->id]->collector_price ?? 3100],
                    ['waste_category_id' => $cat2->id, 'weight' => 12.0, 'price_per_unit' => $prices[$cat2->id]->collector_price ?? 1500],
                ],
            ]);
        }

        // Sale 2: 3 items
        $cat3 = $categories->firstWhere('name', 'Gelas Plastik Bersih');
        if ($cat1 && $cat2 && $cat3) {
            $service->recordSale([
                'collector_id' => $collectorId,
                'date' => now()->subDays(3)->format('Y-m-d'),
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