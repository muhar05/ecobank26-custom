<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class BankSampahResetCommand extends Command
{
    protected $signature = 'bank-sampah:reset '
        .' {--force : Jalankan tanpa konfirmasi interaktif}';

    protected $description = 'Reset development Bank Sampah: kosongkan data domain, pertahankan users + authorization (roles/permissions) + app_settings. Tanpa seeding, tanpa ubah schema.';

    /**
     * Tabel domain Bank Sampah yang dikosongkan. Urutan: child dulu
     * (FK-safe, constraint juga dinonaktifkan saat truncate).
     *
     * WAJIB dipertahankan (tidak ada di daftar ini):
     * - users,
     * - roles, permissions, model_has_roles, model_has_permissions,
     *   role_has_permissions (otorisasi Spatie; menghapusnya membuat
     *   semua user kehilangan permission dan login berujung 403),
     * - app_settings (dibutuhkan aplikasi),
     * - migrations (pembukuan schema; truncate akan merusak migrate
     *   berikutnya karena Laravel menganggap semua migration belum jalan).
     *
     * @var array<int, string>
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

    public function handle(): int
    {
        $usersBefore = DB::table('users')->count();

        $this->info('====================================================');
        $this->info('         EcoBank026 Development Reset               ');
        $this->info('====================================================');
        $this->line(' - Data domain Bank Sampah akan DIHAPUS.');
        $this->line(" - Users ({$usersBefore} user) DIPERTAHANKAN apa adanya.");
        $this->line(' - Roles/permissions + relasinya dan app_settings DIPERTAHANKAN (otorisasi tetap valid).');
        $this->line(' - TIDAK ada seeding ulang master/baseline.');
        $this->line(' - Schema/migration/business logic TIDAK DIUBAH.');
        $this->line('');

        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to run bank-sampah:reset in production without --force.');
            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Hapus data domain Bank Sampah? Users, roles/permissions, dan app_settings dipertahankan.', false)) {
            $this->warn('Reset dibatalkan.');
            return self::SUCCESS;
        }

        $deletedCounts = [];
        foreach ($this->domainTables as $table) {
            $deletedCounts[$table] = Schema::hasTable($table) ? DB::table($table)->count() : 0;
        }

        // Kumpulkan fingerprint Import Setoran dari notes deposit SEBELUM
        // truncate, agar kunci idempotency-nya bisa dilupakan dari cache.
        $importFingerprints = $this->collectDepositImportFingerprints();

        Schema::disableForeignKeyConstraints();
        try {
            foreach ($this->domainTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $tempDir = storage_path('app/temp_imports');
        if (File::isDirectory($tempDir)) {
            File::cleanDirectory($tempDir);
        }

        // Hapus record idempotency Import Setoran agar file Excel yang sama
        // bisa diimpor ulang. Mekanisme anti-duplikat di aplikasi TIDAK
        // diubah; yang dihapus hanya record-nya.
        $clearedImportKeys = $this->clearDepositImportIdempotency($importFingerprints);

        $usersAfter = DB::table('users')->count();

        $this->line('');
        $this->info('Preserved:');
        $this->line("- users: {$usersAfter}" . ($usersAfter === $usersBefore ? ' (unchanged)' : ' (MISMATCH!)'));
        foreach (['roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions', 'app_settings'] as $table) {
            if (Schema::hasTable($table)) {
                $this->line("- {$table}: " . DB::table($table)->count());
            }
        }

        $this->line('');
        $this->info('Deleted:');
        foreach ($deletedCounts as $table => $count) {
            $this->line("- {$table}: {$count}");
        }
        $this->line("- deposit import idempotency (cache): {$clearedImportKeys} keys");

        $this->line('');
        $this->info('====================================================');
        $this->info('Reset selesai. Users + otorisasi + app_settings dipertahankan.');
        $this->info('====================================================');

        if ($usersAfter !== $usersBefore) {
            $this->error("Users count changed ({$usersBefore} -> {$usersAfter}). Investigate immediately.");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Kumpulkan fingerprint Import Setoran yang tertanam di notes deposit
     * dengan format [FP:{hash}] (ditulis oleh DepositImportService::commit()).
     *
     * @return array<int, string>
     */
    protected function collectDepositImportFingerprints(): array
    {
        if (!Schema::hasTable('deposits')) {
            return [];
        }

        $fingerprints = [];
        $notesList = DB::table('deposits')
            ->where('notes', 'like', '%[FP:%]%')
            ->pluck('notes');

        foreach ($notesList as $notes) {
            // Format commit asli: "[FP: {hash}]" (dengan spasi setelah colon).
            if (preg_match_all('/\[FP:\s*([^\]]+)\]/', (string) $notes, $matches)) {
                foreach ($matches[1] as $fp) {
                    $fp = trim($fp);
                    if ($fp !== '') {
                        $fingerprints[] = $fp;
                    }
                }
            }
        }

        return array_values(array_unique($fingerprints));
    }

    /**
     * Hapus record idempotency Import Setoran:
     * - kunci fingerprint permanen deposit_import_fp_* (Cache::forever saat commit),
     * - sisa staging deposit_import_* dari cache store database.
     *
     * Hanya prefix deposit_import_* yang disentuh; tabel users dan
     * mekanisme anti-duplikat di aplikasi tidak diubah.
     *
     * @param array<int, string> $fingerprints
     */
    protected function clearDepositImportIdempotency(array $fingerprints): int
    {
        $keys = [];
        foreach ($fingerprints as $fp) {
            $keys[] = "deposit_import_fp_{$fp}";
        }

        if (Schema::hasTable('cache')) {
            $tableKeys = [];
            $stored = DB::table('cache')->pluck('key')->all();
            foreach ($stored as $storedKey) {
                if (str_starts_with((string) $storedKey, 'deposit_import_')) {
                    $tableKeys[] = (string) $storedKey;
                }
            }
            if (!empty($tableKeys)) {
                DB::table('cache')->whereIn('key', $tableKeys)->delete();
            }
            $keys = array_merge($keys, $tableKeys);
        }

        $keys = array_values(array_unique($keys));
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        return count($keys);
    }
}
