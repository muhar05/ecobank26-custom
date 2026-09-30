<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $seeders = [
            RolePermissionSeeder::class,
            DefaultAdminSeeder::class,
            DefaultAppSettingsSeeder::class,
        ];

        // Seed demo/test data only if explicitly requested
        if (env('SEED_DEMO_DATA', false)) {
            $seeders[] = DefaultWasteCategoryGroupSeeder::class;
            $seeders[] = DemoSeeder::class;
        }

        $this->call($seeders);
    }
}
