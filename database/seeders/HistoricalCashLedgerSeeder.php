<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WasteBankCashLedger;
use App\Models\WasteBankExpense;
use App\Services\BankSampahService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder migrasi buku kas operasional dari sheet 'Kas Baru'
 * (docs/Bank Sampah NEW.xlsx).
 *
 * Mengimpor 57 baris transaksi:
 * - 41 beban pengeluaran operasional (WasteBankExpense + WasteBankCashLedger out)
 * - 6 pemasukan margin penjualan (WasteBankCashLedger in)
 * - 1 koreksi margin penjualan minus (WasteBankCashLedger out)
 * - 6 pemasukan non-penjualan: sedot WC & deposit PKK (WasteBankCashLedger in)
 * - 3 pengeluaran pencairan tabungan nasabah (WasteBankCashLedger out)
 *
 * Saldo akhir: Rp 162.953,50 (100% presisi dengan buku kas fisik).
 * Idempotent: aman dijalankan berulang.
 */
class HistoricalCashLedgerSeeder extends Seeder
{
    /**
     * @return array<int, array{row: int, date: string, desc: string, masuk: ?float, keluar: ?float, note: ?string}>
     */
    public static function rows(): array
    {
        return [
            ['row' => 4, 'date' => '2026-05-09', 'desc' => 'Keuntungan Penjualan', 'masuk' => 184227.5, 'keluar' => null, 'note' => null],
            ['row' => 5, 'date' => '2026-05-09', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 40000.0, 'note' => 'Bubur Kacang Ijo'],
            ['row' => 6, 'date' => '2026-05-09', 'desc' => 'P3K', 'masuk' => null, 'keluar' => 5000.0, 'note' => 'Handsaplast'],
            ['row' => 7, 'date' => '2026-05-09', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 69000.0, 'note' => 'Es Kelapa'],
            ['row' => 8, 'date' => '2026-05-09', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 5000.0, 'note' => 'Kertas Nasi'],
            ['row' => 9, 'date' => '2026-05-09', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 10000.0, 'note' => 'Sarung Tangan Plastik'],
            ['row' => 10, 'date' => '2026-05-09', 'desc' => 'Tip Supir', 'masuk' => null, 'keluar' => 20000.0, 'note' => null],
            ['row' => 11, 'date' => '2026-05-17', 'desc' => 'Sedot WC Bulan Mei', 'masuk' => 240000.0, 'keluar' => null, 'note' => null],
            ['row' => 12, 'date' => '2026-05-24', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 52000.0, 'note' => 'Cat Pot'],
            ['row' => 13, 'date' => '2026-05-24', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 10000.0, 'note' => 'Paku'],
            ['row' => 14, 'date' => '2026-05-26', 'desc' => 'Deposit PKK', 'masuk' => 100000.0, 'keluar' => null, 'note' => null],
            ['row' => 15, 'date' => '2026-05-30', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 65000.0, 'note' => 'Soto'],
            ['row' => 16, 'date' => '2026-05-30', 'desc' => 'Tip Supir', 'masuk' => null, 'keluar' => 20000.0, 'note' => null],
            ['row' => 17, 'date' => '2026-05-30', 'desc' => 'Keuntungan Penjualan', 'masuk' => 558142.5, 'keluar' => null, 'note' => null],
            ['row' => 18, 'date' => '2026-06-05', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 313500.0, 'note' => 'Kawat'],
            ['row' => 19, 'date' => '2026-06-07', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 185000.0, 'note' => 'Besi ke Pak Surat'],
            ['row' => 20, 'date' => '2026-06-08', 'desc' => 'Sedot WC Bulan Juni', 'masuk' => 120000.0, 'keluar' => null, 'note' => null],
            ['row' => 21, 'date' => '2026-06-20', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 12000.0, 'note' => 'Paku'],
            ['row' => 22, 'date' => '2026-06-20', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 15000.0, 'note' => 'Baja ke Pak Surat'],
            ['row' => 23, 'date' => '2026-06-20', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 25000.0, 'note' => 'Engsel'],
            ['row' => 24, 'date' => '2026-06-20', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 15000.0, 'note' => 'Kabel'],
            ['row' => 25, 'date' => '2026-06-21', 'desc' => 'Keuntungan Penjualan', 'masuk' => -69134.0, 'keluar' => null, 'note' => null],
            ['row' => 26, 'date' => '2026-06-22', 'desc' => 'Tip Supir', 'masuk' => null, 'keluar' => 50000.0, 'note' => 'Tip Supir+Kopi'],
            ['row' => 27, 'date' => '2026-06-27', 'desc' => 'Tabungan', 'masuk' => null, 'keluar' => 200000.0, 'note' => null],
            ['row' => 28, 'date' => '2026-06-27', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 25000.0, 'note' => 'Tanah'],
            ['row' => 29, 'date' => '2026-07-07', 'desc' => 'Sedot WC Bulan Juli', 'masuk' => 30000.0, 'keluar' => null, 'note' => null],
            ['row' => 30, 'date' => '2026-07-11', 'desc' => 'Keuntungan Penjualan', 'masuk' => 273768.0, 'keluar' => null, 'note' => null],
            ['row' => 31, 'date' => '2026-07-11', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 99000.0, 'note' => 'Mie ayam'],
            ['row' => 32, 'date' => '2026-07-11', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 30000.0, 'note' => 'Sop buah'],
            ['row' => 33, 'date' => '2026-07-11', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 11000.0, 'note' => 'Kelapa'],
            ['row' => 34, 'date' => '2026-07-11', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 15000.0, 'note' => 'Sekrup'],
            ['row' => 35, 'date' => '2026-07-11', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 14000.0, 'note' => 'Air+sendok'],
            ['row' => 36, 'date' => '2026-07-11', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 24000.0, 'note' => 'Nasi ayam'],
            ['row' => 37, 'date' => '2026-07-11', 'desc' => 'Tip Supir', 'masuk' => null, 'keluar' => 20000.0, 'note' => null],
            ['row' => 38, 'date' => '2026-07-18', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 55000.0, 'note' => 'Rapat Inti'],
            ['row' => 39, 'date' => '2026-07-24', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 40000.0, 'note' => 'Karung'],
            ['row' => 40, 'date' => '2026-08-01', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 105000.0, 'note' => 'Sop Bakso'],
            ['row' => 41, 'date' => '2026-08-01', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 55000.0, 'note' => 'Rujak, Es Jeruk'],
            ['row' => 42, 'date' => '2026-08-01', 'desc' => 'Keuntungan Penjualan', 'masuk' => 123851.0, 'keluar' => null, 'note' => null],
            ['row' => 43, 'date' => '2026-08-08', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 50000.0, 'note' => 'Sosis dan Tofu'],
            ['row' => 44, 'date' => '2026-08-08', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 40000.0, 'note' => 'Kabel'],
            ['row' => 45, 'date' => '2026-08-08', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 20000.0, 'note' => 'Saklar'],
            ['row' => 46, 'date' => '2026-08-08', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 46000.0, 'note' => 'Kabel dan Fissher'],
            ['row' => 47, 'date' => '2026-08-11', 'desc' => 'Sedot WC Bulan Agustus', 'masuk' => 120000.0, 'keluar' => null, 'note' => null],
            ['row' => 48, 'date' => '2026-08-22', 'desc' => 'Keuntungan Penjualan', 'masuk' => 721654.0, 'keluar' => null, 'note' => null],
            ['row' => 49, 'date' => '2026-08-22', 'desc' => 'Tip Supir', 'masuk' => null, 'keluar' => 40000.0, 'note' => null],
            ['row' => 50, 'date' => '2026-09-01', 'desc' => 'Peralatan Bank Sampah', 'masuk' => null, 'keluar' => 73500.0, 'note' => 'Selang Air'],
            ['row' => 51, 'date' => '2026-09-01', 'desc' => 'Sedot WC Bulan September', 'masuk' => 30000.0, 'keluar' => null, 'note' => null],
            ['row' => 52, 'date' => '2026-09-08', 'desc' => 'Tabungan', 'masuk' => null, 'keluar' => 200000.0, 'note' => null],
            ['row' => 53, 'date' => '2026-09-10', 'desc' => 'BPJS Anggota', 'masuk' => null, 'keluar' => 130463.0, 'note' => null],
            ['row' => 54, 'date' => '2026-09-12', 'desc' => 'Keuntungan Penjualan', 'masuk' => 504907.5, 'keluar' => null, 'note' => null],
            ['row' => 55, 'date' => '2026-09-12', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 170000.0, 'note' => 'Hokben'],
            ['row' => 56, 'date' => '2026-09-12', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 30000.0, 'note' => 'Bubur Kacang Ijo'],
            ['row' => 57, 'date' => '2026-09-12', 'desc' => 'Parkir', 'masuk' => null, 'keluar' => 10000.0, 'note' => null],
            ['row' => 58, 'date' => '2026-09-12', 'desc' => 'Tip Supir', 'masuk' => null, 'keluar' => 35000.0, 'note' => null],
            ['row' => 59, 'date' => '2026-09-12', 'desc' => 'Tabungan', 'masuk' => null, 'keluar' => 300000.0, 'note' => null],
            ['row' => 60, 'date' => '2026-09-12', 'desc' => 'Konsumsi Anggota', 'masuk' => null, 'keluar' => 25000.0, 'note' => 'Jajan'],
        ];
    }

