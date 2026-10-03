<?php

namespace Database\Seeders;

use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use App\Models\WastePrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Baseline development seed untuk domain BANK SAMPAH.
 *
 * Idempotent: aman dijalankan berulang (dipakai oleh
 * `bank-sampah:reset` maupun `db:seed --class=...`).
 * Kunci natural: groups by `code`, categories by `name`
 * (fallback `code`), collector by `name`, prices by
 * pasangan (collector, category).
 *
 * Tidak menyentuh: users, roles/permissions assignment milik user,
 * sessions, cache, jobs, migrations.
 * Tidak membuat data transaksi (deposits/sales/withdrawals/ledgers)
 * maupun nasabah (waste_customers).
 */
class BankSampahBaselineSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DefaultAppSettingsSeeder::class,
        ]);

        $this->seedGroups();
        $categoryIds = $this->seedCategories();
        $collector = $this->seedCollector();
        $this->seedPrices($collector->id, $categoryIds);
    }

    private function seedGroups(): void
    {
        $groups = [
            ['code' => 'PLS', 'name' => 'Plastik', 'description' => 'Kategori sampah berbahan dasar plastik'],
            ['code' => 'KRT', 'name' => 'Kertas', 'description' => 'Kategori sampah berbahan dasar kertas/karton'],
            ['code' => 'LOG', 'name' => 'Logam', 'description' => 'Kategori sampah berbahan dasar logam/besi/baja'],
            ['code' => 'KCA', 'name' => 'Kaca', 'description' => 'Kategori sampah berbahan dasar kaca/beling'],
            ['code' => 'ORG', 'name' => 'Organik', 'description' => 'Kategori sampah organik/sisa makanan/daun'],
            ['code' => 'ELK', 'name' => 'Elektronik', 'description' => 'Kategori sampah elektronik/komponen listrik'],
            ['code' => 'LNY', 'name' => 'Lainnya', 'description' => 'Kategori sampah lainnya yang tidak termasuk dalam grup di atas'],
        ];

        foreach ($groups as $group) {
            DB::table('waste_category_groups')->updateOrInsert(
                ['code' => $group['code']],
                $group + ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * @return array<string, int> name => id
     */
    private function seedCategories(): array
    {
        $categories = [
            ['name' => 'Botol Putih Bersih', 'code' => 'PLS.01', 'group_code' => 'PLS', 'unit' => 'kg', 'member_price' => 2800],
            ['name' => 'Botol Putih Kotor', 'code' => 'PLS.02', 'group_code' => 'PLS', 'unit' => 'kg', 'member_price' => 1500],
            ['name' => 'Gelas Plastik Bersih', 'code' => 'PLS.03', 'group_code' => 'PLS', 'unit' => 'kg', 'member_price' => 1800],
            ['name' => 'Kardus Bersih', 'code' => 'KRT.01', 'group_code' => 'KRT', 'unit' => 'kg', 'member_price' => 1200],
            ['name' => 'Kertas Campur', 'code' => 'KRT.02', 'group_code' => 'KRT', 'unit' => 'kg', 'member_price' => 800],
        ];

        $ids = [];

        foreach ($categories as $item) {
            $group = WasteCategoryGroup::where('code', $item['group_code'])->first();

            $category = WasteCategory::where('name', $item['name'])->first()
                ?? ($item['code'] ? WasteCategory::where('code', $item['code'])->first() : null);

            if ($category) {
                $category->fill([
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'unit' => $item['unit'],
                    'member_price' => $item['member_price'],
                    'category_group' => $group?->name,
                    'waste_category_group_id' => $group?->id,
                ]);
                $category->save();
            } else {
                $category = WasteCategory::create([
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'unit' => $item['unit'],
                    'member_price' => $item['member_price'],
                    'category_group' => $group?->name,
                    'waste_category_group_id' => $group?->id,
                ]);
            }

            $ids[$item['name']] = $category->id;
        }

        return $ids;
    }

    private function seedCollector(): Collector
    {
        $collector = Collector::withTrashed()->where('name', 'Bu Erta')->first();

        if ($collector) {
            if ($collector->trashed()) {
                $collector->restore();
            }
            $collector->fill(['phone' => '081234567890', 'address' => 'Jl. Pasar No. 10']);
            $collector->save();

            return $collector;
        }

        return Collector::create([
            'name' => 'Bu Erta',
            'phone' => '081234567890',
            'address' => 'Jl. Pasar No. 10',
        ]);
    }

    /**
     * @param array<string, int> $categoryIds
     */
    private function seedPrices(int $collectorId, array $categoryIds): void
    {
        $prices = [
            'Botol Putih Bersih' => 3100,
            'Botol Putih Kotor' => 1800,
            'Kardus Bersih' => 1500,
            'Gelas Plastik Bersih' => 2200,
            'Kertas Campur' => 1000,
        ];

        foreach ($prices as $name => $collectorPrice) {
            if (! isset($categoryIds[$name])) {
                continue;
            }

            WastePrice::updateOrCreate(
                ['collector_id' => $collectorId, 'waste_category_id' => $categoryIds[$name]],
                ['collector_price' => $collectorPrice]
            );
        }
    }
}
