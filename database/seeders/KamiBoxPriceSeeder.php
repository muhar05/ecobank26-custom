<?php

namespace Database\Seeders;

use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use App\Models\WastePriceHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder sinkronisasi harga agregator KamiBox dari sheet 'KamiBox'
 * (docs/Bank Sampah NEW.xlsx).
 *
 * Mengimpor 51 harga beli agregator (collector_price) kanonik ke tabel
 * waste_prices dan mencatat riwayat perubahan di waste_price_histories.
 *
 * Idempotent: aman dijalankan berulang.
 */
class KamiBoxPriceSeeder extends Seeder
{
    public const BATCH_ID = 'HISTORICAL_KAMIBOX_2026';
    public const EFFECTIVE_DATE = '2026-05-01 00:00:00';

    /**
     * Definisi harga kanonik agregator KamiBox.
     * Menggunakan nama kategori kanonik untuk resolusi ID agar aman lintas database.
     *
     * @return array<int, array{category_name: string, excel_name: string, collector_price: float}>
     */
    public static function prices(): array
    {
        return [
            ['category_name' => 'AC Blower', 'excel_name' => 'AC', 'collector_price' => 200000],
            ['category_name' => 'Aki', 'excel_name' => 'Aki', 'collector_price' => 9000],
            ['category_name' => 'Acrylic', 'excel_name' => 'Akrilik Putih', 'collector_price' => 4000],
            ['category_name' => 'Alumunium', 'excel_name' => 'Alumunium', 'collector_price' => 10000],
            ['category_name' => 'Arsip', 'excel_name' => 'Arsip', 'collector_price' => 1000],
            ['category_name' => 'Asoy', 'excel_name' => 'Asoy', 'collector_price' => 1500],
            ['category_name' => 'Besi', 'excel_name' => 'Besi', 'collector_price' => 4000],
            ['category_name' => 'Boncos Kotor', 'excel_name' => 'Boncos Kotor', 'collector_price' => 400],
            ['category_name' => 'MIX A/Boncos', 'excel_name' => 'MIX A/Boncos', 'collector_price' => 700],
            ['category_name' => 'Botol & Kaca Beling', 'excel_name' => 'Botol Kaca Bening/Putih', 'collector_price' => 200],
            ['category_name' => 'Botol Kaca Coklat/Merah', 'excel_name' => 'Botol Kaca Coklat/Merah', 'collector_price' => 800],
            ['category_name' => 'Botol Kaca Hijau', 'excel_name' => 'Botol Kaca Hijau', 'collector_price' => 100],
            ['category_name' => 'Botol Kaca Campur', 'excel_name' => 'Botol Kaca Campur', 'collector_price' => 100],
            ['category_name' => 'Botol Putih Bersih', 'excel_name' => 'Botol PET Bersih', 'collector_price' => 5000],
            ['category_name' => 'Botol & Gelas Putih (Kotor)', 'excel_name' => 'Botol PET Kotor', 'collector_price' => 2500],
            ['category_name' => 'Botol Berwarna', 'excel_name' => 'Botol Plastik Kemasan Campur', 'collector_price' => 1000],
            ['category_name' => 'Emberan', 'excel_name' => 'Emberan', 'collector_price' => 1200],
            ['category_name' => 'Gabrukan', 'excel_name' => 'Gabrukan', 'collector_price' => 1000],
            ['category_name' => 'Galon PC', 'excel_name' => 'Galon PC', 'collector_price' => 3000],
            ['category_name' => 'Gelas Putih Bersih', 'excel_name' => 'Gelas PP Bersih', 'collector_price' => 4000],
            ['category_name' => 'Gelas PP Warna', 'excel_name' => 'Gelas PP Warna', 'collector_price' => 2000],
            ['category_name' => 'Handphone', 'excel_name' => 'Handphone', 'collector_price' => 5000],
            ['category_name' => 'Impact', 'excel_name' => 'Impact', 'collector_price' => 700],
            ['category_name' => 'Kabin', 'excel_name' => 'Kabin', 'collector_price' => 2000],
            ['category_name' => 'Minyak Jelantah', 'excel_name' => 'Jelantah', 'collector_price' => 7000],
            ['category_name' => 'Kabel', 'excel_name' => 'Kabel', 'collector_price' => 1500],
            ['category_name' => 'Kaleng', 'excel_name' => 'Kaleng', 'collector_price' => 2000],
            ['category_name' => 'Kertas Semen', 'excel_name' => 'Kantung Semen', 'collector_price' => 1800],
            ['category_name' => 'Kardus Baik/Kering', 'excel_name' => 'Kardus', 'collector_price' => 1800],
            ['category_name' => 'Karung Beras', 'excel_name' => 'Karung', 'collector_price' => 1200],
            ['category_name' => 'Kaset CD', 'excel_name' => 'Kaset CD', 'collector_price' => 4000],
            ['category_name' => 'Kertas HVS Putih', 'excel_name' => 'HVS/Putihan', 'collector_price' => 2000],
            ['category_name' => 'Kipas Angin', 'excel_name' => 'Kipas Angin', 'collector_price' => 15000],
            ['category_name' => 'Komputer', 'excel_name' => 'Komputer', 'collector_price' => 60000],
            ['category_name' => 'Kones', 'excel_name' => 'Kones', 'collector_price' => 700],
            ['category_name' => 'Toples Kue Kristal', 'excel_name' => 'Kristal Putih', 'collector_price' => 4000],
            ['category_name' => 'Kulkas 1 Pintu', 'excel_name' => 'Kulkas', 'collector_price' => 50000],
            ['category_name' => 'Kuningan', 'excel_name' => 'Kuningan', 'collector_price' => 35000],
            ['category_name' => 'Mesin Cuci', 'excel_name' => 'Mesin Cuci', 'collector_price' => 50000],
            ['category_name' => 'Multilayer', 'excel_name' => 'Plastik Multilayer', 'collector_price' => 500],
            ['category_name' => 'PVC', 'excel_name' => 'PVC', 'collector_price' => 900],
            ['category_name' => 'Plastik (PE)', 'excel_name' => 'Plastik PE', 'collector_price' => 1700],
            ['category_name' => 'Sterofoam', 'excel_name' => 'Styrofoam', 'collector_price' => 400],
            ['category_name' => 'TV Tabung', 'excel_name' => 'TV', 'collector_price' => 20000],
            ['category_name' => 'Tembaga', 'excel_name' => 'Tembaga', 'collector_price' => 60000],
            ['category_name' => 'Tetrapak/UBC', 'excel_name' => 'Tetrapak/UBC', 'collector_price' => 150],
            ['category_name' => 'Tutup Botol', 'excel_name' => 'Tutup Botol', 'collector_price' => 3000],
            ['category_name' => 'Tutup Galon', 'excel_name' => 'Tutup Galon', 'collector_price' => 4500],
            ['category_name' => 'Yakult', 'excel_name' => 'Yakult', 'collector_price' => 1000],
            ['category_name' => 'Baja Ringan', 'excel_name' => 'Baja Ringan', 'collector_price' => 2000],
            ['category_name' => 'Plastik PP', 'excel_name' => 'Plastik PP', 'collector_price' => 1700],
        ];
    }