    public function run(): void
    {
        $admin = User::role('admin_bank_sampah')->first() ?? User::role('admin_rw')->first() ?? User::first();
        $adminId = $admin?->id;

        $rows = self::rows();
        $runningBalance = 0.0;

        $createdExpenses = 0;
        $createdLedgers = 0;

        DB::transaction(function () use ($rows, $adminId, &$runningBalance, &$createdExpenses, &$createdLedgers) {
            foreach ($rows as $item) {
                $rowNum = $item['row'];
                $date = $item['date'];
                $desc = $item['desc'];
                $note = $item['note'];
                $masuk = $item['masuk'];
                $keluar = $item['keluar'];

                $isExpense = ($keluar !== null && $desc !== 'Tabungan');
                $isNegativeSale = ($masuk !== null && $masuk < 0);
                $isTabungan = ($desc === 'Tabungan');

                if ($masuk !== null) {
                    if ($isNegativeSale) {
                        $type = 'out';
                        $amount = abs($masuk);
                        $runningBalance -= $amount;
                        $ledgerDesc = 'Koreksi Margin Penjualan (Minus)' . ($note ? " - {$note}" : '');
                    } else {
                        $type = 'in';
                        $amount = $masuk;
                        $runningBalance += $amount;
                        $ledgerDesc = $desc . ($note ? " - {$note}" : '');
                    }
                } else {
                    $type = 'out';
                    $amount = (float) $keluar;
                    $runningBalance -= $amount;
                    if ($isTabungan) {
                        $ledgerDesc = 'Penarikan Tabungan Nasabah (Kas Keluar)' . ($note ? " - {$note}" : '');
                    } else {
                        $ledgerDesc = $desc . ($note ? " - {$note}" : '');
                    }
                }

                if ($isExpense) {
                    $expenseCode = sprintf('EXP-H-%04d', $rowNum);
                    $expenseDesc = $desc . ($note ? " - {$note}" : '');

                    $expense = WasteBankExpense::updateOrCreate(
                        ['expense_code' => $expenseCode],
                        [
                            'amount' => $amount,
                            'description' => $expenseDesc,
                            'expense_date' => $date,
                            'recorded_by' => $adminId,
                        ]
                    );

                    $createdExpenses++;

                    WasteBankCashLedger::updateOrCreate(
                        [
                            'reference_type' => WasteBankExpense::class,
                            'reference_id'   => $expense->id,
                        ],
                        [
                            'type'        => 'out',
                            'amount'      => $amount,
                            'balance'     => $runningBalance,
                            'date'        => $date,
                            'description' => "Pengeluaran operasional [{$expenseCode}]: {$expenseDesc}",
                        ]
                    );

                    $createdLedgers++;
                } else {
                    WasteBankCashLedger::updateOrCreate(
                        [
                            'reference_type' => 'KAS_BARU',
                            'reference_id'   => $rowNum,
                        ],
                        [
                            'type'        => $type,
                            'amount'      => $amount,
                            'balance'     => $runningBalance,
                            'date'        => $date,
                            'description' => $ledgerDesc,
                        ]
                    );

                    $createdLedgers++;
                }
            }

            // Recalculate balances secara menyeluruh untuk memastikan rantai saldo 100% konsisten
            app(BankSampahService::class)->recalculateWasteBankBalances();
        });

        $finalBalance = app(BankSampahService::class)->getWasteBankBalance();
        $this->command?->info("Kas Baru berhasil disinkronkan: {$createdExpenses} beban pengeluaran, {$createdLedgers} buku besar kas. Saldo akhir: Rp " . number_format($finalBalance, 2, ',', '.'));
    }
}
