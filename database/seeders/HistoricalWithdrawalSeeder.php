<?php

namespace Database\Seeders;

use App\Models\SavingsLedger;
use App\Models\WasteCustomer;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder migrasi riwayat penarikan saldo nasabah dari sheet 'Tabungan Nasabah'
 * (docs/Bank Sampah NEW.xlsx & storage/historical/Bank-Sampah-2025-2026.xlsx).
 *
 * Mengimpor 43 transaksi penarikan saldo (withdrawals) untuk 38 nasabah.
 * Total penarikan: Rp 5.416.730,00.
 *
 * Idempotent: aman dijalankan berulang.
 */
class HistoricalWithdrawalSeeder extends Seeder
{
    /**
     * @return array<int, array{customer: string, amount: float, date: string, notes: string}>
     */
    public static function events(): array
    {
        return [
            ['customer' => 'Bu Betria', 'amount' => 300000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Maryani', 'amount' => 20000.0, 'date' => '2025-10-19', 'notes' => 'Penarikan tabungan (Cash)'],
            ['customer' => 'Bu Alim', 'amount' => 44000.0, 'date' => '2025-10-23', 'notes' => 'Penarikan tabungan (TF)'],
            ['customer' => 'Bu Angga', 'amount' => 200000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Bu Fardani', 'amount' => 17560.0, 'date' => '2025-08-02', 'notes' => 'Penarikan tabungan (Cash)'],
            ['customer' => 'Bu Hartinah', 'amount' => 77000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Lia RT 2', 'amount' => 63000.0, 'date' => '2025-10-01', 'notes' => 'Penarikan tabungan (TF)'],
            ['customer' => 'Bu Martinah', 'amount' => 25000.0, 'date' => '2025-10-23', 'notes' => 'Penarikan tabungan (TF)'],
            ['customer' => 'Bu Ratu Tuti', 'amount' => 85000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Widodo', 'amount' => 140000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Mbak Nurhayati', 'amount' => 190000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Transfer)'],
            ['customer' => 'Mbak Nurhayati', 'amount' => 180000.0, 'date' => '2026-09-07', 'notes' => 'Penarikan tabungan periode lanjutan'],
            ['customer' => 'Pak Suprayitno', 'amount' => 29000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Pak Wawan', 'amount' => 100000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Bu Purwani', 'amount' => 141000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Bu Purwani', 'amount' => 38000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan periode lanjutan'],
            ['customer' => 'Bu Rumiyati', 'amount' => 125000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Yayuk', 'amount' => 19572.5, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (dipindah ke bu wiwin)'],
            ['customer' => 'Bu Wiwin', 'amount' => 130427.5, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Bu Wiwin', 'amount' => 150000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan periode lanjutan'],
            ['customer' => 'Bu Ani', 'amount' => 50000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Bu Linda', 'amount' => 58000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Irma', 'amount' => 100000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Bu Neni', 'amount' => 200000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Bu Wuri', 'amount' => 200000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Tunut', 'amount' => 200000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Selly', 'amount' => 200000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Mba Dian', 'amount' => 131000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Bu Arsudin', 'amount' => 41200.0, 'date' => '2025-08-09', 'notes' => 'Penarikan tabungan (Cash)'],
            ['customer' => 'Bu Ecin Dwi', 'amount' => 100000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Bu Ecin Dwi', 'amount' => 200000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan periode lanjutan'],
            ['customer' => 'Bu Sri Retno', 'amount' => 80000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Bu Uum', 'amount' => 110000.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Syifa Kamila', 'amount' => 150000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Hadiyana', 'amount' => 500000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Halimah', 'amount' => 200000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Devilie', 'amount' => 113000.0, 'date' => '2025-09-07', 'notes' => 'Penarikan tabungan (Cash)'],
            ['customer' => 'Bu Ani RW 21', 'amount' => 133575.0, 'date' => '2026-04-26', 'notes' => 'Penarikan tabungan (Pencairan Tabungan Periode 2025/2026)'],
            ['customer' => 'Bu Ani RW 21', 'amount' => 106400.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan periode lanjutan'],
            ['customer' => 'Bu Sinta UE', 'amount' => 11300.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Gloria RT 5', 'amount' => 94000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Tria Blok Uc', 'amount' => 319695.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Aruna RT 5', 'amount' => 44000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
        ];
    }

    public function run(): void
    {
        $customers = WasteCustomer::all()->keyBy('name');
        $events = self::events();

        $createdWithdrawals = 0;
        $createdLedgers = 0;

        DB::transaction(function () use ($customers, $events, &$createdWithdrawals, &$createdLedgers) {
            foreach ($events as $item) {
                $customer = $customers->get($item['customer']);
                if (! $customer) {
                    continue;
                }

                $amount = (float) $item['amount'];
                $date = $item['date'];
                $notes = $item['notes'];

                // 1. Update or create Withdrawal (using whereDate for cross-DB SQLite/MySQL consistency)
                $withdrawal = Withdrawal::where('waste_customer_id', $customer->id)
                    ->whereDate('date', $date)
                    ->where('amount', $amount)
                    ->first();

                if ($withdrawal) {
                    $withdrawal->update(['notes' => $notes]);
                } else {
                    $withdrawal = Withdrawal::create([
                        'waste_customer_id' => $customer->id,
                        'date'              => $date,
                        'amount'            => $amount,
                        'notes'             => $notes,
                    ]);
                }

                $createdWithdrawals++;

                // 2. Update or create corresponding SavingsLedger debit
                SavingsLedger::updateOrCreate(
                    [
                        'waste_customer_id' => $customer->id,
                        'reference_type'    => Withdrawal::class,
                        'reference_id'      => $withdrawal->id,
                    ],
                    [
                        'type'        => 'debit',
                        'amount'      => $amount,
                        'description' => $notes,
                    ]
                );

                $createdLedgers++;
            }
        });

        $this->command?->info("Penarikan saldo historis berhasil disinkronkan: {$createdWithdrawals} penarikan, {$createdLedgers} buku besar tabungan (debit).");
    }
}
