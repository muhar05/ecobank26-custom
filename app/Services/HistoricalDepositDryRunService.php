<?php

namespace App\Services;

use App\Models\MaterialAlias;
use App\Models\WasteCustomer;
use Database\Seeders\HistoricalCustomerSeeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * DRY-RUN historical deposit import. READ-ONLY terhadap database:
 * tidak commit transaksi, tidak auto-create material/customer,
 * tidak mengubah harga master.
 *
 * Aturan historical mode (terpisah dari operational import):
 * - Harga HARUS dari Excel. Kosong / #N/A / invalid => NEEDS_MAPPING.
 * - Harga 0 = 0 (valid), bukan NULL.
 * - Material UNKNOWN => NEEDS_MAPPING (tanpa findOrCreateAutoMaterial).
 * - Customer tak dikenal / ambiguous => NEEDS_MAPPING.
 * - Kas, Karang Taruna, Sahabat Qur'an => EXCLUDED.
 * - Baris total/pivot/rekap/overflow => IGNORED.
 */
class HistoricalDepositDryRunService
{
    public const EXCLUDED_NAMES = [
        'kas', 'karang taruna', "sahabat qur'an",
        // File B (audit customer): NON_NASABAH / organisasi.
        'kas bank sampah', 'bank sampah dasana ub',
    ];

    /**
     * Harga historis yang selnya kosong/rusak di workbook tetapi ditemukan
     * di source historis lain dalam workbook yang sama.
     * Format: 'sheet:excel_row' => ['price' => float, 'source' => string].
     * BUKAN fallback ke WasteCategory.member_price.
     */
    public const HISTORICAL_PRICE_OVERRIDES = [
        // 23-8-2025 row 197: sel Harga kosong (tanpa formula). Pola seluruh
        // sheet = VLOOKUP(D,'Bu Erta'!$C$2:$E$66,3,0); price list Bu Erta
        // baris 'Botol & Kaca Beling' Harga Nasabah = 150.
        '23-8-2025:197' => [
            'price' => 150,
            'source' => "Bu Erta price list 'Botol & Kaca Beling' Harga Nasabah=150",
        ],
    ];

    /**
     * Atribusi customer berbasis evidence workbook (tanpa membuat master).
     * 'dipo' => seluruh 26 baris transaksi RT 6 + Tabungan Nasabah RT 6.
     * Customer belum ada di master => NEEDS_MAPPING dengan alasan ini.
     */
    public const CUSTOMER_EVIDENCE = [
        'dipo' => 'seluruh baris transaksi RT 6 + Tabungan Nasabah RT 6 (pending pembuatan customer)',
    ];

    /**
     * Sheet yang DIKELUARKAN dari dataset final File B (hasil audit 3 kasus
     * duplicate/revisi). Dataset final = sheet utama tiap tanggal.
     * - '21-6-26 (2)'  : duplikat identik dari '21-6-26' (data transaksi sama).
     * - '11-07-26 (2)' : varian rusak. Baja Ringan @1 (KamiBox = 1500),
     *                    PVC berubah jadi Selang (#N/A). Tidak cocok Tabungan
     *                    Nasabah kolom '11 Juli'.
     * - '30-5-26 Sent' : varian revisi. Tabungan Nasabah kolom '30 Mei'
     *                    cocok persis dengan sheet utama '30-5-26' (5/5 customer).
     */
    public const EXCLUDED_SHEET_NAMES = ['21-6-26 (2)', '11-07-26 (2)', '30-5-26 Sent'];

    /**
     * Material komposit yang tetap dipertahankan sebagai HISTORICAL EXCEPTION
     * (Opsi A): TIDAK dipaksa menjadi WasteCategory canonical, disimpan lewat
     * `material_label` (waste_category_id NULL). Label memakai normalisasi
     * `normalize()`.
     */
    public const HISTORICAL_EXCEPTION_LABELS = [
        'buku, majalah, brosur, koran',
        'seng/kawat',
    ];

    /**
     * Baris dengan source data gap di workbook (berat kosong). Diperlakukan
     * SKIPPED / ZERO_WEIGHT, bukan ERROR, dan tidak diberi berat karangan.
     * Format: 'sheet:excel_row' => alasan.
     */
    public const SOURCE_DATA_GAP_SKIPS = [
        '30-5-26:382' => 'Danang - Plastik Multilayer tanpa berat (source data gap workbook)',
    ];

    protected MaterialMappingService $mapping;

    public function __construct(?MaterialMappingService $mapping = null)
    {
        $this->mapping = $mapping ?? app(MaterialMappingService::class);
    }

