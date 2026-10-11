<?php

namespace App\Console\Commands;

use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use Database\Seeders\KamiBoxPriceSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncKamiBoxPricesCommand extends Command
{
    protected $signature = 'bank-sampah:sync-kamibox-prices {--dry-run : Lakukan simulasi tanpa menulis ke database}';

    protected $description = 'Sinkronisasi harga lapak agregator KamiBox ke tabel waste_prices';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $prices = KamiBoxPriceSeeder::prices();

        $this->line('=== SINKRONISASI HARGA AGREGATOR KAMIBOX ===');
        $this->line('Jumlah item katalog: ' . count($prices));

        $collector = Collector::firstOrCreate(
            ['name' => 'KamiBox'],
            ['phone' => null, 'address' => null]
        );

        $categories = WasteCategory::all()->keyBy('name');

        $readyToSync = [];
        $unmatched = [];

        foreach ($prices as $item) {
            $cat = $categories->get($item['category_name']);
            if ($cat) {
                $readyToSync[] = [
                    'category_id' => $cat->id,
                    'code' => $cat->code,
                    'category_name' => $cat->name,
                    'excel_name' => $item['excel_name'],
                    'unit' => $cat->unit,
                    'collector_price' => $item['collector_price'],
                ];
            } else {
                $unmatched[] = $item;
            }
        }

        $this->info("Kategori cocok: " . count($readyToSync) . " | Tidak cocok: " . count($unmatched));

        if ($dryRun) {
            $this->warn('[DRY-RUN] Tidak ada data yang ditulis ke database.');
            $this->table(
                ['ID', 'Kode', 'Nama Kategori', 'Nama Excel', 'Unit', 'Harga Lapak (Rp)'],
                array_map(fn($r) => [
                    $r['category_id'],
                    $r['code'],
                    $r['category_name'],
                    $r['excel_name'],
                    $r['unit'],
                    number_format($r['collector_price'], 0, ',', '.'),
                ], array_slice($readyToSync, 0, 15))
            );
            if (count($readyToSync) > 15) {
                $this->line('... dan ' . (count($readyToSync) - 15) . ' item lainnya.');
            }
            return self::SUCCESS;
        }

        $beforeCount = WastePrice::where('collector_id', $collector->id)->count();

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\KamiBoxPriceSeeder',
            '--force' => true,
        ], $this->getOutput());

        $afterCount = WastePrice::where('collector_id', $collector->id)->count();

        $this->info("Berhasil! Total harga KamiBox di database: {$afterCount} (sebelumnya: {$beforeCount}).");

        return self::SUCCESS;
    }
}
