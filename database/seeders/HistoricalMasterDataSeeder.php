<?php

namespace Database\Seeders;

use App\Models\Collector;
use App\Models\MaterialAlias;
use App\Models\WasteCategory;
use App\Services\MaterialMappingService;
use Illuminate\Database\Seeder;

/**
 * Master data seed dari SOURCE OF TRUTH:
 * `.audit/final-material-decision-table.md` + audit material File B
 * (`.agents/historical-import-context.md`).
 *
 * - 80 WasteCategory (78 canonical + 2 File B NEW: Plastik PP, Baja Ringan).
 * - 28 MaterialAlias (21 + 7 File B ALIAS, scope BANK_SAMPAH, via
 *   MaterialMappingService).
 * - 4 Collector: Bu Erta, Pak Surat, KamiBox, Pak Suparno.
 *
 * Disengaja TIDAK di-seed (HUMAN DECISION): "Seng/Kawat",
 * "Buku, Majalah, Brosur, Koran". JANGAN buat canonical/alias untuk
 * keduanya sampai ada keputusan bisnis.
 *
 * TIDAK menyentuh: users, waste_customers, transaksi (deposits/sales/
 * ledgers), WastePrice/WastePriceHistory, schema.
 *
 * Idempotent: aman dijalankan berulang. Kunci natural: kategori by `name`,
 * alias by (source_type, source_id, normalized_name) via
 * MaterialMappingService::registerAlias, collector by `name`.
 */
