<?php

namespace Database\Seeders;

use App\Models\FundCategory;
use Illuminate\Database\Seeder;

class FundCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Dana Kematian',
            'Dana Sampah',
            'Dana Keamanan',
            'Dana Kebersihan',
            'Dana Sosial',
            'Kas Umum',
        ];

        foreach ($categories as $name) {
            FundCategory::firstOrCreate(['name' => $name]);
        }
    }
}
