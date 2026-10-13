<?php

namespace Database\Seeders;

use App\Models\SavingsLedger;
use App\Models\WasteCustomer;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder riwayat penarikan saldo nasabah, HANYA periode File B
 * (`storage/historical/Bank Sampah NEW.xlsx`, Mei - September 2026).
 *
 * Mengimpor 20 transaksi penarikan saldo (withdrawals) untuk 20 nasabah.
 * Total penarikan: Rp 3.238.395,00.
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
            ['customer' => 'Mbak Nurhayati', 'amount' => 180000.0, 'date' => '2026-09-07', 'notes' => 'Penarikan tabungan periode lanjutan'],
            ['customer' => 'Bu Betria', 'amount' => 300000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Hartinah', 'amount' => 77000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Ratu Tuti', 'amount' => 85000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Purwani', 'amount' => 38000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan periode lanjutan'],
            ['customer' => 'Bu Rumiyati', 'amount' => 125000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Wiwin', 'amount' => 150000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan periode lanjutan'],
            ['customer' => 'Bu Linda', 'amount' => 58000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Wuri', 'amount' => 200000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Tunut', 'amount' => 200000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Selly', 'amount' => 200000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Ecin Dwi', 'amount' => 200000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan periode lanjutan'],
            ['customer' => 'Syifa Kamila', 'amount' => 150000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Hadiyana', 'amount' => 500000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
            ['customer' => 'Bu Halimah', 'amount' => 200000.0, 'date' => '2026-09-12', 'notes' => 'Penarikan tabungan (Pencairan Tabungan)'],
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