class HistoricalMasterDataSeeder extends Seeder
{
    /**
     * @return array<int, array{name: string, member_price: float, unit: string}>
     */
    public static function categories(): array
    {
        return [
            ['name' => 'AC Blower', 'member_price' => 90000, 'unit' => 'unit'],
            ['name' => 'AC Indoor', 'member_price' => 35000, 'unit' => 'unit'],
            ['name' => 'Aki', 'member_price' => 6000, 'unit' => 'kg'],
            ['name' => 'Acrylic', 'member_price' => 1200, 'unit' => 'kg'],
            ['name' => 'Alumunium', 'member_price' => 8000, 'unit' => 'kg'],
            ['name' => 'Alumunium Foil', 'member_price' => 3500, 'unit' => 'kg'],
            ['name' => 'Arsip', 'member_price' => 800, 'unit' => 'kg'],
            ['name' => 'Asoy', 'member_price' => 200, 'unit' => 'kg'],
            ['name' => 'Babet', 'member_price' => 7500, 'unit' => 'kg'],
            // File B NEW (audit material): ada di price list KamiBox, belum ada kanonik.
            ['name' => 'Baja Ringan', 'member_price' => 1500, 'unit' => 'kg'],
            ['name' => 'Ban Bekas', 'member_price' => 150, 'unit' => 'kg'],
            ['name' => 'Besi', 'member_price' => 2500, 'unit' => 'kg'],
            ['name' => 'Boncos Kotor', 'member_price' => 300, 'unit' => 'kg'],
            ['name' => 'MIX A/Boncos', 'member_price' => 500, 'unit' => 'kg'],
            ['name' => 'Botol & Kaca Beling', 'member_price' => 150, 'unit' => 'kg'],
            ['name' => 'Botol Kaca Coklat/Merah', 'member_price' => 600, 'unit' => 'kg'],
            ['name' => 'Botol Kaca Hijau', 'member_price' => 80, 'unit' => 'kg'],
            ['name' => 'Botol Kaca Campur', 'member_price' => 80, 'unit' => 'kg'],
            ['name' => 'Botol Putih Bersih', 'member_price' => 2800, 'unit' => 'kg'],
            ['name' => 'Botol & Gelas Putih (Kotor)', 'member_price' => 1600, 'unit' => 'kg'],
            ['name' => 'Botol Berwarna', 'member_price' => 800, 'unit' => 'kg'],
            ['name' => 'Buku Tulis & Pelajaran', 'member_price' => 1200, 'unit' => 'kg'],
            ['name' => 'CD Kristal', 'member_price' => 5000, 'unit' => 'kg'],
            ['name' => 'CPU', 'member_price' => 14000, 'unit' => 'unit'],
            ['name' => 'Emberan', 'member_price' => 1400, 'unit' => 'kg'],
            ['name' => 'Gabrukan', 'member_price' => 1000, 'unit' => 'kg'],
            ['name' => 'Galon Aqua', 'member_price' => 4000, 'unit' => 'unit'],
            ['name' => 'Galon PC', 'member_price' => 2500, 'unit' => 'kg'],
            ['name' => 'Gelas Putih Bersih', 'member_price' => 3500, 'unit' => 'kg'],
            ['name' => 'Gelas Berwarna', 'member_price' => 1300, 'unit' => 'kg'],
            ['name' => 'Gelas PP Warna', 'member_price' => 1600, 'unit' => 'kg'],
            ['name' => 'Handphone', 'member_price' => 1800, 'unit' => 'unit'],
            ['name' => 'Impact', 'member_price' => 600, 'unit' => 'kg'],
            ['name' => 'Injek', 'member_price' => 1600, 'unit' => 'kg'],
            ['name' => 'Naso', 'member_price' => 2000, 'unit' => 'kg'],
            ['name' => 'Kabin', 'member_price' => 1800, 'unit' => 'kg'],
            ['name' => 'Minyak Jelantah', 'member_price' => 4000, 'unit' => 'ltr'],
            ['name' => 'Kabel', 'member_price' => 1400, 'unit' => 'kg'],
            ['name' => 'Kabel Internet', 'member_price' => 150, 'unit' => 'kg'],
            ['name' => 'Kabel Kulit', 'member_price' => 700, 'unit' => 'kg'],
            ['name' => 'Kaleng', 'member_price' => 1500, 'unit' => 'kg'],
            ['name' => 'Kertas Semen', 'member_price' => 1300, 'unit' => 'kg'],
            ['name' => 'Kardus Baik/Kering', 'member_price' => 1000, 'unit' => 'kg'],
            ['name' => 'Karpet Talang', 'member_price' => 400, 'unit' => 'kg'],
            ['name' => 'Karung Beras', 'member_price' => 400, 'unit' => 'kg'],
            ['name' => 'Kaset CD', 'member_price' => 3500, 'unit' => 'kg'],
            ['name' => 'Seng', 'member_price' => 800, 'unit' => 'kg'],
            ['name' => 'Seng Pasir', 'member_price' => 700, 'unit' => 'kg'],
            ['name' => 'Kawat', 'member_price' => 700, 'unit' => 'kg'],
            ['name' => 'Kertas HVS Putih', 'member_price' => 1400, 'unit' => 'kg'],
            ['name' => 'Kipas Angin', 'member_price' => 13000, 'unit' => 'unit'],
            ['name' => 'Komputer', 'member_price' => 50000, 'unit' => 'set'],
            ['name' => 'Kones', 'member_price' => 500, 'unit' => 'kg'],
            ['name' => 'Koran/LKS', 'member_price' => 800, 'unit' => 'kg'],
            ['name' => 'Toples Kue Kristal', 'member_price' => 2700, 'unit' => 'kg'],
            ['name' => 'Kulkas 1 Pintu', 'member_price' => 40000, 'unit' => 'unit'],
            ['name' => 'Kulkas 2 Pintu', 'member_price' => 50000, 'unit' => 'unit'],
            ['name' => 'Kuningan', 'member_price' => 25000, 'unit' => 'kg'],
            ['name' => 'Magic Com Lengkap', 'member_price' => 10000, 'unit' => 'unit'],
            ['name' => 'Majalah', 'member_price' => 600, 'unit' => 'kg'],
            ['name' => 'Mesin Cuci', 'member_price' => 35000, 'unit' => 'unit'],
            // Mika: "Tidak Terima" di KamiBox, harga nasabah tak diketahui.
            // member_price 0 = default kolom, bukan harga asumsi.
            ['name' => 'Mika', 'member_price' => 0, 'unit' => 'kg'],
            ['name' => 'Monitor LED', 'member_price' => 8000, 'unit' => 'unit'],
            ['name' => 'Monitor Tabung', 'member_price' => 13000, 'unit' => 'unit'],
            ['name' => 'Multilayer', 'member_price' => 300, 'unit' => 'kg'],
            ['name' => 'Oli Bekas', 'member_price' => 500, 'unit' => 'ltr'],
            ['name' => 'Paralon', 'member_price' => 1200, 'unit' => 'kg'],
            ['name' => 'PVC', 'member_price' => 700, 'unit' => 'kg'],
            ['name' => 'Plastik (PE)', 'member_price' => 800, 'unit' => 'kg'],
            // File B NEW (audit material): PP berbeda dari PE, harga KamiBox 1000.
            ['name' => 'Plastik PP', 'member_price' => 1000, 'unit' => 'kg'],
            ['name' => 'Selang Air', 'member_price' => 400, 'unit' => 'kg'],
            ['name' => 'Sterofoam', 'member_price' => 150, 'unit' => 'kg'],
            ['name' => 'TV Tabung', 'member_price' => 13000, 'unit' => 'unit'],
            ['name' => 'TV LED', 'member_price' => 8000, 'unit' => 'unit'],
            ['name' => 'Tembaga', 'member_price' => 50000, 'unit' => 'kg'],
            ['name' => 'Tetrapak/UBC', 'member_price' => 100, 'unit' => 'kg'],
            ['name' => 'Try Telur', 'member_price' => 150, 'unit' => 'kg'],
            ['name' => 'Tutup Botol', 'member_price' => 2500, 'unit' => 'kg'],
            ['name' => 'Tutup Galon', 'member_price' => 3500, 'unit' => 'kg'],
            ['name' => 'Yakult', 'member_price' => 700, 'unit' => 'kg'],
        ];
    }

