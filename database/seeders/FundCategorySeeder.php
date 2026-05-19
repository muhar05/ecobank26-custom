<?php

namespace Database\Seeders;

use App\Models\FundCategory;
use Illuminate\Database\Seeder;

class FundCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Dana Kematian', 'target_amount' => 5000000],
            ['name' => 'Dana Sampah', 'target_amount' => 2000000],
            ['name' => 'Dana Keamanan', 'target_amount' => 3000000],
            ['name' => 'Dana Kebersihan', 'target_amount' => 2500000],
            ['name' => 'Dana Sosial', 'target_amount' => 3000000],
            ['name' => 'Kas Umum', 'target_amount' => 2000000],
        ];

        foreach ($categories as $cat) {
            FundCategory::updateOrCreate(
                ['name' => $cat['name']],
                ['target_amount' => $cat['target_amount']]
            );
        }
    }
}