    public function normalize(string $s): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $s)));
    }

    public function parseNumber($val): ?float
    {
        if ($val === null || $val === '') {
            return null;
        }
        if (is_numeric($val)) {
            return (float) $val;
        }
        $str = str_replace([' ', 'Rp', 'rp', 'RP'], '', (string) $val);
        if (str_contains($str, ',') && ! str_contains($str, '.')) {
            $str = str_replace(',', '.', $str);
        } elseif (str_contains($str, '.') && str_contains($str, ',')) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        }

        return is_numeric($str) ? (float) $str : null;
    }

    /**
     * Tebak tanggal dari nama sheet. Mengembalikan [Y-m-d, label] atau null.
     */
    public function guessDate(string $sheet): ?string
    {
        $clean = trim($sheet);
        if (preg_match('/^(\d{1,2})[-_\/.](\d{1,2})[-_\/.](\d{2,4})$/', $clean, $m)) {
            $y = (int) $m[3] < 100 ? (int) $m[3] + 2000 : (int) $m[3];
            if (checkdate((int) $m[2], (int) $m[1], $y)) {
                return sprintf('%04d-%02d-%02d', $y, $m[2], $m[1]);
            }
        }

        return null;
    }

    /**
     * @return array{date: string, rows: array<int, array>}
     */
    public function dryRunSheet(string $filePath, string $sheetName, string $date): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(false);
        // Harga berisi VLOOKUP ke sheet price list ('Bu Erta' pada
        // sheet lama, 'KamiBox' pada sheet April 2026): keduanya wajib ikut
        // dimuat agar kalkulasi formula berhasil (tanpa load seluruh workbook).
        $reader->setLoadSheetsOnly([$sheetName, 'Bu Erta', 'KamiBox']);
        $spreadsheet = $reader->load($filePath);
        $allRows = $spreadsheet->getSheetByName($sheetName)->toArray(null, true, false, false);
        $spreadsheet->disconnectWorksheets();

        // Cari baris header. Ada dua layout workbook historis:
        // - Type 1 (File A, sheet 30-05): header kolom D = 'Jenis Sampah'
        //   (A=No, B=Nama, C=RT, D=Jenis Sampah, E=Harga, F=Berat, G=Total).
        // - Type 2 (sebagian besar File B, layout transposed):
        //   header A = 'Nasabah', B = 'Material'
        //   (A=Nasabah, B=Material, C=Berat, D=Harga, E=Total).
        // Blok pivot/timbangan di kanan (G-I, K-M, O-T, U-Z, ...) diabaikan
        // karena kolom nama & material-nya kosong.
        $headerIdx = null;
        $layout = null;
        foreach ($allRows as $i => $row) {
            if ($i > 12) {
                break;
            }
            $d = isset($row[3]) ? $this->normalize((string) $row[3]) : '';
            if ($d === 'jenis sampah') {
                $headerIdx = $i;
                $layout = 'type1';
                break;
            }
            $a = isset($row[0]) ? $this->normalize((string) $row[0]) : '';
            $b = isset($row[1]) ? $this->normalize((string) $row[1]) : '';
            if ($a === 'nasabah' && $b === 'material') {
                $headerIdx = $i;
                $layout = 'type2';
                break;
            }
        }

        $cols = $layout === 'type2'
            ? ['nama' => 0, 'jenis' => 1, 'harga' => 3, 'weight' => 2, 'total' => 4]
            : ['nama' => 1, 'jenis' => 3, 'harga' => 4, 'weight' => 5, 'total' => 6];

        $customers = WasteCustomer::where('status', 'active')->get();
        $byName = [];
        foreach ($customers as $c) {
            $byName[$this->normalize($c->name)][] = $c;
        }

        $rows = [];
        if ($headerIdx === null) {
            return ['date' => $date, 'rows' => $rows, 'no_header' => true];
        }

        $total = count($allRows);
        for ($r = $headerIdx + 1; $r < $total; $r++) {
            $row = $allRows[$r];
            $nonEmpty = array_filter($row, fn ($c) => $c !== null && trim((string) $c) !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            $nama = isset($row[$cols['nama']]) ? trim((string) $row[$cols['nama']]) : '';
            $jenis = isset($row[$cols['jenis']]) ? trim((string) $row[$cols['jenis']]) : '';
            $hargaCell = $row[$cols['harga']] ?? null;
            $weight = $this->parseNumber($row[$cols['weight']] ?? null);
            $totalCell = $row[$cols['total']] ?? null;
            $excelTotal = $this->parseNumber($totalCell);
            $hargaEmpty = $hargaCell === null || trim((string) $hargaCell) === '';
            $harga = $this->parseNumber($hargaCell);

            $entry = [
                'sheet' => $sheetName,
                'date' => $date,
                'excel_row' => $r + 1,
                'nama' => $nama,
                'material' => $jenis,
                'harga_cell' => $hargaCell === null ? null : trim((string) $hargaCell),
                'weight' => $weight,
                'excel_total' => $excelTotal,
                'status' => null,
                'reason' => null,
            ];

            // 1. Baris non-data.
            if ($nama === '' || $jenis === '') {
                $entry['status'] = 'IGNORED';
                $entry['reason'] = 'Baris non-data (total/rekap/pivot/overflow/kosong)';
                $rows[] = $entry;

                continue;
            }
            $nNorm = $this->normalize($nama);
            if (in_array($nNorm, ['nama', 'no', 'row labels'], true)
                || str_contains($nNorm, 'timbangan')
                || $this->normalize($jenis) === 'jenis sampah'
                || $this->normalize($jenis) === 'jenis barang') {
                $entry['status'] = 'IGNORED';
                $entry['reason'] = 'Baris header/pivot berulang di tengah sheet';
                $rows[] = $entry;

                continue;
            }

            // 2. Excluded.
            if (in_array($nNorm, self::EXCLUDED_NAMES, true)) {
                $entry['status'] = 'EXCLUDED';
                $entry['reason'] = "NON_NASABAH '{$nama}' (Kas/Karang Taruna/Sahabat Qur'an)";
                $rows[] = $entry;

                continue;
            }

            // 3. Customer (exact, lalu alias nama kanonik hasil audit).
            $matched = $byName[$nNorm] ?? [];
            if (count($matched) === 0) {
                $aliasTarget = HistoricalCustomerSeeder::aliases()[$nNorm] ?? null;
                if ($aliasTarget !== null) {
                    $matched = $byName[$this->normalize($aliasTarget)] ?? [];
                    if (count($matched) === 1) {
                        $entry['customer_alias'] = $aliasTarget;
                    }
                }
            }
            if (count($matched) > 1) {
                $entry['status'] = 'NEEDS_MAPPING';
                $entry['reason'] = "Nasabah ambiguous: '{$nama}' cocok >1 master";
                $rows[] = $entry;

                continue;
            }
            if (count($matched) === 0) {
                $entry['status'] = 'NEEDS_MAPPING';
                $entry['reason'] = isset(self::CUSTOMER_EVIDENCE[$nNorm])
                    ? "'{$nama}' => ".self::CUSTOMER_EVIDENCE[$nNorm]
                    : "Nasabah '{$nama}' tidak ada di master";
                $rows[] = $entry;

                continue;
            }
            $entry['customer_id'] = $matched[0]->id;

            // 4. Material: canonical/alias (tanpa auto-create), atau
            //    HISTORICAL EXCEPTION (material_label, category NULL).
            $isException = in_array($this->normalize($jenis), self::HISTORICAL_EXCEPTION_LABELS, true);
            if ($isException) {
                $entry['category_id'] = null;
                $entry['material_label'] = $jenis;
                $entry['match_type'] = 'HISTORICAL_EXCEPTION';
            } else {
                $res = $this->mapping->resolve($jenis, MaterialAlias::SOURCE_BANK_SAMPAH, null);
                if (! $res['category']) {
                    $entry['status'] = 'NEEDS_MAPPING';
                    $entry['reason'] = "Material '{$jenis}' UNKNOWN (tidak ada kanonik/alias)";
                    $rows[] = $entry;

                    continue;
                }
                $entry['category_id'] = $res['category']->id;
                $entry['category_name'] = $res['category']->name;
                $entry['match_type'] = $res['match_type'];
            }

            // 5. Berat. Nol => SKIPPED (ZERO_WEIGHT). Source data gap (berat
            //    kosong pada baris yang sudah diaudit) => SKIPPED juga.
            $gapKey = $sheetName.':'.($r + 1);
            $gapReason = self::SOURCE_DATA_GAP_SKIPS[$gapKey] ?? null;

            if ($weight !== null && $weight <= 0) {
                $entry['status'] = 'SKIPPED';
                $entry['reason'] = 'SKIPPED: ZERO_WEIGHT (berat 0, jangan import)';
                $rows[] = $entry;

                continue;
            }
            if ($weight === null) {
                if ($gapReason) {
                    $entry['status'] = 'SKIPPED';
                    $entry['reason'] = 'SKIPPED: ZERO_WEIGHT (source data gap) - '.$gapReason;
                    $rows[] = $entry;

                    continue;
                }
                $entry['status'] = 'ERROR';
                $entry['reason'] = 'Berat tidak valid (bukan angka / kosong)';
                $rows[] = $entry;

                continue;
            }

            // 6. Harga historis (tanpa fallback master).
            $override = self::HISTORICAL_PRICE_OVERRIDES[$gapKey] ?? null;
            if ($harga === null && $override) {
                $harga = (float) $override['price'];
                $entry['price_source'] = $override['source'];
            }
            if ($harga === null) {
                $entry['status'] = 'NEEDS_MAPPING';
                $entry['reason'] = $hargaEmpty
                    ? "Harga historis kosong untuk '{$jenis}' (tanpa fallback master)"
                    : "Harga invalid '{$entry['harga_cell']}' untuk '{$jenis}'";
                $rows[] = $entry;

                continue;
            }
            if ($harga < 0) {
                $entry['status'] = 'NEEDS_MAPPING';
                $entry['reason'] = "Harga negatif '{$entry['harga_cell']}' untuk '{$jenis}'";
                $rows[] = $entry;

                continue;
            }

            $entry['used_price'] = $harga;
            $entry['subtotal'] = $excelTotal ?? round($weight * $harga);
            $entry['status'] = $isException ? 'HISTORICAL_EXCEPTION' : 'VALID';
            $rows[] = $entry;
        }

        return ['date' => $date, 'rows' => $rows];
    }
}
