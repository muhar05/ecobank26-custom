<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\SaleDetail;
use Database\Seeders\HistoricalSalesSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncHistoricalSalesCommand extends Command
{
    protected $signature = 'bank-sampah:sync-sales {--dry-run : Lakukan simulasi tanpa menulis ke database}';

    protected $description = 'Sinkronisasi data penjualan ke lapak (KamiBox & Non KamiBox) dari sheet transaksi bertanggal ke tabel sales dan sale_details';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sales = HistoricalSalesSeeder::sales();

        $this->line('=== SINKRONISASI PENJUALAN SAMPAH KE LAPAK / AGREGATOR ===');
        $this->line('Total transaksi penjualan: ' . count($sales));

        $totalWeight = 0.0;
        $totalAmount = 0.0;
        $totalMargin = 0.0;
        $totalDetails = 0;

        foreach ($sales as $s) {
            $totalAmount += (float) $s['total_amount'];
            foreach ($s['details'] as $d) {
                $totalDetails++;
                $totalWeight += (float) $d['weight'];
                $totalMargin += (float) $d['margin'];
            }
        }

        $this->info(sprintf(
            "Total Transaksi   : %d transaksi (%d baris detail)\nTotal Berat Muat  : %s kg\nTotal Nilai Jual  : Rp %s\nEstimasi Margin   : Rp %s",
            count($sales),
            $totalDetails,
            number_format($totalWeight, 2, ',', '.'),
            number_format($totalAmount, 2, ',', '.'),
            number_format($totalMargin, 2, ',', '.')
        ));

        if ($dryRun) {
            $this->warn('[DRY-RUN] Tidak ada data yang ditulis ke database.');
            $this->table(
                ['Tanggal', 'Agregator', 'Keterangan', 'Baris Material', 'Nilai Penjualan (Rp)'],
                array_map(fn($s) => [
                    $s['date'],
                    $s['collector'],
                    $s['notes'],
                    count($s['details']),
                    number_format($s['total_amount'], 0, ',', '.'),
                ], $sales)
            );
            return self::SUCCESS;
        }

        $beforeSales = Sale::count();
        $beforeDetails = SaleDetail::count();

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\HistoricalSalesSeeder',
            '--force' => true,
        ], $this->getOutput());

        $afterSales = Sale::count();
        $afterDetails = SaleDetail::count();

        $this->info("Berhasil!");
        $this->line("Transaksi penjualan (Sale): {$afterSales} (sebelumnya: {$beforeSales})");
        $this->line("Detail timbangan lapak (SaleDetail): {$afterDetails} (sebelumnya: {$beforeDetails})");

        return self::SUCCESS;
    }
}