    /**
     * @return array<string, string> external label => canonical name
     */
    public static function aliases(): array
    {
        return [
            'AC' => 'AC Blower',
            'AKI' => 'Aki',
            'Akrilik Putih' => 'Acrylic',
            'Boncos' => 'Boncos Kotor',
            'Botol Kaca Bening/Putih' => 'Botol & Kaca Beling',
            'Kaca' => 'Botol & Kaca Beling',
            'Botol PET Bersih' => 'Botol Putih Bersih',
            'Botol PET Kotor' => 'Botol & Gelas Putih (Kotor)',
            'Botol Plastik Kemasan Campur' => 'Botol Berwarna',
            'Gelas PP Bersih' => 'Gelas Putih Bersih',
            'Jelantah' => 'Minyak Jelantah',
            'Kantung Semen' => 'Kertas Semen',
            'Kardus' => 'Kardus Baik/Kering',
            'HVS' => 'Kertas HVS Putih',
            'Kristal Putih' => 'Toples Kue Kristal',
            'Kulkas' => 'Kulkas 1 Pintu',
            'Plastik Multilayer' => 'Multilayer',
            'Plastik Minyak goreng' => 'Multilayer',
            'Plastik PE' => 'Plastik (PE)',
            'Styrofoam' => 'Sterofoam',
            'TV' => 'TV Tabung',
            // File B ALIAS (audit material): hanya beda penamaan, harga = kanonik.
            'HVS/Putihan' => 'Kertas HVS Putih',
            'Karung' => 'Karung Beras',
            'Gelas PP Bersih (bening)' => 'Gelas Putih Bersih',
            'Aluminium Rongsok' => 'Alumunium',
            'Kabel Alumunium' => 'Kabel',
            // Final audit: komposit yang aman (terbukti memetakan ke kanonik).
            'STAL/Kabin/Enamel' => 'Kabin',
            'Impact/Kerasan' => 'Impact',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function collectors(): array
    {
        return ['Bu Erta', 'Pak Surat', 'KamiBox', 'Pak Suparno'];
    }

    public function run(): void
    {
        foreach (self::collectors() as $name) {
            $collector = Collector::withTrashed()->where('name', $name)->first();
            if ($collector) {
                if ($collector->trashed()) {
                    $collector->restore();
                }

                continue;
            }
            Collector::create(['name' => $name]);
        }

        foreach (self::categories() as $item) {
            $category = WasteCategory::where('name', $item['name'])->first();
            if ($category) {
                $category->fill([
                    'unit' => $item['unit'],
                    'member_price' => $item['member_price'],
                ]);
                $category->save();

                continue;
            }
            WasteCategory::create([
                'name' => $item['name'],
                'code' => WasteCategory::generateCode(null),
                'unit' => $item['unit'],
                'member_price' => $item['member_price'],
            ]);
        }

        $mapping = app(MaterialMappingService::class);
        $byName = WasteCategory::all()->keyBy('name');
        foreach (self::aliases() as $external => $canonical) {
            $target = $byName->get($canonical);
            if (! $target) {
                $this->command->warn("Alias '{$external}': canonical '{$canonical}' tidak ditemukan, dilewati.");

                continue;
            }
            $mapping->registerAlias(
                $external,
                $target->id,
                MaterialAlias::SOURCE_BANK_SAMPAH,
                null,
                true
            );
        }
    }
}
