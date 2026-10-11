<?php

namespace App\Console\Commands;

use App\Models\WasteBankCashLedger;
use App\Models\WasteBankExpense;
use App\Services\BankSampahService;
use Database\Seeders\HistoricalCashLedgerSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncHistoricalCashLedgerCommand extends Command
{
    protected $signature = 'bank-sampah:sync-cash-ledger {--dry-run : Lakukan simulasi tanpa menulis ke database}';

    protected $description = 'Sinkronisasi buku kas operasional dari sheet Kas Baru ke tabel waste_bank_cash_ledgers dan waste_bank_expenses';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $rows = HistoricalCashLedgerSeeder::rows();

        $this->line('=== SINKRONISASI BUKU KAS OPERASIONAL (KAS BARU) ===');
        $this->line('Total baris transaksi: ' . count($rows));

        $totalIn = 0.0;
        $totalOut = 0.0;
        $runningBalance = 0.0;

        foreach ($rows as $item) {
            $masuk = $item['masuk'];
            $keluar = $item['keluar'];

            if ($masuk !== null) {
                if ($masuk < 0) {
                    $totalOut += abs($masuk);
                    $runningBalance -= abs($masuk);
                } else {
                    $totalIn += $masuk;
                    $runningBalance += $masuk;
                }
            } else {
                $totalOut += (float) $keluar;
                $runningBalance -= (float) $keluar;
            }
        }

        $this->info(sprintf(
            "Total Kas Masuk : Rp %s\nTotal Kas Keluar: Rp %s\nSaldo Akhir Kas : Rp %s",
            number_format($totalIn, 2, ',', '.'),
            number_format($totalOut, 2, ',', '.'),
            number_format($runningBalance, 2, ',', '.')
        ));

        if ($dryRun) {
            $this->warn('[DRY-RUN] Tidak ada data yang ditulis ke database.');
            return self::SUCCESS;
        }

        $beforeLedgers = WasteBankCashLedger::count();
        $beforeExpenses = WasteBankExpense::count();

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\HistoricalCashLedgerSeeder',
            '--force' => true,
        ], $this->getOutput());

        $afterLedgers = WasteBankCashLedger::count();
        $afterExpenses = WasteBankExpense::count();
        $finalBalance = app(BankSampahService::class)->getWasteBankBalance();

        $this->info("Berhasil!");
        $this->line("Buku besar kas (WasteBankCashLedger): {$afterLedgers} (sebelumnya: {$beforeLedgers})");
        $this->line("Beban pengeluaran (WasteBankExpense): {$afterExpenses} (sebelumnya: {$beforeExpenses})");
        $this->line("Saldo kas aktif saat ini: Rp " . number_format($finalBalance, 2, ',', '.'));

        return self::SUCCESS;
    }
}
