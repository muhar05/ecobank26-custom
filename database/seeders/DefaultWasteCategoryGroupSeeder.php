<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultWasteCategoryGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'code' => 'PLS',
                'name' => 'Plastik',
                'description' => 'Kategori sampah berbahan dasar plastik',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'KRT',
                'name' => 'Kertas',
                'description' => 'Kategori sampah berbahan dasar kertas/karton',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'LOG',
                'name' => 'Logam',
                'description' => 'Kategori sampah berbahan dasar logam/besi/baja',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'KCA',
                'name' => 'Kaca',
                'description' => 'Kategori sampah berbahan dasar kaca/beling',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ORG',
                'name' => 'Organik',
                'description' => 'Kategori sampah organik/sisa makanan/daun',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ELK',
                'name' => 'Elektronik',
                'description' => 'Kategori sampah elektronik/komponen listrik',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'LNY',
                'name' => 'Lainnya',
                'description' => 'Kategori sampah lainnya yang tidak termasuk dalam grup di atas',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($groups as $group) {
            DB::table('waste_category_groups')->updateOrInsert(
                ['code' => $group['code']],
                $group
            );
        }
    }
}
