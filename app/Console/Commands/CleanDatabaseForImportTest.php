<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CleanDatabaseForImportTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean-for-import-test {--force : Jalankan pembersihan tanpa konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan seluruh data domain/dummy Bank Sampah untuk kondisi fresh, dengan tetap mempertahankan data user dan sistem.';

    /**
     * Daftar tabel domain operasional yang akan dibersihkan.
     *
     * @var array
     */
    protected array $domainTables = [
        'sale_details',
        'sales',
        'deposit_details',
        'deposits',
        'waste_bank_cash_ledgers',
        'waste_bank_expenses',
        'savings_ledgers',
        'withdrawals',
        'waste_price_histories',
        'waste_prices',
        'material_aliases',
        'waste_categories',
        'waste_category_groups',
        'collectors',
        'waste_customers',
        'admin_devices',
        'activity_logs',
        'import_histories',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('');
        $this->info('====================================================');
        $this->info('          EcoBank026 Database Cleaner               ');
        $this->info('====================================================');
        $this->line('');

        $preservedUsersCount = DB::table('users')->count();
        $this->comment("Pemberitahuan:");
        $this->line(" - Seluruh data domain/dummy akan DIHAPUS.");
        $this->line(" - Data user/login ({$preservedUsersCount} user) dan role/permission akan DIPERTAHANKAN.");
        $this->line(" - Schema, tabel, dan migration TIDAK AKAN DIUBAH.");
        $this->line('');

        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin membersihkan seluruh data domain?', false)) {
                $this->warn('Pembersihan database dibatalkan.');
                return self::SUCCESS;
            }
        }

        $this->line('');
        $this->info('Memulai pembersihan data domain...');

        // Catat jumlah data sebelum dihapus
        $deletedCounts = [];
        foreach ($this->domainTables as $table) {
            if (Schema::hasTable($table)) {
                $deletedCounts[$table] = DB::table($table)->count();
            } else {
                $deletedCounts[$table] = 0;
            }
        }

        // Hapus dengan FK safe
        Schema::disableForeignKeyConstraints();

        foreach ($this->domainTables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();

        // Bersihkan berkas sementara impor jika ada
        $tempDir = storage_path('app/temp_imports');
        if (File::isDirectory($tempDir)) {
            File::cleanDirectory($tempDir);
        }

        // Hitung sisa record domain
        $remainingDomainRecords = 0;
        foreach ($this->domainTables as $table) {
            if (Schema::hasTable($table)) {
                $remainingDomainRecords += DB::table($table)->count();
            }
        }

        // Tampilkan Laporan Output
        $this->line('');
        $this->info('Preserved:');
        $this->line("- users: {$preservedUsersCount}");
        if (Schema::hasTable('roles')) {
            $rolesCount = DB::table('roles')->count();
            $this->line("- roles: {$rolesCount}");
        }
        if (Schema::hasTable('permissions')) {
            $permCount = DB::table('permissions')->count();
            $this->line("- permissions: {$permCount}");
        }
        if (Schema::hasTable('app_settings')) {
            $settingsCount = DB::table('app_settings')->count();
            $this->line("- app_settings: {$settingsCount}");
        }

        $this->line('');
        $this->info('Deleted:');
        foreach ($deletedCounts as $table => $count) {
            $this->line("- {$table}: {$count}");
        }

        $this->line('');
        $this->info('====================================================');
        $this->info('Database is clean.');
        $this->line("Users preserved: {$preservedUsersCount}");
        $this->line("Domain records remaining: {$remainingDomainRecords}");
        $this->info('====================================================');
        $this->line('');

        return self::SUCCESS;
    }
}
