<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\SavingsLedger;
use App\Services\BankSampahService;
use App\Services\DepositImportService;
use App\Services\HistoricalDepositDryRunService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * COMMIT historical deposit import.
 *
 * - Kronologis (urut tanggal) lintas file.
 * - Idempotent per-sheet via fingerprint (Cache + deposits.notes [FP:...]).
 * - Transactional: seluruh batch satu DB transaction; gagal => rollback.
 * - Tidak mengubah master, tidak sync member_price, tidak auto-create.
 * - Historical exception disimpan sebagai material_label (waste_category_id NULL).
 * - ZERO_WEIGHT / EXCLUDED / IGNORED tidak membuat transaksi.
 */
class HistoricalDepositCommit extends Command
{
    protected $signature = 'historical:deposit-commit
        {files* : Path workbook, urut kronologis}
        {--batch= : Kode batch (opsional)}';

    protected $description = 'Commit historical deposit import (kronologis, idempotent, transactional)';

    public function handle(
        HistoricalDepositDryRunService $svc,
        DepositImportService $importer,
        BankSampahService $bank
    ): int {
        $files = (array) $this->argument('files');
        foreach ($files as $f) {
            if (! is_file($f)) {
                $this->error("File tidak ditemukan: {$f}");

                return self::FAILURE;
            }
        }

        $batch = $this->option('batch') ?: 'HIST-'.date('Ymd-His');

        // 1. Rencana kronologis.
        $plan = [];
        foreach ($files as $file) {
            $base = basename($file);
            $reader = IOFactory::createReaderForFile($file);
            $sheets = $reader->listWorksheetNames($file);
            $dated = [];
            foreach ($sheets as $s) {
                if (in_array($s, HistoricalDepositDryRunService::EXCLUDED_SHEET_NAMES, true)) {
                    continue;
                }
                $d = $svc->guessDate($s);
                if ($d) {
                    $dated[$s] = $d;
                }
            }
            asort($dated);
            foreach ($dated as $sheet => $date) {
                $plan[] = ['file' => $file, 'base' => $base, 'sheet' => $sheet, 'date' => $date];
            }
        }
        usort($plan, fn ($a, $b) => [$a['date'], $a['base'], $a['sheet']] <=> [$b['date'], $b['base'], $b['sheet']]);

        // 2. Fase plan (DRY-RUN, read-only): hitung totals + grup per sheet.
        $sheetsData = [];
        $planned = [
            'valid' => 0, 'exception' => 0, 'excluded' => 0, 'ignored' => 0,
            'zero' => 0, 'error' => 0, 'needs' => 0,
            'weight' => 0.0, 'value' => 0.0,
            'new_deposits' => 0, 'new_details' => 0, 'new_exceptions' => 0,
            'new_exception_weight' => 0.0, 'new_exception_value' => 0.0,
        ];

        foreach ($plan as $p) {
            $fp = $importer->calculateFingerprint($p['file'], $p['sheet'], $p['date']);
            $already = $importer->isFingerprintImported($fp);
            $res = $svc->dryRunSheet($p['file'], $p['sheet'], $p['date']);

            $groups = [];
            foreach ($res['rows'] as $row) {
                switch ($row['status']) {
                    case 'VALID':
                    case 'HISTORICAL_EXCEPTION':
                        $isExc = $row['status'] === 'HISTORICAL_EXCEPTION';
                        $groups[$row['customer_id']][] = $row;
                        $planned[$isExc ? 'exception' : 'valid']++;
                        $planned['weight'] += $row['weight'];
                        $planned['value'] += $row['subtotal'];
                        if (! $already) {
                            $planned['new_details']++;
                            if ($isExc) {
                                $planned['new_exceptions']++;
                                $planned['new_exception_weight'] += $row['weight'];
                                $planned['new_exception_value'] += $row['subtotal'];
                            }
                        }
                        break;
                    case 'EXCLUDED':
                        $planned['excluded']++;
                        break;
                    case 'IGNORED':
                        $planned['ignored']++;
                        break;
                    case 'SKIPPED':
                        $planned['zero']++;
                        break;
                    case 'NEEDS_MAPPING':
                        $planned['needs']++;
                        break;
                    default:
                        $planned['error']++;
                }
            }
            if (! $already) {
                $planned['new_deposits'] += count($groups);
            }
            $sheetsData[] = ['plan' => $p, 'fp' => $fp, 'already' => $already, 'groups' => $groups];
        }

        // 3. Tampilkan PLANNED sebelum commit.
        $this->line('=== PLANNED (dari dry-run) — SEBELUM COMMIT ===');
        $this->line('Batch: '.$batch);
        $this->line('Sheets: '.count($plan).' ('.count(array_filter($sheetsData, fn ($s) => $s['already'])).' sudah pernah diimpor)');
        $this->line('VALID='.$planned['valid'].' EXCEPTION='.$planned['exception']
            .' EXCLUDED='.$planned['excluded'].' IGNORED='.$planned['ignored']
            .' ZERO_WEIGHT='.$planned['zero'].' ERROR='.$planned['error'].' NEEDS_MAPPING='.$planned['needs']);
        $this->line('Weight='.number_format($planned['weight'], 2).' kg | Value=Rp '.number_format($planned['value'], 0, ',', '.'));
        $this->line('NEW (belum diimpor): deposits='.$planned['new_deposits'].' details='.$planned['new_details']
            .' exception='.$planned['new_exceptions'].' weight='.number_format($planned['weight'], 2).' value=Rp '.number_format($planned['value'], 0, ',', '.'));

        // Guard: hanya boleh commit saat dry-run bersih.
        if ($planned['needs'] > 0 || $planned['error'] > 0) {
            $this->error('STOP: masih ada NEEDS_MAPPING/ERROR. Commit dibatalkan.');

            return self::FAILURE;
        }

        // 4. Baseline DB (untuk verifikasi delta).
        $before = [
            'deposits' => Deposit::count(),
            'details' => DepositDetail::count(),
            'weight' => (float) DepositDetail::sum('weight'),
            'subtotal' => (float) DepositDetail::sum('subtotal'),
            'ledgers' => SavingsLedger::where('type', 'credit')->count(),
            'ledger_amount' => (float) SavingsLedger::where('type', 'credit')->sum('amount'),
            'exceptions' => DepositDetail::whereNull('waste_category_id')->count(),
        ];

        // 5. COMMIT (satu transaction).
        $createdDeposits = 0;
        $createdDetails = 0;
        $touchedSheets = [];

        DB::transaction(function () use ($sheetsData, $bank, $batch, $before, &$createdDeposits, &$createdDetails, &$touchedSheets) {
            foreach ($sheetsData as $sd) {
                if ($sd['already']) {
                    continue;
                }
                $sheet = $sd['plan']['sheet'];
                $date = $sd['plan']['date'];

                foreach ($sd['groups'] as $customerId => $rows) {
                    $details = [];
                    foreach ($rows as $row) {
                        $details[] = [
                            'waste_category_id' => $row['category_id'] ?? null,
                            'material_label' => $row['material_label'] ?? null,
                            'weight' => $row['weight'],
                            'price_per_unit' => $row['used_price'],
                            'subtotal' => $row['subtotal'],
                        ];
                    }
                    $bank->recordDeposit([
                        'waste_customer_id' => $customerId,
                        'date' => $date,
                        'notes' => "Historical import (Sheet: {$sheet}) [Batch: {$batch}] [FP: {$sd['fp']}]",
                        'details' => $details,
                    ]);
                    $createdDeposits++;
                    $createdDetails += count($details);
                }
                $touchedSheets[] = $sd['fp'];
            }

            // Verifikasi di dalam transaction: mismatch => rollback.
            $after = [
                'deposits' => Deposit::count(),
                'details' => DepositDetail::count(),
                'weight' => (float) DepositDetail::sum('weight'),
                'subtotal' => (float) DepositDetail::sum('subtotal'),
                'ledgers' => SavingsLedger::where('type', 'credit')->count(),
                'ledger_amount' => (float) SavingsLedger::where('type', 'credit')->sum('amount'),
            ];
            $expected = [
                'deposits' => $before['deposits'] + collect($sheetsData)->filter(fn ($s) => ! $s['already'])->sum(fn ($s) => count($s['groups'])),
                'details' => $before['details'] + (int) collect($sheetsData)->filter(fn ($s) => ! $s['already'])->sum(function ($s) {
                    return collect($s['groups'])->sum(fn ($rows) => count($rows));
                }),
                'weight' => $before['weight'] + (float) collect($sheetsData)->filter(fn ($s) => ! $s['already'])->sum(function ($s) {
                    return collect($s['groups'])->sum(fn ($rows) => collect($rows)->sum('weight'));
                }),
                'subtotal' => $before['subtotal'] + (float) collect($sheetsData)->filter(fn ($s) => ! $s['already'])->sum(function ($s) {
                    return collect($s['groups'])->sum(fn ($rows) => collect($rows)->sum('subtotal'));
                }),
            ];
            $expected['ledgers'] = $expected['deposits'];
            $expected['ledger_amount'] = $expected['subtotal'];

            foreach (['deposits', 'details', 'weight', 'subtotal', 'ledgers', 'ledger_amount'] as $k) {
                $a = round((float) $after[$k], 2);
                $e = round((float) $expected[$k], 2);
                if (abs($a - $e) > 0.01) {
                    throw new \RuntimeException("Verifikasi commit gagal pada '{$k}': after={$a} expected={$e}. Rollback.");
                }
            }
        });

        // 6. Catat fingerprint setelah transaction sukses.
        foreach ($touchedSheets as $fp) {
            Cache::forever("deposit_import_fp_{$fp}", true);
        }

        // 7. Verifikasi akhir + output.
        $afterFull = [
            'deposits' => Deposit::count(),
            'details' => DepositDetail::count(),
            'weight' => (float) DepositDetail::sum('weight'),
            'subtotal' => (float) DepositDetail::sum('subtotal'),
            'ledgers' => SavingsLedger::where('type', 'credit')->count(),
            'ledger_amount' => (float) SavingsLedger::where('type', 'credit')->sum('amount'),
            'exceptions' => DepositDetail::whereNull('waste_category_id')->count(),
            'exception_weight' => (float) DepositDetail::whereNull('waste_category_id')->sum('weight'),
            'exception_value' => (float) DepositDetail::whereNull('waste_category_id')->sum('subtotal'),
        ];

        $this->line('');
        $this->line('=== COMMIT RESULT ===');
        $this->line("Deposit dibuat   : {$createdDeposits}");
        $this->line("Detail dibuat    : {$createdDetails}");
        $this->line('Sheets committed : '.count($touchedSheets));
        $this->line('');
        $this->line('=== BEFORE / AFTER DB ===');
        $this->line(sprintf('deposits        : %d -> %d', $before['deposits'], $afterFull['deposits']));
        $this->line(sprintf('deposit_details : %d -> %d', $before['details'], $afterFull['details']));
        $this->line(sprintf('total weight    : %s -> %s kg', number_format($before['weight'], 2), number_format($afterFull['weight'], 2)));
        $this->line(sprintf('total subtotal  : Rp %s -> Rp %s', number_format($before['subtotal'], 0, ',', '.'), number_format($afterFull['subtotal'], 0, ',', '.')));
        $this->line(sprintf('savings_ledgers : %d -> %d', $before['ledgers'], $afterFull['ledgers']));
        $this->line(sprintf('ledger amount   : Rp %s -> Rp %s', number_format($before['ledger_amount'], 0, ',', '.'), number_format($afterFull['ledger_amount'], 0, ',', '.')));
        $this->line('');
        $this->line('=== HISTORICAL EXCEPTION ===');
        $this->line('rows='.$afterFull['exceptions'].' weight='.number_format($afterFull['exception_weight'], 2)
            .' kg value=Rp '.number_format($afterFull['exception_value'], 0, ',', '.'));
        $this->line('');
        $this->line('=== NON-IMPORTED (expected dari dry-run) ===');
        $this->line('EXCLUDED='.$planned['excluded'].' IGNORED='.$planned['ignored'].' ZERO_WEIGHT='.$planned['zero']
            .' ERROR='.$planned['error'].' NEEDS_MAPPING='.$planned['needs']);
        $this->line('Fingerprint sheets committed: '.count($touchedSheets).' / '.count($plan));

        return self::SUCCESS;
    }
}
