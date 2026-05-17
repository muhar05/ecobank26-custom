<?php

namespace Database\Seeders;

use App\Models\FundCategory;
use App\Models\User;
use App\Services\CommunityCashService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist
        $this->call(RolePermissionSeeder::class);
        $this->call(FundCategorySeeder::class);

        // Create demo users
        $admin = User::firstOrCreate(
            ['email' => 'adminrt@test.com'],
            ['name' => 'Admin RT', 'password' => Hash::make('password')]
        );
        $admin->syncRoles(['admin_rt']);

        $bendahara = User::firstOrCreate(
            ['email' => 'bendahara@test.com'],
            ['name' => 'Bendahara', 'password' => Hash::make('password')]
        );
        $bendahara->syncRoles(['bendahara']);

        $bankSampah = User::firstOrCreate(
            ['email' => 'banksampah@test.com'],
            ['name' => 'Admin Bank Sampah', 'password' => Hash::make('password')]
        );
        $bankSampah->syncRoles(['admin_bank_sampah']);

        $warga = User::firstOrCreate(
            ['email' => 'warga@test.com'],
            ['name' => 'Warga Biasa', 'password' => Hash::make('password')]
        );
        $warga->syncRoles(['warga']);

        // Sample transactions
        $service = new CommunityCashService();
        $categories = FundCategory::all();

        if ($categories->isEmpty()) {
            return;
        }

        $names = ['Budi', 'Siti', 'Andi', 'Dewi', 'Rudi', 'Rina'];

        foreach ($categories->take(4) as $cat) {
            foreach (array_slice($names, 0, 3) as $i => $name) {
                $service->recordContribution([
                    'fund_category_id' => $cat->id,
                    'member_name' => $name,
                    'amount' => rand(2, 10) * 10000,
                    'date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                    'description' => "Iuran {$cat->name} - {$name}",
                    'recorded_by' => $bendahara->id,
                ]);
            }
        }

        // Sample expenses
        $expenses = [
            ['desc' => 'Beli sapu dan alat kebersihan', 'amount' => 75000],
            ['desc' => 'Santunan keluarga Pak Hadi', 'amount' => 200000],
            ['desc' => 'Bayar petugas keamanan', 'amount' => 150000],
            ['desc' => 'Beli kantong sampah', 'amount' => 50000],
        ];

        foreach ($expenses as $i => $exp) {
            $cat = $categories[$i % $categories->count()];
            $service->recordExpense([
                'fund_category_id' => $cat->id,
                'amount' => $exp['amount'],
                'date' => now()->subDays(rand(1, 15))->format('Y-m-d'),
                'description' => $exp['desc'],
                'recorded_by' => $bendahara->id,
            ]);
        }
    }
}
