<?php

namespace Database\Seeders;

use App\Models\Collector;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Services\MaterialMappingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder migrasi penjualan sampah historis ke lapak/agregator (KamiBox & Non KamiBox)
 * dari 7 sheet transaksi bertanggal di docs/Bank Sampah NEW.xlsx (Mei - September 2026).
 *
 * Mengimpor 15 transaksi penjualan (194 baris detail timbangan) dengan total nilai
 * penjualan Rp 10.391.421,00.
 *
 * Idempotent: aman dijalankan berulang.
 */
class HistoricalSalesSeeder extends Seeder
{
    public function run(): void
    {
        $mappingService = app(MaterialMappingService::class);
        $collectors = Collector::all()->keyBy('name');

        // Gabungkan blok Timbangan dengan kunci natural identik
        // (collector + tanggal + catatan) supaya total_amount = jumlah
        // seluruh blok pada hari itu, bukan hanya blok terakhir.
        $groups = [];
        foreach (self::sales() as $saleItem) {
            $key = $saleItem['collector'].'|'.$saleItem['date'].'|'.$saleItem['notes'];
            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'collector' => $saleItem['collector'],
                    'date' => $saleItem['date'],
                    'notes' => $saleItem['notes'],
                    'details' => [],
                ];
            }
            foreach ($saleItem['details'] as $detailItem) {
                $groups[$key]['details'][] = $detailItem;
            }
        }

        $createdSales = 0;
        $createdDetails = 0;

        DB::transaction(function () use ($groups, $collectors, $mappingService, &$createdSales, &$createdDetails) {
            foreach ($groups as $group) {
                $collector = $collectors->get($group['collector']);
                if (! $collector) {
                    $collector = Collector::firstOrCreate(['name' => $group['collector']]);
                    $collectors->put($collector->name, $collector);
                }

                $saleAmount = round((float) collect($group['details'])->sum('subtotal'), 2);

                $sale = Sale::where('collector_id', $collector->id)
                    ->whereDate('date', $group['date'])
                    ->where('notes', $group['notes'])
                    ->first();

                if ($sale) {
                    $sale->update(['total_amount' => $saleAmount]);
                } else {
                    $sale = Sale::create([
                        'collector_id' => $collector->id,
                        'date' => $group['date'],
                        'notes' => $group['notes'],
                        'total_amount' => $saleAmount,
                    ]);
                }

                $createdSales++;

                // Recreate detail tiap blok agar setiap baris Excel tersimpan
                // utuh, termasuk dua label berbeda yang memetakan ke kategori
                // kanonik yang sama (mis. Kaca & Botol Kaca Bening/Putih).
                $sale->details()->delete();

                foreach ($group['details'] as $detailItem) {
                    $mat = $detailItem['material'];
                    $resolved = $mappingService->resolve($mat);

                    $catId = $resolved['category']?->id ?? null;
                    $matLabel = $catId ? null : $mat;

                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'waste_category_id' => $catId,
                        'material_label' => $matLabel,
                        'weight' => $detailItem['weight'],
                        'price_per_unit' => $detailItem['price_per_unit'],
                        'member_price' => $detailItem['member_price'],
                        'subtotal' => $detailItem['subtotal'],
                        'margin' => $detailItem['margin'],
                    ]);

                    $createdDetails++;
                }
            }
        });

        $this->command?->info("Penjualan historis berhasil disinkronkan: {$createdSales} transaksi penjualan, {$createdDetails} baris detail timbangan lapak.");
    }

    public static function sales(): array
    {
        return [
            [
                'date' => '2026-05-09',
                'collector' => 'KamiBox',
                'notes' => 'Penjualan lapak KamiBox (Timbangan Kami Box)',
                'total_amount' => 572385.0,
                'details' => [
                    ['material' => 'Alumunium', 'weight' => 0.1, 'price_per_unit' => 10000.0, 'member_price' => 8000.0, 'subtotal' => 1000.0, 'margin' => 200.0],
                    ['material' => 'Besi', 'weight' => 7.35, 'price_per_unit' => 4000.0, 'member_price' => 2500.0, 'subtotal' => 29400.0, 'margin' => 11025.0],
                    ['material' => 'Botol Kaca Bening/Putih', 'weight' => 9.55, 'price_per_unit' => 200.0, 'member_price' => 150.0, 'subtotal' => 1910.0, 'margin' => 477.5],
                    ['material' => 'Botol PET bersih', 'weight' => 28.35, 'price_per_unit' => 5000.0, 'member_price' => 2800.0, 'subtotal' => 141750.0, 'margin' => 62370.0],
                    ['material' => 'Botol PET Kotor', 'weight' => 8.5, 'price_per_unit' => 2500.0, 'member_price' => 1600.0, 'subtotal' => 21250.0, 'margin' => 7650.0],
                    ['material' => 'Buku, majalah, brosur, koran', 'weight' => 10.9, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 10900.0, 'margin' => 2180.0],
                    ['material' => 'Emberan', 'weight' => 34.95, 'price_per_unit' => 1200.0, 'member_price' => 1000.0, 'subtotal' => 41940.0, 'margin' => 6990.0],
                    ['material' => 'Gabrukan', 'weight' => 31.6, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 31600.0, 'margin' => 6320.0],
                    ['material' => 'Gelas PP Bersih', 'weight' => 1.2, 'price_per_unit' => 4000.0, 'member_price' => 3000.0, 'subtotal' => 4800.0, 'margin' => 1200.0],
                    ['material' => 'Gelas PP Warna', 'weight' => 4.75, 'price_per_unit' => 2000.0, 'member_price' => 1600.0, 'subtotal' => 9500.0, 'margin' => 1900.0],
                    ['material' => 'HVS/Putihan', 'weight' => 6.1, 'price_per_unit' => 2000.0, 'member_price' => 1400.0, 'subtotal' => 12200.0, 'margin' => 3660.0],
                    ['material' => 'Impact', 'weight' => 3.7, 'price_per_unit' => 700.0, 'member_price' => 600.0, 'subtotal' => 2590.0, 'margin' => 370.0],
                    ['material' => 'Jelantah', 'weight' => 4.0, 'price_per_unit' => 7000.0, 'member_price' => 5000.0, 'subtotal' => 28000.0, 'margin' => 8000.0],
                    ['material' => 'Kabin', 'weight' => 6.75, 'price_per_unit' => 2000.0, 'member_price' => 1800.0, 'subtotal' => 13500.0, 'margin' => 1350.0],
                    ['material' => 'Kaca', 'weight' => 21.0, 'price_per_unit' => 100.0, 'member_price' => 80.0, 'subtotal' => 2100.0, 'margin' => 420.0],
                    ['material' => 'Kaleng', 'weight' => 7.15, 'price_per_unit' => 2000.0, 'member_price' => 1500.0, 'subtotal' => 14300.0, 'margin' => 3575.0],
                    ['material' => 'Kardus', 'weight' => 59.3, 'price_per_unit' => 1800.0, 'member_price' => 1000.0, 'subtotal' => 106740.0, 'margin' => 47440.0],
                    ['material' => 'Kaset CD', 'weight' => 8.75, 'price_per_unit' => 4000.0, 'member_price' => 3500.0, 'subtotal' => 35000.0, 'margin' => 4375.0],
                    ['material' => 'Kristal Putih', 'weight' => 3.2, 'price_per_unit' => 4000.0, 'member_price' => 2700.0, 'subtotal' => 12800.0, 'margin' => 4160.0],
                    ['material' => 'MIX A/Boncos', 'weight' => 24.6, 'price_per_unit' => 700.0, 'member_price' => 500.0, 'subtotal' => 17220.0, 'margin' => 4920.0],
                    ['material' => 'Plastik Multilayer', 'weight' => 27.9, 'price_per_unit' => 500.0, 'member_price' => 400.0, 'subtotal' => 13950.0, 'margin' => 2790.0],
                    ['material' => 'Plastik PE', 'weight' => 4.5, 'price_per_unit' => 1700.0, 'member_price' => 800.0, 'subtotal' => 7650.0, 'margin' => 4050.0],
                    ['material' => 'Styrofoam', 'weight' => 0.15, 'price_per_unit' => 400.0, 'member_price' => 150.0, 'subtotal' => 60.0, 'margin' => 37.5],
                    ['material' => 'Tutup Botol', 'weight' => 2.8, 'price_per_unit' => 3000.0, 'member_price' => 2500.0, 'subtotal' => 8400.0, 'margin' => 1400.0],
                    ['material' => 'Tutup Galon', 'weight' => 0.85, 'price_per_unit' => 4500.0, 'member_price' => 3500.0, 'subtotal' => 3825.0, 'margin' => 850.0],
                ],
            ],
            [
                'date' => '2026-05-09',
                'collector' => 'KamiBox',
                'notes' => 'Penjualan lapak KamiBox (Timbangan Kami Box)',
                'total_amount' => 3350.0,
                'details' => [
                    ['material' => 'Asoy', 'weight' => 16.75, 'price_per_unit' => 200.0, 'member_price' => 500.0, 'subtotal' => 3350.0, 'margin' => -5025.0],
                ],
            ],
            [
                'date' => '2026-05-30',
                'collector' => 'KamiBox',
                'notes' => 'Penjualan lapak KamiBox (Timbangan Kami Box)',
                'total_amount' => 1321765.0,
                'details' => [
                    ['material' => 'Alumunium', 'weight' => 0.9, 'price_per_unit' => 10000.0, 'member_price' => 8000.0, 'subtotal' => 9000.0, 'margin' => 1800.0],
                    ['material' => 'Botol Kaca Bening/Putih', 'weight' => 40.95, 'price_per_unit' => 200.0, 'member_price' => 150.0, 'subtotal' => 8190.0, 'margin' => 2047.5],
                    ['material' => 'Botol PET bersih', 'weight' => 83.5, 'price_per_unit' => 5000.0, 'member_price' => 2800.0, 'subtotal' => 417500.0, 'margin' => 183700.0],
                    ['material' => 'Botol PET Kotor', 'weight' => 5.05, 'price_per_unit' => 2500.0, 'member_price' => 1600.0, 'subtotal' => 12625.0, 'margin' => 4545.0],
                    ['material' => 'Buku, majalah, brosur, koran', 'weight' => 22.15, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 22150.0, 'margin' => 4430.0],
                    ['material' => 'Emberan', 'weight' => 75.2, 'price_per_unit' => 1200.0, 'member_price' => 1000.0, 'subtotal' => 90240.0, 'margin' => 15040.0],
                    ['material' => 'Gabrukan', 'weight' => 53.6, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 53600.0, 'margin' => 10720.0],
                    ['material' => 'Galon PC', 'weight' => 1.4, 'price_per_unit' => 3000.0, 'member_price' => 2500.0, 'subtotal' => 4200.0, 'margin' => 700.0],
                    ['material' => 'Gelas PP Bersih', 'weight' => 5.0, 'price_per_unit' => 4000.0, 'member_price' => 3000.0, 'subtotal' => 20000.0, 'margin' => 5000.0],
                    ['material' => 'Gelas PP Warna', 'weight' => 1.05, 'price_per_unit' => 2000.0, 'member_price' => 1600.0, 'subtotal' => 2100.0, 'margin' => 420.0],
                    ['material' => 'Handphone', 'weight' => 1.0, 'price_per_unit' => 5000.0, 'member_price' => 1800.0, 'subtotal' => 5000.0, 'margin' => 3200.0],
                    ['material' => 'HVS/Putihan', 'weight' => 34.15, 'price_per_unit' => 2000.0, 'member_price' => 1400.0, 'subtotal' => 68300.0, 'margin' => 20490.0],
                    ['material' => 'Jelantah', 'weight' => 19.15, 'price_per_unit' => 7000.0, 'member_price' => 5000.0, 'subtotal' => 134050.0, 'margin' => 38300.0],
                    ['material' => 'Kabin', 'weight' => 14.75, 'price_per_unit' => 2000.0, 'member_price' => 1800.0, 'subtotal' => 29500.0, 'margin' => 2950.0],
                    ['material' => 'Kaleng', 'weight' => 15.65, 'price_per_unit' => 2000.0, 'member_price' => 1500.0, 'subtotal' => 31300.0, 'margin' => 7825.0],
                    ['material' => 'Kardus', 'weight' => 184.55, 'price_per_unit' => 1800.0, 'member_price' => 1000.0, 'subtotal' => 332190.0, 'margin' => 147640.0],
                    ['material' => 'Kristal Putih', 'weight' => 0.25, 'price_per_unit' => 4000.0, 'member_price' => 2700.0, 'subtotal' => 1000.0, 'margin' => 325.0],
                    ['material' => 'MIX A/Boncos', 'weight' => 30.85, 'price_per_unit' => 700.0, 'member_price' => 500.0, 'subtotal' => 21595.0, 'margin' => 6170.0],
                    ['material' => 'Plastik Multilayer', 'weight' => 71.95, 'price_per_unit' => 500.0, 'member_price' => 400.0, 'subtotal' => 35975.0, 'margin' => 7195.0],
                    ['material' => 'Seng/Kawat', 'weight' => 1.6, 'price_per_unit' => 700.0, 'member_price' => 500.0, 'subtotal' => 1120.0, 'margin' => 320.0],
                    ['material' => 'Styrofoam', 'weight' => 1.4, 'price_per_unit' => 400.0, 'member_price' => 150.0, 'subtotal' => 560.0, 'margin' => 350.0],
                    ['material' => 'Tutup Botol', 'weight' => 6.25, 'price_per_unit' => 3000.0, 'member_price' => 2500.0, 'subtotal' => 18750.0, 'margin' => 3125.0],
                    ['material' => 'Tutup Galon', 'weight' => 0.6, 'price_per_unit' => 4500.0, 'member_price' => 3500.0, 'subtotal' => 2700.0, 'margin' => 600.0],
                    ['material' => 'Karung', 'weight' => 0.1, 'price_per_unit' => 1200.0, 'member_price' => 400.0, 'subtotal' => 120.0, 'margin' => 80.0],
                ],
            ],
            [
                'date' => '2026-05-30',
                'collector' => 'KamiBox',
                'notes' => 'Penjualan lapak KamiBox (Timbangan Kami Box)',
                'total_amount' => 102320.0,
                'details' => [
                    ['material' => 'Asoy', 'weight' => 17.4, 'price_per_unit' => 1500.0, 'member_price' => 500.0, 'subtotal' => 26100.0, 'margin' => 17400.0],
                    ['material' => 'Besi', 'weight' => 16.0, 'price_per_unit' => 4000.0, 'member_price' => 2500.0, 'subtotal' => 65000.0, 'margin' => 24000.0],
                    ['material' => 'Plastik PE', 'weight' => 5.85, 'price_per_unit' => 1700.0, 'member_price' => 800.0, 'subtotal' => 9945.0, 'margin' => 5265.0],
                    ['material' => 'Plastik PP', 'weight' => 0.75, 'price_per_unit' => 1700.0, 'member_price' => 1000.0, 'subtotal' => 1275.0, 'margin' => 525.0],
                ],
            ],
            [
                'date' => '2026-06-21',
                'collector' => 'KamiBox',
                'notes' => 'Penjualan lapak KamiBox (Timbangan Kami Box)',
                'total_amount' => 899796.0,
                'details' => [
                    ['material' => 'Alumunium', 'weight' => 0.4, 'price_per_unit' => 10000.0, 'member_price' => 8000.0, 'subtotal' => 4000.0, 'margin' => 800.0],
                    ['material' => 'Besi', 'weight' => 25.85, 'price_per_unit' => 4000.0, 'member_price' => 2500.0, 'subtotal' => 103400.0, 'margin' => 38775.0],
                    ['material' => 'Botol Kaca Hijau', 'weight' => 8.0, 'price_per_unit' => 100.0, 'member_price' => 80.0, 'subtotal' => 800.0, 'margin' => 160.0],
                    ['material' => 'Botol Kaca Bening/Putih', 'weight' => 23.65, 'price_per_unit' => 200.0, 'member_price' => 150.0, 'subtotal' => 4730.0, 'margin' => 1182.5],
                    ['material' => 'Botol Kaca Coklat/Merah', 'weight' => 0.8, 'price_per_unit' => 800.0, 'member_price' => 600.0, 'subtotal' => 640.0, 'margin' => 160.0],
                    ['material' => 'Botol PET bersih', 'weight' => 4.3, 'price_per_unit' => 5000.0, 'member_price' => 2800.0, 'subtotal' => 21500.0, 'margin' => 9460.0],
                    ['material' => 'Botol PET Kotor', 'weight' => 4.85, 'price_per_unit' => 2500.0, 'member_price' => 1600.0, 'subtotal' => 12125.0, 'margin' => 4365.0],
                    ['material' => 'Buku, majalah, brosur, koran', 'weight' => 98.15, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 98150.0, 'margin' => 19630.0],
                    ['material' => 'Emberan', 'weight' => 93.68, 'price_per_unit' => 1200.0, 'member_price' => 1000.0, 'subtotal' => 112416.0, 'margin' => 18736.0],
                    ['material' => 'Gabrukan', 'weight' => 35.7, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 35700.0, 'margin' => 7140.0],
                    ['material' => 'Gelas PP Bersih', 'weight' => 3.8, 'price_per_unit' => 4000.0, 'member_price' => 3000.0, 'subtotal' => 15200.0, 'margin' => 3800.0],
                    ['material' => 'Gelas PP Warna', 'weight' => 4.55, 'price_per_unit' => 2000.0, 'member_price' => 1600.0, 'subtotal' => 9100.0, 'margin' => 1820.0],
                    ['material' => 'HVS/Putihan', 'weight' => 1.8, 'price_per_unit' => 2000.0, 'member_price' => 1400.0, 'subtotal' => 3600.0, 'margin' => 1080.0],
                    ['material' => 'Kabel', 'weight' => 2.25, 'price_per_unit' => 1500.0, 'member_price' => 1400.0, 'subtotal' => 3375.0, 'margin' => 225.0],
                    ['material' => 'Kabin', 'weight' => 17.2, 'price_per_unit' => 2000.0, 'member_price' => 1800.0, 'subtotal' => 34400.0, 'margin' => 3440.0],
                    ['material' => 'Kaleng', 'weight' => 12.9, 'price_per_unit' => 2000.0, 'member_price' => 1500.0, 'subtotal' => 25800.0, 'margin' => 6450.0],
                    ['material' => 'Kardus', 'weight' => 168.7, 'price_per_unit' => 1800.0, 'member_price' => 1000.0, 'subtotal' => 303660.0, 'margin' => 134960.0],
                    ['material' => 'Kones', 'weight' => 1.4, 'price_per_unit' => 700.0, 'member_price' => 500.0, 'subtotal' => 980.0, 'margin' => 280.0],
                    ['material' => 'Kristal Putih', 'weight' => 1.75, 'price_per_unit' => 4000.0, 'member_price' => 2700.0, 'subtotal' => 7000.0, 'margin' => 2275.0],
                    ['material' => 'MIX A/Boncos', 'weight' => 45.55, 'price_per_unit' => 700.0, 'member_price' => 500.0, 'subtotal' => 31885.0, 'margin' => 9110.0],
                    ['material' => 'Plastik Multilayer', 'weight' => 68.8, 'price_per_unit' => 500.0, 'member_price' => 400.0, 'subtotal' => 34400.0, 'margin' => 6880.0],
                    ['material' => 'Plastik PE', 'weight' => 2.1, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 2100.0, 'margin' => 420.0],
                    ['material' => 'Plastik PP', 'weight' => 11.05, 'price_per_unit' => 1000.0, 'member_price' => 1000.0, 'subtotal' => 11050.0, 'margin' => 0.0],
                    ['material' => 'Styrofoam', 'weight' => 0.25, 'price_per_unit' => 400.0, 'member_price' => 150.0, 'subtotal' => 100.0, 'margin' => 62.5],
                    ['material' => 'Tutup Botol', 'weight' => 5.55, 'price_per_unit' => 3000.0, 'member_price' => 2500.0, 'subtotal' => 16650.0, 'margin' => 2775.0],
                    ['material' => 'Tutup Galon', 'weight' => 0.55, 'price_per_unit' => 4500.0, 'member_price' => 3500.0, 'subtotal' => 2475.0, 'margin' => 550.0],
                    ['material' => 'Karung', 'weight' => 3.8, 'price_per_unit' => 1200.0, 'member_price' => 400.0, 'subtotal' => 4560.0, 'margin' => 3040.0],
                ],
            ],
            [
                'date' => '2026-06-21',
                'collector' => 'Pak Surat',
                'notes' => 'Penjualan lapak non KamiBox (Timbangan Non Kami Box)',
                'total_amount' => 1016785.0,
                'details' => [
                    ['material' => 'Asoy', 'weight' => 16.05, 'price_per_unit' => 1500.0, 'member_price' => 500.0, 'subtotal' => 24825.0, 'margin' => 16050.0],
                    ['material' => 'Botol PET bersih', 'weight' => 62.0, 'price_per_unit' => 5500.0, 'member_price' => 2800.0, 'subtotal' => 342000.0, 'margin' => 167400.0],
                    ['material' => 'Jelantah', 'weight' => 91.45, 'price_per_unit' => 7000.0, 'member_price' => 5000.0, 'subtotal' => 640150.0, 'margin' => 182900.0],
                    ['material' => 'Plastik Multilayer', 'weight' => 18.6, 'price_per_unit' => 500.0, 'member_price' => 400.0, 'subtotal' => 9300.0, 'margin' => 1860.0],
                    ['material' => 'Plastik PP', 'weight' => 0.3, 'price_per_unit' => 1700.0, 'member_price' => 1000.0, 'subtotal' => 510.0, 'margin' => 210.0],
                ],
            ],
            [
                'date' => '2026-07-11',
                'collector' => 'KamiBox',
                'notes' => 'Penjualan lapak KamiBox (Timbangan Kami Box)',
                'total_amount' => 1019285.0,
                'details' => [
                    ['material' => 'Aki', 'weight' => 2.7, 'price_per_unit' => 9000.0, 'member_price' => 6000.0, 'subtotal' => 24300.0, 'margin' => 8100.0],
                    ['material' => 'Alumunium', 'weight' => 0.45, 'price_per_unit' => 10000.0, 'member_price' => 8000.0, 'subtotal' => 4500.0, 'margin' => 900.0],
                    ['material' => 'Besi', 'weight' => 9.4, 'price_per_unit' => 4000.0, 'member_price' => 2500.0, 'subtotal' => 37600.0, 'margin' => 14100.0],
                    ['material' => 'Botol Kaca Bening/Putih', 'weight' => 22.95, 'price_per_unit' => 200.0, 'member_price' => 150.0, 'subtotal' => 4590.0, 'margin' => 1147.5],
                    ['material' => 'Botol PET bersih', 'weight' => 58.5, 'price_per_unit' => 5000.0, 'member_price' => 2800.0, 'subtotal' => 292500.0, 'margin' => 128700.0],
                    ['material' => 'Botol PET Kotor', 'weight' => 13.33, 'price_per_unit' => 2500.0, 'member_price' => 1600.0, 'subtotal' => 33325.0, 'margin' => 11997.0],
                    ['material' => 'Buku, majalah, brosur, koran', 'weight' => 36.85, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 36850.0, 'margin' => 7370.0],
                    ['material' => 'Emberan', 'weight' => 68.65, 'price_per_unit' => 1200.0, 'member_price' => 1000.0, 'subtotal' => 82380.0, 'margin' => 13730.0],
                    ['material' => 'Gabrukan', 'weight' => 29.0, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 29000.0, 'margin' => 5800.0],
                    ['material' => 'Gelas PP Bersih', 'weight' => 0.3, 'price_per_unit' => 4000.0, 'member_price' => 3000.0, 'subtotal' => 1200.0, 'margin' => 300.0],
                    ['material' => 'Gelas PP Warna', 'weight' => 2.15, 'price_per_unit' => 2000.0, 'member_price' => 1600.0, 'subtotal' => 4300.0, 'margin' => 860.0],
                    ['material' => 'HVS/Putihan', 'weight' => 7.15, 'price_per_unit' => 2000.0, 'member_price' => 1400.0, 'subtotal' => 14300.0, 'margin' => 4290.0],
                    ['material' => 'Kabel', 'weight' => 3.5, 'price_per_unit' => 1500.0, 'member_price' => 1400.0, 'subtotal' => 5250.0, 'margin' => 350.0],
                    ['material' => 'Kabin', 'weight' => 9.7, 'price_per_unit' => 2000.0, 'member_price' => 1800.0, 'subtotal' => 19400.0, 'margin' => 1940.0],
                    ['material' => 'Kaleng', 'weight' => 13.45, 'price_per_unit' => 2000.0, 'member_price' => 1500.0, 'subtotal' => 26900.0, 'margin' => 6725.0],
                    ['material' => 'Kardus', 'weight' => 156.9, 'price_per_unit' => 1800.0, 'member_price' => 1000.0, 'subtotal' => 282420.0, 'margin' => 125520.0],
                    ['material' => 'Kristal Putih', 'weight' => 0.4, 'price_per_unit' => 4000.0, 'member_price' => 2700.0, 'subtotal' => 1600.0, 'margin' => 520.0],
                    ['material' => 'MIX A/Boncos', 'weight' => 36.1, 'price_per_unit' => 700.0, 'member_price' => 500.0, 'subtotal' => 25270.0, 'margin' => 7220.0],
                    ['material' => 'Plastik Multilayer', 'weight' => 63.5, 'price_per_unit' => 500.0, 'member_price' => 400.0, 'subtotal' => 31750.0, 'margin' => 6350.0],
                    ['material' => 'PVC', 'weight' => 3.7, 'price_per_unit' => 900.0, 'member_price' => 700.0, 'subtotal' => 3330.0, 'margin' => 740.0],
                    ['material' => 'Seng/Kawat', 'weight' => 0.3, 'price_per_unit' => 700.0, 'member_price' => 500.0, 'subtotal' => 210.0, 'margin' => 60.0],
                    ['material' => 'Tutup Botol', 'weight' => 14.95, 'price_per_unit' => 3000.0, 'member_price' => 2500.0, 'subtotal' => 44850.0, 'margin' => 7475.0],
                    ['material' => 'Tutup Galon', 'weight' => 2.4, 'price_per_unit' => 4500.0, 'member_price' => 3500.0, 'subtotal' => 10800.0, 'margin' => 2400.0],
                    ['material' => 'Karung', 'weight' => 1.3, 'price_per_unit' => 1200.0, 'member_price' => 400.0, 'subtotal' => 1560.0, 'margin' => 1040.0],
                    ['material' => 'Baja Ringan', 'weight' => 0.55, 'price_per_unit' => 2000.0, 'member_price' => 1500.0, 'subtotal' => 1100.0, 'margin' => 275.0],
                ],
            ],
            [
                'date' => '2026-07-11',
                'collector' => 'Pak Surat',
                'notes' => 'Penjualan lapak non KamiBox (Timbangan Non Kami Box)',
                'total_amount' => 75250.0,
                'details' => [
                    ['material' => 'Asoy', 'weight' => 19.7, 'price_per_unit' => 1500.0, 'member_price' => 500.0, 'subtotal' => 29550.0, 'margin' => 19700.0],
                    ['material' => 'Plastik PE', 'weight' => 0.6, 'price_per_unit' => 1700.0, 'member_price' => 800.0, 'subtotal' => 1020.0, 'margin' => 540.0],
                    ['material' => 'Plastik PP', 'weight' => 4.15, 'price_per_unit' => 1700.0, 'member_price' => 1000.0, 'subtotal' => 7055.0, 'margin' => 2905.0],
                ],
            ],
            [
                'date' => '2026-07-11',
                'collector' => 'Pak Surat',
                'notes' => 'Penjualan lapak non KamiBox (Timbangan Non Kami Box)',
                'total_amount' => 1482000.0,
                'details' => [
                    ['material' => 'Jelantah', 'weight' => 168.0, 'price_per_unit' => 8800.0, 'member_price' => 5000.0, 'subtotal' => 1482000.0, 'margin' => 638400.0],
                ],
            ],
            [
                'date' => '2026-08-01',
                'collector' => 'KamiBox',
                'notes' => 'Penjualan lapak KamiBox (Timbangan Kami Box)',
                'total_amount' => 879140.0,
                'details' => [
                    ['material' => 'Alumunium', 'weight' => 0.85, 'price_per_unit' => 10000.0, 'member_price' => 8000.0, 'subtotal' => 8500.0, 'margin' => 1700.0],
                    ['material' => 'Besi', 'weight' => 3.65, 'price_per_unit' => 4000.0, 'member_price' => 2500.0, 'subtotal' => 14600.0, 'margin' => 5475.0],
                    ['material' => 'Botol Kaca Bening/Putih', 'weight' => 30.6, 'price_per_unit' => 200.0, 'member_price' => 150.0, 'subtotal' => 6120.0, 'margin' => 1530.0],
                    ['material' => 'Botol PET bersih', 'weight' => 49.4, 'price_per_unit' => 5000.0, 'member_price' => 2800.0, 'subtotal' => 247000.0, 'margin' => 108680.0],
                    ['material' => 'Botol PET Kotor', 'weight' => 6.15, 'price_per_unit' => 2500.0, 'member_price' => 1600.0, 'subtotal' => 15375.0, 'margin' => 5535.0],
                    ['material' => 'Buku, majalah, brosur, koran', 'weight' => 43.6, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 43600.0, 'margin' => 8720.0],
                    ['material' => 'Emberan', 'weight' => 41.55, 'price_per_unit' => 1200.0, 'member_price' => 1000.0, 'subtotal' => 49860.0, 'margin' => 8310.0],
                    ['material' => 'Gabrukan', 'weight' => 30.1, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 30100.0, 'margin' => 6020.0],
                    ['material' => 'Gelas PP Bersih', 'weight' => 1.35, 'price_per_unit' => 4000.0, 'member_price' => 3000.0, 'subtotal' => 5400.0, 'margin' => 1350.0],
                    ['material' => 'Gelas PP Warna', 'weight' => 2.5, 'price_per_unit' => 2000.0, 'member_price' => 1600.0, 'subtotal' => 5000.0, 'margin' => 1000.0],
                    ['material' => 'HVS/Putihan', 'weight' => 9.55, 'price_per_unit' => 2000.0, 'member_price' => 1400.0, 'subtotal' => 19100.0, 'margin' => 5730.0],
                    ['material' => 'Kabel', 'weight' => 0.35, 'price_per_unit' => 1500.0, 'member_price' => 1400.0, 'subtotal' => 525.0, 'margin' => 35.0],
                    ['material' => 'Kabin', 'weight' => 4.7, 'price_per_unit' => 2000.0, 'member_price' => 1800.0, 'subtotal' => 9400.0, 'margin' => 940.0],
                    ['material' => 'Kaleng', 'weight' => 10.2, 'price_per_unit' => 2000.0, 'member_price' => 1500.0, 'subtotal' => 20400.0, 'margin' => 5100.0],
                    ['material' => 'Kardus', 'weight' => 165.75, 'price_per_unit' => 1800.0, 'member_price' => 1000.0, 'subtotal' => 298350.0, 'margin' => 132600.0],
                    ['material' => 'Kipas Angin', 'weight' => 1.0, 'price_per_unit' => 20000.0, 'member_price' => 13000.0, 'subtotal' => 20000.0, 'margin' => 7000.0],
                    ['material' => 'Kristal Putih', 'weight' => 0.7, 'price_per_unit' => 4000.0, 'member_price' => 2700.0, 'subtotal' => 2800.0, 'margin' => 910.0],
                    ['material' => 'MIX A/Boncos', 'weight' => 50.5, 'price_per_unit' => 700.0, 'member_price' => 500.0, 'subtotal' => 35350.0, 'margin' => 10100.0],
                    ['material' => 'Plastik Multilayer', 'weight' => 45.1, 'price_per_unit' => 500.0, 'member_price' => 400.0, 'subtotal' => 22550.0, 'margin' => 4510.0],
                    ['material' => 'Seng/Kawat', 'weight' => 0.15, 'price_per_unit' => 700.0, 'member_price' => 500.0, 'subtotal' => 105.0, 'margin' => 30.0],
                    ['material' => 'Styrofoam', 'weight' => 0.45, 'price_per_unit' => 400.0, 'member_price' => 150.0, 'subtotal' => 180.0, 'margin' => 112.5],
                    ['material' => 'Tutup Botol', 'weight' => 4.85, 'price_per_unit' => 3000.0, 'member_price' => 2500.0, 'subtotal' => 14550.0, 'margin' => 2425.0],
                    ['material' => 'Tutup Galon', 'weight' => 1.55, 'price_per_unit' => 4500.0, 'member_price' => 3500.0, 'subtotal' => 6975.0, 'margin' => 1550.0],
                    ['material' => 'Karung', 'weight' => 2.75, 'price_per_unit' => 1200.0, 'member_price' => 400.0, 'subtotal' => 3300.0, 'margin' => 2200.0],
                ],
            ],
            [
                'date' => '2026-08-01',
                'collector' => 'Pak Surat',
                'notes' => 'Penjualan lapak non KamiBox (Timbangan Non Kami Box)',
                'total_amount' => 47890.0,
                'details' => [
                    ['material' => 'Asoy', 'weight' => 14.15, 'price_per_unit' => 1500.0, 'member_price' => 500.0, 'subtotal' => 21225.0, 'margin' => 14150.0],
                    ['material' => 'Plastik PE', 'weight' => 0.15, 'price_per_unit' => 1700.0, 'member_price' => 800.0, 'subtotal' => 255.0, 'margin' => 135.0],
                    ['material' => 'Plastik PP', 'weight' => 1.45, 'price_per_unit' => 1700.0, 'member_price' => 1000.0, 'subtotal' => 2465.0, 'margin' => 1015.0],
                ],
            ],
            [
                'date' => '2026-08-22',
                'collector' => 'KamiBox',
                'notes' => 'Penjualan lapak KamiBox (Timbangan Kami Box)',
                'total_amount' => 1519560.0,
                'details' => [
                    ['material' => 'Alumunium', 'weight' => 0.2, 'price_per_unit' => 10000.0, 'member_price' => 8000.0, 'subtotal' => 2000.0, 'margin' => 400.0],
                    ['material' => 'Besi', 'weight' => 17.25, 'price_per_unit' => 4000.0, 'member_price' => 2500.0, 'subtotal' => 69000.0, 'margin' => 25875.0],
                    ['material' => 'Botol Kaca Bening/Putih', 'weight' => 29.3, 'price_per_unit' => 200.0, 'member_price' => 150.0, 'subtotal' => 5860.0, 'margin' => 1465.0],
                    ['material' => 'Botol PET bersih', 'weight' => 56.45, 'price_per_unit' => 5000.0, 'member_price' => 2800.0, 'subtotal' => 282250.0, 'margin' => 124190.0],
                    ['material' => 'Botol PET Kotor', 'weight' => 6.7, 'price_per_unit' => 2500.0, 'member_price' => 1600.0, 'subtotal' => 16750.0, 'margin' => 6030.0],
                    ['material' => 'Buku, majalah, brosur, koran', 'weight' => 12.5, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 12500.0, 'margin' => 2500.0],
                    ['material' => 'Emberan', 'weight' => 69.7, 'price_per_unit' => 1200.0, 'member_price' => 1000.0, 'subtotal' => 83640.0, 'margin' => 13940.0],
                    ['material' => 'Gabrukan', 'weight' => 52.4, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 52400.0, 'margin' => 10480.0],
                    ['material' => 'Gelas PP Bersih', 'weight' => 2.5, 'price_per_unit' => 4000.0, 'member_price' => 3000.0, 'subtotal' => 10000.0, 'margin' => 2500.0],
                    ['material' => 'Gelas PP Warna', 'weight' => 2.2, 'price_per_unit' => 2000.0, 'member_price' => 1600.0, 'subtotal' => 4400.0, 'margin' => 880.0],
                    ['material' => 'HVS/Putihan', 'weight' => 35.0, 'price_per_unit' => 2000.0, 'member_price' => 1400.0, 'subtotal' => 70000.0, 'margin' => 21000.0],
                    ['material' => 'Jelantah', 'weight' => 71.35, 'price_per_unit' => 7000.0, 'member_price' => 5000.0, 'subtotal' => 499450.0, 'margin' => 142700.0],
                    ['material' => 'Kabin', 'weight' => 2.6, 'price_per_unit' => 2000.0, 'member_price' => 1800.0, 'subtotal' => 5200.0, 'margin' => 520.0],
                    ['material' => 'Kaleng', 'weight' => 9.7, 'price_per_unit' => 2000.0, 'member_price' => 1500.0, 'subtotal' => 19400.0, 'margin' => 4850.0],
                    ['material' => 'Kardus', 'weight' => 167.55, 'price_per_unit' => 1800.0, 'member_price' => 1000.0, 'subtotal' => 301590.0, 'margin' => 134040.0],
                    ['material' => 'MIX A/Boncos', 'weight' => 41.5, 'price_per_unit' => 700.0, 'member_price' => 500.0, 'subtotal' => 29050.0, 'margin' => 8300.0],
                    ['material' => 'Plastik Multilayer', 'weight' => 69.15, 'price_per_unit' => 500.0, 'member_price' => 400.0, 'subtotal' => 34575.0, 'margin' => 6915.0],
                    ['material' => 'Tutup Botol', 'weight' => 5.7, 'price_per_unit' => 3000.0, 'member_price' => 2500.0, 'subtotal' => 17100.0, 'margin' => 2850.0],
                    ['material' => 'Tutup Galon', 'weight' => 0.95, 'price_per_unit' => 4500.0, 'member_price' => 3500.0, 'subtotal' => 4275.0, 'margin' => 950.0],
                    ['material' => 'Karung', 'weight' => 0.1, 'price_per_unit' => 1200.0, 'member_price' => 400.0, 'subtotal' => 120.0, 'margin' => 80.0],
                ],
            ],
            [
                'date' => '2026-08-22',
                'collector' => 'Pak Surat',
                'notes' => 'Penjualan lapak non KamiBox (Timbangan Non Kami Box)',
                'total_amount' => 58390.0,
                'details' => [
                    ['material' => 'Asoy', 'weight' => 18.5, 'price_per_unit' => 1500.0, 'member_price' => 500.0, 'subtotal' => 27750.0, 'margin' => 18500.0],
                    ['material' => 'Plastik PE', 'weight' => 0.5, 'price_per_unit' => 1700.0, 'member_price' => 800.0, 'subtotal' => 850.0, 'margin' => 450.0],
                    ['material' => 'Plastik PP', 'weight' => 0.35, 'price_per_unit' => 1700.0, 'member_price' => 1000.0, 'subtotal' => 595.0, 'margin' => 245.0],
                ],
            ],
            [
                'date' => '2026-09-12',
                'collector' => 'KamiBox',
                'notes' => 'Penjualan lapak KamiBox (Timbangan Kami Box)',
                'total_amount' => 1293585.0,
                'details' => [
                    ['material' => 'Besi', 'weight' => 21.5, 'price_per_unit' => 4000.0, 'member_price' => 2500.0, 'subtotal' => 86000.0, 'margin' => 32250.0],
                    ['material' => 'Botol Kaca Bening/Putih', 'weight' => 19.4, 'price_per_unit' => 200.0, 'member_price' => 150.0, 'subtotal' => 3880.0, 'margin' => 970.0],
                    ['material' => 'Botol PET bersih', 'weight' => 71.6, 'price_per_unit' => 5000.0, 'member_price' => 2800.0, 'subtotal' => 358000.0, 'margin' => 157520.0],
                    ['material' => 'Botol PET Kotor', 'weight' => 4.1, 'price_per_unit' => 2500.0, 'member_price' => 1600.0, 'subtotal' => 10250.0, 'margin' => 3690.0],
                    ['material' => 'Buku, majalah, brosur, koran', 'weight' => 18.65, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 18650.0, 'margin' => 3730.0],
                    ['material' => 'Emberan', 'weight' => 68.1, 'price_per_unit' => 1200.0, 'member_price' => 1000.0, 'subtotal' => 81720.0, 'margin' => 13620.0],
                    ['material' => 'Gabrukan', 'weight' => 21.35, 'price_per_unit' => 1000.0, 'member_price' => 800.0, 'subtotal' => 21350.0, 'margin' => 4270.0],
                    ['material' => 'Gelas PP Bersih', 'weight' => 3.8, 'price_per_unit' => 4000.0, 'member_price' => 3000.0, 'subtotal' => 15200.0, 'margin' => 3800.0],
                    ['material' => 'Gelas PP Warna', 'weight' => 3.15, 'price_per_unit' => 2000.0, 'member_price' => 1600.0, 'subtotal' => 6300.0, 'margin' => 1260.0],
                    ['material' => 'HVS/Putihan', 'weight' => 1.9, 'price_per_unit' => 2000.0, 'member_price' => 1400.0, 'subtotal' => 3800.0, 'margin' => 1140.0],
                    ['material' => 'Jelantah', 'weight' => 49.2, 'price_per_unit' => 7000.0, 'member_price' => 5000.0, 'subtotal' => 344400.0, 'margin' => 98400.0],
                    ['material' => 'Kabel', 'weight' => 2.7, 'price_per_unit' => 1500.0, 'member_price' => 1400.0, 'subtotal' => 4050.0, 'margin' => 270.0],
                    ['material' => 'Kabin', 'weight' => 5.6, 'price_per_unit' => 2000.0, 'member_price' => 1800.0, 'subtotal' => 11200.0, 'margin' => 1120.0],
                    ['material' => 'Kaleng', 'weight' => 19.6, 'price_per_unit' => 2000.0, 'member_price' => 1500.0, 'subtotal' => 39200.0, 'margin' => 9800.0],
                    ['material' => 'Kardus', 'weight' => 108.15, 'price_per_unit' => 1800.0, 'member_price' => 1000.0, 'subtotal' => 194670.0, 'margin' => 86520.0],
                    ['material' => 'Kristal Putih', 'weight' => 0.8, 'price_per_unit' => 4000.0, 'member_price' => 2700.0, 'subtotal' => 3200.0, 'margin' => 1040.0],
                    ['material' => 'MIX A/Boncos', 'weight' => 39.4, 'price_per_unit' => 700.0, 'member_price' => 500.0, 'subtotal' => 27580.0, 'margin' => 7880.0],
                    ['material' => 'Plastik Multilayer', 'weight' => 81.65, 'price_per_unit' => 500.0, 'member_price' => 400.0, 'subtotal' => 40825.0, 'margin' => 8165.0],
                    ['material' => 'Styrofoam', 'weight' => 2.25, 'price_per_unit' => 400.0, 'member_price' => 150.0, 'subtotal' => 900.0, 'margin' => 562.5],
                    ['material' => 'Tutup Botol', 'weight' => 4.8, 'price_per_unit' => 3000.0, 'member_price' => 2500.0, 'subtotal' => 14400.0, 'margin' => 2400.0],
                    ['material' => 'Tutup Galon', 'weight' => 1.5, 'price_per_unit' => 4500.0, 'member_price' => 3500.0, 'subtotal' => 6750.0, 'margin' => 1500.0],
                    ['material' => 'Karung', 'weight' => 1.05, 'price_per_unit' => 1200.0, 'member_price' => 400.0, 'subtotal' => 1260.0, 'margin' => 840.0],
                ],
            ],
            [
                'date' => '2026-09-12',
                'collector' => 'Pak Surat',
                'notes' => 'Penjualan lapak non KamiBox (Timbangan Non Kami Box)',
                'total_amount' => 99920.0,
                'details' => [
                    ['material' => 'Asoy', 'weight' => 29.85, 'price_per_unit' => 1500.0, 'member_price' => 500.0, 'subtotal' => 44775.0, 'margin' => 29850.0],
                    ['material' => 'Plastik PE', 'weight' => 0.55, 'price_per_unit' => 1700.0, 'member_price' => 800.0, 'subtotal' => 935.0, 'margin' => 495.0],
                    ['material' => 'Plastik PP', 'weight' => 2.5, 'price_per_unit' => 1700.0, 'member_price' => 1000.0, 'subtotal' => 4250.0, 'margin' => 1750.0],
                ],
            ],
        ];
    }
}
