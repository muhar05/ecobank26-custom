<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Master Seeder untuk seluruh data historis Bank Sampah.
 *
 * Menggabungkan seluruh data master, harga, kas operasional, penarikan,
 * dan penjualan lapak ke dalam satu eksekusi seeder yang idempotent.
 *
 * Urutan dependensi:
 * 1. HistoricalMasterDataSeeder (Kategori, Alias, Agregator)
 * 2. HistoricalCustomerSeeder   (Nasabah Bank Sampah)
 * 3. KamiBoxPriceSeeder         (Katalog Harga KamiBox)
 * 4. HistoricalDepositSeeder    (Setoran nasabah)
 * 5. HistoricalCashLedgerSeeder (Buku Kas & Beban Operasional)
 * 6. HistoricalWithdrawalSeeder (Penarikan Saldo Nasabah)
 * 7. HistoricalSalesSeeder      (Penjualan Sampah ke Lapak)
 */
class BankSampahHistoricalSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HistoricalMasterDataSeeder::class,
            HistoricalCustomerSeeder::class,
            KamiBoxPriceSeeder::class,
            HistoricalDepositSeeder::class,
            HistoricalCashLedgerSeeder::class,
            HistoricalWithdrawalSeeder::class,
            HistoricalSalesSeeder::class,
        ]);
    }
}
