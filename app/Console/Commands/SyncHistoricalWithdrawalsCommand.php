<?php

namespace App\Console\Commands;

use App\Models\SavingsLedger;
use App\Models\WasteCustomer;
use App\Models\Withdrawal;
use Database\Seeders\HistoricalWithdrawalSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncHistoricalWithdrawalsCommand extends Command
{
    protected $signature = 'bank-sampah:sync-withdrawals {--dry-run : Lakukan simulasi tanpa menulis ke database}';

    protected $description = 'Sinkronisasi riwayat penarikan saldo nasabah dari sheet Tabungan Nasabah ke tabel withdrawals dan savings_ledgers';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $events = HistoricalWithdrawalSeeder::events();

        $this->line('=== SINKRONISASI PENARIKAN SALDO NASABAH (TABUNGAN NASABAH) ===');
        $this->line('Total transaksi penarikan: ' . count($events));

        $customers = WasteCustomer::all()->keyBy('name');
        $totalWithdrawal = 0.0;
        $matched = 0;
        $unmatched = 0;

        foreach ($events as $item) {
            $cat = $customers->get($item['customer']);
            if ($cat) {
                $matched++;
                $totalWithdrawal += (float) $item['amount'];
            } else {
                $unmatched++;
            }
        }

        $this->info("Nasabah cocok: {$matched} | Nasabah tidak ditemukan: {$unmatched}");
        $this->info("Total nominal penarikan yang akan dicatat: Rp " . number_format($totalWithdrawal, 2, ',', '.'));

        if ($dryRun) {
            $this->warn('[DRY-RUN] Tidak ada data yang ditulis ke database.');
            $this->table(
                ['Nasabah', 'Tanggal', 'Nominal (Rp)', 'Keterangan'],
                array_map(fn($e) => [
                    $e['customer'],
                    $e['date'],
                    number_format($e['amount'], 0, ',', '.'),
                    $e['notes'],
                ], array_slice($events, 0, 15))
            );
            if (count($events) > 15) {
                $this->line('... dan ' . (count($events) - 15) . ' transaksi penarikan lainnya.');
            }
            return self::SUCCESS;
        }

        $beforeWithdrawals = Withdrawal::count();
        $beforeDebits = SavingsLedger::where('type', 'debit')->count();

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\HistoricalWithdrawalSeeder',
            '--force' => true,
        ], $this->getOutput());

        $afterWithdrawals = Withdrawal::count();
        $afterDebits = SavingsLedger::where('type', 'debit')->count();

        $totalCredit = (float) SavingsLedger::where('type', 'credit')->sum('amount');
        $totalDebit = (float) SavingsLedger::where('type', 'debit')->sum('amount');
        $remainingBalance = $totalCredit - $totalDebit;

        $this->info("Berhasil!");
        $this->line("Data penarikan (Withdrawal): {$afterWithdrawals} (sebelumnya: {$beforeWithdrawals})");
        $this->line("Buku besar debit (SavingsLedger debit): {$afterDebits} (sebelumnya: {$beforeDebits})");
        $this->line("Total simpanan nasabah aktif: Rp " . number_format($remainingBalance, 2, ',', '.'));

        return self::SUCCESS;
    }
}
