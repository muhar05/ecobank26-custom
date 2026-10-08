<?php

namespace App\Console\Commands;

use App\Models\WasteCustomer;
use App\Services\DepositImportService;
use App\Services\HistoricalDepositDryRunService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * DRY-RUN historical deposit import. Read-only:
 * tidak commit, tidak menulis database sama sekali.
 *
 * Banyak workbook (combined A+B), semua sheet bertanggal diproses KRONOLOGIS
 * lintas file. Sheet duplikat/revisi yang sudah diaudit dikeluarkan.
 */
class HistoricalDepositDryRun extends Command
{
    protected $signature = 'historical:deposit-dry-run {files* : Satu atau lebih path workbook, urut kronologis}';

    protected $description = 'Dry-run historical deposit import seluruh workbook (read-only, multi-file, kronologis)';

    public function handle(HistoricalDepositDryRunService $svc): int
    {
        $files = (array) $this->argument('files');
        foreach ($files as $f) {
            if (! is_file($f)) {
                $this->error("File tidak ditemukan: {$f}");

                return self::FAILURE;
            }
        }

        // Label exception dinormalkan agar varian huruf (File A vs B) menyatu.
        $exceptionDisplay = [
            'buku, majalah, brosur, koran' => 'Buku, Majalah, Brosur, Koran',
            'seng/kawat' => 'Seng/Kawat',
        ];
        $masterNames = WasteCustomer::pluck('name', 'id');

        $tot = ['scanned' => 0, 'valid' => 0, 'exception' => 0, 'ignored' => 0, 'excluded' => 0, 'needs' => 0, 'error' => 0, 'skipped' => 0];
        $weight = 0.0;
        $value = 0.0;

        $unknownMats = [];
        $missingPrice = [];
        $ambigCust = [];
        $errorDetails = [];
        $exceptionDetails = [];
        $zeroDetails = [];

        $byCustomer = [];
        $byMaterial = [];
        $byException = [];
        $dailyMaterial = [];
        $fingerprints = [];
        $perSheet = [];
        $perFile = [];
        $skippedSheetsAll = [];

        // 1. Bangun rencana global (file, sheet, date), lalu sort kronologis.
        $plan = [];
        foreach ($files as $file) {
            $base = basename($file);
            $reader = IOFactory::createReaderForFile($file);
            $sheets = $reader->listWorksheetNames($file);
            $dated = [];
            $skipped = [];
            foreach ($sheets as $s) {
                if (in_array($s, HistoricalDepositDryRunService::EXCLUDED_SHEET_NAMES, true)) {
                    $skipped[] = $s.' (excluded: duplikat/revisi)';

                    continue;
                }
                $d = $svc->guessDate($s);
                if ($d) {
                    $dated[$s] = $d;
                } else {
                    $skipped[] = $s;
                }
            }
            asort($dated);
            $skippedSheetsAll[$base] = $skipped;
            foreach ($dated as $sheet => $date) {
                $plan[] = ['file' => $file, 'base' => $base, 'sheet' => $sheet, 'date' => $date];
            }
            $perFile[$base] = ['tot' => array_fill_keys(array_keys($tot), 0), 'w' => 0.0, 'v' => 0.0];
        }
        usort($plan, fn ($a, $b) => [$a['date'], $a['base'], $a['sheet']] <=> [$b['date'], $b['base'], $b['sheet']]);

        $this->line('=== FINAL COMBINED HISTORICAL DRY-RUN ===');
        $this->line('Files: '.count($files).' | '.count($plan).' sheet bertanggal (kronologis)');

        $sheetC = null;

        $flushSheet = function () use (&$sheetC, &$perSheet) {
            if ($sheetC !== null) {
                $perSheet[] = $sheetC;
            }
        };

        // 2. Proses kronologis.
        foreach ($plan as $p) {
            $flushSheet();
            $res = $svc->dryRunSheet($p['file'], $p['sheet'], $p['date']);
            $sheetC = [
                'file' => $p['base'], 'date' => $p['date'], 'sheet' => $p['sheet'],
                'rows' => 0, 'valid' => 0, 'exception' => 0, 'ignored' => 0, 'excluded' => 0, 'needs' => 0, 'error' => 0, 'skipped' => 0, 'w' => 0.0, 'v' => 0.0,
            ];

            foreach ($res['rows'] as $row) {
                $sheetC['rows']++;
                $tot['scanned']++;
                $perFile[$p['base']]['tot']['scanned']++;
                $status = $row['status'];
                switch ($status) {
                    case 'VALID':
                    case 'HISTORICAL_EXCEPTION':
                        $key = $status === 'VALID' ? 'valid' : 'exception';
                        $label = $status === 'VALID'
                            ? $row['category_name']
                            : ($exceptionDisplay[$svc->normalize($row['material_label'])] ?? $row['material_label']);
                        $custId = $row['customer_id'];
                        $custName = $masterNames[$custId] ?? $row['nama'];
                        $sheetC[$key]++;
                        $sheetC['w'] += $row['weight'];
                        $sheetC['v'] += $row['subtotal'];
                        $tot[$key]++;
                        $perFile[$p['base']]['tot'][$key]++;
                        $perFile[$p['base']]['w'] += $row['weight'];
                        $perFile[$p['base']]['v'] += $row['subtotal'];
                        $weight += $row['weight'];
                        $value += $row['subtotal'];

                        $byCustomer[$custId]['name'] = $custName;
                        $byCustomer[$custId]['rows'] = ($byCustomer[$custId]['rows'] ?? 0) + 1;
                        $byCustomer[$custId]['w'] = ($byCustomer[$custId]['w'] ?? 0) + $row['weight'];
                        $byCustomer[$custId]['v'] = ($byCustomer[$custId]['v'] ?? 0) + $row['subtotal'];
                        $byMaterial[$label]['rows'] = ($byMaterial[$label]['rows'] ?? 0) + 1;
                        $byMaterial[$label]['w'] = ($byMaterial[$label]['w'] ?? 0) + $row['weight'];
                        $byMaterial[$label]['v'] = ($byMaterial[$label]['v'] ?? 0) + $row['subtotal'];
                        $dailyMaterial[$p['date']][$label] = ($dailyMaterial[$p['date']][$label] ?? 0) + $row['weight'];
                        if ($status === 'HISTORICAL_EXCEPTION') {
                            $byException[$label]['rows'] = ($byException[$label]['rows'] ?? 0) + 1;
                            $byException[$label]['w'] = ($byException[$label]['w'] ?? 0) + $row['weight'];
                            $byException[$label]['v'] = ($byException[$label]['v'] ?? 0) + $row['subtotal'];
                            $row['file'] = $p['base'];
                            $exceptionDetails[] = $row;
                        }
                        break;
                    case 'IGNORED':
                        $sheetC['ignored']++;
                        $tot['ignored']++;
                        $perFile[$p['base']]['tot']['ignored']++;
                        break;
                    case 'EXCLUDED':
                        $sheetC['excluded']++;
                        $tot['excluded']++;
                        $perFile[$p['base']]['tot']['excluded']++;
                        break;
                    case 'SKIPPED':
                        $sheetC['skipped']++;
                        $tot['skipped']++;
                        $perFile[$p['base']]['tot']['skipped']++;
                        $row['file'] = $p['base'];
                        $zeroDetails[] = $row;
                        break;
                    case 'NEEDS_MAPPING':
                        $sheetC['needs']++;
                        $tot['needs']++;
                        $perFile[$p['base']]['tot']['needs']++;
                        $row['file'] = $p['base'];
                        if (str_contains($row['reason'], 'Harga')) {
                            $missingPrice[$row['material'] ?: '(kosong)'] = true;
                        }
                        if (str_contains($row['reason'], 'UNKNOWN')) {
                            $unknownMats[$row['material']] = true;
                        }
                        if (str_contains($row['reason'], 'Nasabah') || str_contains($row['reason'], 'Dipo')) {
                            $ambigCust[$row['nama']] = $row['reason'];
                        }
                        $errorDetails[] = $row;
                        break;
                    default:
                        $sheetC['error']++;
                        $tot['error']++;
                        $perFile[$p['base']]['tot']['error']++;
                        $row['file'] = $p['base'];
                        $errorDetails[] = $row;
                }
            }
        }
        $flushSheet();

        // 3. Fingerprint per sheet (idempotency key, read-only).
        $importer = app(DepositImportService::class);
        foreach ($plan as $p) {
            $fingerprints[] = [
                'file' => $p['base'], 'date' => $p['date'], 'sheet' => $p['sheet'],
                'fp' => $importer->calculateFingerprint($p['file'], $p['sheet'], $p['date']),
            ];
        }

        // 4. Validasi cumulative stock kronologis (semua baris = deposit, tanpa sale).
        ksort($dailyMaterial);
        $cumulative = [];
        $negative = [];
        foreach ($dailyMaterial as $date => $mats) {
            foreach ($mats as $label => $w) {
                $cumulative[$label] = ($cumulative[$label] ?? 0) + $w;
                if ($cumulative[$label] < -0.001) {
                    $negative[] = "$date $label => {$cumulative[$label]}";
                }
            }
        }

        // ================= OUTPUT =================
        $this->line('');
        foreach ($perFile as $base => $pf) {
            $this->line("--- FILE: {$base} ---");
            $this->line('Sheets dilewati: '.implode(', ', $skippedSheetsAll[$base]));
            $t = $pf['tot'];
            $this->line(sprintf(
                '  scanned=%d valid=%d exception=%d ignored=%d excluded=%d needs=%d error=%d zero_weight=%d | weight=%s kg | value=Rp %s',
                $t['scanned'], $t['valid'], $t['exception'], $t['ignored'], $t['excluded'], $t['needs'], $t['error'], $t['skipped'],
                number_format($pf['w'], 2), number_format($pf['v'], 0, ',', '.')
            ));
        }

        $this->line('');
        $this->line('=== 4. TOTAL ROWS ===');
        $this->line('Scanned            : '.$tot['scanned']);
        $this->line('VALID              : '.$tot['valid']);
        $this->line('HISTORICAL_EXCEPTION: '.$tot['exception']);
        $this->line('EXCLUDED           : '.$tot['excluded']);
        $this->line('IGNORED            : '.$tot['ignored']);
        $this->line('ZERO_WEIGHT        : '.$tot['skipped']);
        $this->line('ERROR              : '.$tot['error']);
        $this->line('NEEDS_MAPPING      : '.$tot['needs']);
        $this->line('');
        $this->line('=== 5-6. TOTAL BERAT & NILAI (VALID + EXCEPTION) ===');
        $this->line('Total berat : '.number_format($weight, 2).' kg');
        $this->line('Total nilai : Rp '.number_format($value, 0, ',', '.'));

        $this->line('');
        $this->line('=== 7. BREAKDOWN HISTORICAL EXCEPTION ===');
        foreach ($byException as $label => $e) {
            $this->line(sprintf('  %-34s %3d row | %s kg | Rp %s', $label, $e['rows'], number_format($e['w'], 2), number_format($e['v'], 0, ',', '.')));
        }

        $this->line('');
        $this->line('=== 8. ERROR / DISCREPANCY ===');
        $this->line('Unknown materials: '.count($unknownMats).' ['.implode(', ', array_keys($unknownMats)).']');
        $this->line('Missing historical price: '.count($missingPrice).' ['.implode(', ', array_keys($missingPrice)).']');
        $this->line('Ambiguous customers: '.count($ambigCust).($ambigCust ? ' ['.implode('; ', $ambigCust).']' : ''));
        $this->line('Negative cumulative stock: '.count($negative).($negative ? ' ['.implode(', ', $negative).']' : ''));
        $this->line('Error rows: '.$tot['error']);
        foreach ($errorDetails as $d) {
            $this->line(sprintf(
                '  [%s] %s %s r%d | nama=[%s] material=[%s] harga=[%s] berat=[%s] => %s: %s',
                $d['file'], $d['date'], $d['sheet'], $d['excel_row'], $d['nama'], $d['material'],
                $d['harga_cell'] ?? 'NULL', $d['weight'] ?? 'NULL', $d['status'], $d['reason']
            ));
        }
        foreach ($zeroDetails as $d) {
            $this->line(sprintf(
                '  ZERO_WEIGHT [%s] %s %s r%d | nama=[%s] material=[%s] => %s',
                $d['file'], $d['date'], $d['sheet'], $d['excel_row'], $d['nama'], $d['material'], $d['reason']
            ));
        }

        $this->line('');
        $this->line('=== 9. REKONSILIASI PER TANGGAL ===');
        $this->line('| Date | Sheets | Rows | Valid | Exception | Ignored | Excluded | Zero | Needs | Error | Weight | Value |');
        $byDateAgg = [];
        foreach ($perSheet as $s) {
            $d = $s['date'];
            if (! isset($byDateAgg[$d])) {
                $byDateAgg[$d] = ['sheets' => 0, 'rows' => 0, 'valid' => 0, 'exception' => 0, 'ignored' => 0, 'excluded' => 0, 'needs' => 0, 'error' => 0, 'skipped' => 0, 'w' => 0.0, 'v' => 0.0];
            }
            foreach (['sheets'] as $k) {
                $byDateAgg[$d][$k]++;
            }
            foreach (['rows', 'valid', 'exception', 'ignored', 'excluded', 'needs', 'error', 'skipped', 'w', 'v'] as $k) {
                $byDateAgg[$d][$k] += $s[$k];
            }
        }
        ksort($byDateAgg);
        foreach ($byDateAgg as $d => $a) {
            $this->line(sprintf(
                '| %s | %d | %d | %d | %d | %d | %d | %d | %d | %d | %s | %s |',
                $d, $a['sheets'], $a['rows'], $a['valid'], $a['exception'], $a['ignored'], $a['excluded'], $a['skipped'], $a['needs'], $a['error'],
                number_format($a['w'], 2), number_format($a['v'], 0, ',', '.')
            ));
        }

        $this->line('');
        $this->line('=== 10. REKONSILIASI CUSTOMER (VALID + EXCEPTION) ===');
        $this->line('Customers (resolved): '.count($byCustomer));
        $this->line('| Customer | Rows | Weight | Value |');
        uasort($byCustomer, fn ($a, $b) => $b['w'] <=> $a['w']);
        foreach ($byCustomer as $c) {
            $this->line(sprintf('| %s | %d | %s | %s |', $c['name'], $c['rows'], number_format($c['w'], 2), number_format($c['v'], 0, ',', '.')));
        }
        $this->line(sprintf('TOTAL customer: rows=%d weight=%s value=%s', array_sum(array_column($byCustomer, 'rows')), number_format(array_sum(array_column($byCustomer, 'w')), 2), number_format(array_sum(array_column($byCustomer, 'v')), 0, ',', '.')));

        $this->line('');
        $this->line('=== 11. REKONSILIASI MATERIAL (VALID + EXCEPTION) ===');
        $this->line('Materials: '.count($byMaterial));
        $this->line('| Material | Rows | Weight | Value |');
        uasort($byMaterial, fn ($a, $b) => $b['w'] <=> $a['w']);
        foreach ($byMaterial as $name => $m) {
            $this->line(sprintf('| %s | %d | %s | %s |', $name, $m['rows'], number_format($m['w'], 2), number_format($m['v'], 0, ',', '.')));
        }
        $this->line(sprintf('TOTAL material: rows=%d weight=%s value=%s', array_sum(array_column($byMaterial, 'rows')), number_format(array_sum(array_column($byMaterial, 'w')), 2), number_format(array_sum(array_column($byMaterial, 'v')), 0, ',', '.')));

        $this->line('');
        $this->line('=== FINGERPRINT / IDEMPOTENCY (per sheet, read-only) ===');
        foreach ($fingerprints as $fp) {
            $this->line(sprintf('  %-28s %-10s %s', $fp['sheet'], $fp['date'], $fp['fp']));
        }

        $this->line('');
        $this->line('=== 12. DB CHECK (harus tetap 0) ===');
        $this->line('deposits='.DB::table('deposits')->count()
            .' deposit_details='.DB::table('deposit_details')->count()
            .' sales='.DB::table('sales')->count()
            .' savings_ledgers='.DB::table('savings_ledgers')->count());

        return self::SUCCESS;
    }
}