    public function run(): void
    {
        $collector = Collector::firstOrCreate(
            ['name' => 'KamiBox'],
            ['phone' => null, 'address' => null]
        );

        $categories = WasteCategory::all()->keyBy('name');
        $prices = self::prices();

        $synced = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($collector, $categories, $prices, &$synced, &$updated, &$skipped) {
            foreach ($prices as $item) {
                $category = $categories->get($item['category_name']);
                if (! $category) {
                    $skipped++;
                    continue;
                }

                $existingPrice = WastePrice::where('collector_id', $collector->id)
                    ->where('waste_category_id', $category->id)
                    ->first();

                $targetPrice = (float) $item['collector_price'];

                if (! $existingPrice) {
                    $newPrice = WastePrice::create([
                        'collector_id'      => $collector->id,
                        'waste_category_id' => $category->id,
                        'collector_price'   => $targetPrice,
                    ]);

                    WastePriceHistory::create([
                        'waste_price_id'    => $newPrice->id,
                        'collector_id'      => $collector->id,
                        'waste_category_id' => $category->id,
                        'old_price'         => null,
                        'new_price'         => $targetPrice,
                        'unit'              => $category->unit ?? 'kg',
                        'changed_at'        => self::EFFECTIVE_DATE,
                        'changed_by_user_id'=> null,
                        'admin_device_id'   => null,
                        'source'            => WastePriceHistory::SOURCE_IMPORT,
                        'import_batch_id'   => self::BATCH_ID,
                    ]);

                    $synced++;
                } else {
                    $oldPrice = (float) $existingPrice->collector_price;
                    if (abs($oldPrice - $targetPrice) > 0.001) {
                        $existingPrice->update([
                            'collector_price' => $targetPrice,
                        ]);

                        WastePriceHistory::create([
                            'waste_price_id'    => $existingPrice->id,
                            'collector_id'      => $collector->id,
                            'waste_category_id' => $category->id,
                            'old_price'         => $oldPrice,
                            'new_price'         => $targetPrice,
                            'unit'              => $category->unit ?? 'kg',
                            'changed_at'        => now(),
                            'changed_by_user_id'=> null,
                            'admin_device_id'   => null,
                            'source'            => WastePriceHistory::SOURCE_IMPORT,
                            'import_batch_id'   => self::BATCH_ID,
                        ]);

                        $updated++;
                    }
                }
            }
        });

        $this->command?->info("KamiBox prices synced: {$synced} created, {$updated} updated, {$skipped} skipped.");
    }
}
