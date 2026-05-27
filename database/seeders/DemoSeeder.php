<?php

namespace Database\Seeders;

use App\Models\Collector;
use App\Models\FundCategory;
use App\Models\Member;
use App\Models\Sale;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use App\Services\BankSampahService;
use App\Services\CommunityCashService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(FundCategorySeeder::class);

        // Helper to normalize phone
        $normalizePhone = function (string $phone) {
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '620')) {
                $phone = '0' . substr($phone, 3);
            } elseif (str_starts_with($phone, '62')) {
                $phone = '0' . substr($phone, 2);
            }
            return $phone;
        };

        // Demo users using new phone credentials
        $adminRwPhone = $normalizePhone('620811111111');
        $adminRw = User::updateOrCreate(['phone' => $adminRwPhone], ['name' => 'Admin RW', 'email' => null, 'password' => Hash::make('password')]);
        $adminRw->syncRoles(['admin_rw']);

        $bendaharaPhone = $normalizePhone('620822222222');
        $bendahara = User::updateOrCreate(['phone' => $bendaharaPhone], ['name' => 'Bendahara RW', 'email' => null, 'password' => Hash::make('password')]);
        $bendahara->syncRoles(['bendahara_rw', 'bendahara']);

        $adminRtPhone = $normalizePhone('620833333333');
        $admin = User::updateOrCreate(['phone' => $adminRtPhone], ['name' => 'Admin RT', 'email' => null, 'password' => Hash::make('password')]);
        $admin->syncRoles(['admin_rt']);

        $bankSampahPhone = $normalizePhone('620844444444');
        $bankSampah = User::updateOrCreate(['phone' => $bankSampahPhone], ['name' => 'Admin Bank Sampah', 'email' => null, 'password' => Hash::make('password')]);
        $bankSampah->syncRoles(['admin_bank_sampah']);

        $wargaPhone = $normalizePhone('620855555555');
        $warga = User::updateOrCreate(['phone' => $wargaPhone], ['name' => 'Warga Demo', 'email' => null, 'password' => Hash::make('password')]);
        $warga->syncRoles(['warga']);

        // Setup RT, KK, and Warga Demo connection
        $rt = \App\Models\Rt::firstOrCreate(
            ['rt_number' => '001'],
            ['description' => 'RT 001 Kelurahan Eco']
        );

        $kk = \App\Models\Kk::firstOrCreate(
            ['kk_number' => '3201234567890001'],
            [
                'rt_id' => $rt->id,
                'family_head' => 'Warga Demo',
                'address' => 'Jl. Kebersihan No. 26',
                'phone' => '0855555555',
                'status' => 'active',
            ]
        );

        // Members
        $members = [
            ['member_code' => 'WRG001', 'name' => 'Budi Santoso', 'phone' => '081234567001', 'address' => 'Jl. Merdeka No. 1', 'kk_id' => $kk->id, 'relationship' => 'Anggota'],
            ['member_code' => 'WRG002', 'name' => 'Warga Demo', 'phone' => '0855555555', 'address' => 'Jl. Kebersihan No. 26', 'user_id' => $warga->id, 'kk_id' => $kk->id, 'relationship' => 'Kepala Keluarga'],
            ['member_code' => 'WRG003', 'name' => 'Andi Pratama', 'phone' => '081234567003', 'address' => 'Jl. Kenanga No. 5', 'kk_id' => $kk->id, 'relationship' => 'Anak'],
            ['member_code' => 'WRG004', 'name' => 'Dewi Lestari', 'phone' => '081234567004', 'address' => 'Jl. Kenanga No. 8', 'kk_id' => $kk->id, 'relationship' => 'Istri'],
            ['member_code' => 'WRG005', 'name' => 'Rudi Hermawan', 'phone' => '081234567005', 'address' => 'Jl. Anggrek No. 3'],
            ['member_code' => 'WRG006', 'name' => 'Rina Wati', 'phone' => '081234567006', 'address' => 'Jl. Anggrek No. 7'],
        ];

        foreach ($members as $m) {
            Member::updateOrCreate(['member_code' => $m['member_code']], $m);
        }

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

        // Community Cash demo
        $this->seedCommunityCash($bendahara->id);

        // Bank Sampah demo
        $this->seedBankSampah($collector->id);
    }

    private function seedCommunityCash(int $recordedBy): void
    {
        // Skip if data already exists
        if (\App\Models\CommunityContribution::count() > 5) {
            return;
        }

        $service = new CommunityCashService();
        $categories = FundCategory::all();
        $names = ['Budi Santoso', 'Siti Aminah', 'Andi Pratama', 'Dewi Lestari', 'Rudi Hermawan', 'Rina Wati'];

        // Contributions
        foreach ($categories as $cat) {
            foreach (array_slice($names, 0, 4) as $name) {
                $service->recordContribution([
                    'fund_category_id' => $cat->id,
                    'member_name' => $name,
                    'amount' => rand(2, 10) * 10000,
                    'date' => now()->subDays(rand(1, 60))->format('Y-m-d'),
                    'description' => "Iuran {$cat->name} - {$name}",
                    'recorded_by' => $recordedBy,
                ]);
            }
        }

        // Expenses (safe amounts)
        $expenses = [
            ['cat' => 'Dana Kebersihan', 'desc' => 'Beli sapu dan alat kebersihan', 'amount' => 75000],
            ['cat' => 'Dana Kematian', 'desc' => 'Santunan keluarga Pak Hadi', 'amount' => 200000],
            ['cat' => 'Dana Keamanan', 'desc' => 'Bayar petugas keamanan bulan ini', 'amount' => 150000],
            ['cat' => 'Dana Sampah', 'desc' => 'Beli kantong sampah', 'amount' => 50000],
            ['cat' => 'Dana Sosial', 'desc' => 'Bantuan warga sakit', 'amount' => 100000],
            ['cat' => 'Kas Umum', 'desc' => 'Cetak undangan rapat RT', 'amount' => 35000],
        ];

        foreach ($expenses as $exp) {
            $cat = FundCategory::where('name', $exp['cat'])->first();
            if ($cat) {
                try {
                    $service->recordExpense([
                        'fund_category_id' => $cat->id,
                        'amount' => $exp['amount'],
                        'date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                        'description' => $exp['desc'],
                        'recorded_by' => $recordedBy,
                    ]);
                } catch (\Exception $e) {
                    // Skip if insufficient balance
                }
            }
        }
    }

    private function seedBankSampah(int $collectorId): void
    {
        // Seed waste prices (idempotent)
        $this->seedWastePrices($collectorId);

        // Skip if data already exists beyond initial
        if (\App\Models\Deposit::count() > 10) {
            return;
        }

        $service = new BankSampahService();
        $members = Member::all();
        $categories = WasteCategory::all();

        if ($members->isEmpty() || $categories->isEmpty()) {
            return;
        }

        // Deposits for each member (use actual waste prices)
        $prices = WastePrice::where('collector_id', $collectorId)->get()->keyBy('waste_category_id');

        foreach ($members->take(4) as $member) {
            // Find or create active WasteCustomer for this member
            $customer = \App\Models\WasteCustomer::firstOrCreate(
                ['member_id' => $member->id],
                [
                    'user_id' => $member->user_id,
                    'customer_code' => \App\Models\WasteCustomer::generateNextCustomerCode(),
                    'name' => $member->name,
                    'phone' => $member->phone,
                    'address' => $member->address,
                    'status' => 'active',
                    'joined_at' => now(),
                ]
            );

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
        foreach ($members->take(3) as $member) {
            $customer = \App\Models\WasteCustomer::where('member_id', $member->id)->first();
            if (!$customer) {
                continue;
            }
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
