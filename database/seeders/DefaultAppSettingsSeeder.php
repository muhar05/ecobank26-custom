<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class DefaultAppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Informasi RW
        AppSetting::setValue('app.rw_name', 'RW 026', 'string', 'rw_info');
        AppSetting::setValue('app.rw_contact_phone', '08123456789', 'string', 'rw_info');

        // 2. Bank Sampah
        AppSetting::setValue('app.bank_sampah_name', 'EcoBank026', 'string', 'bank_sampah');
        AppSetting::setValue('app.bank_sampah_contact_phone', '08123456789', 'string', 'bank_sampah');

        // 3. Pengaturan Iuran
        AppSetting::setValue('billing.default_due_days', '10', 'integer', 'billing');

        // 4. Format Dokumen
        AppSetting::setValue('billing.bill_prefix', 'BILL', 'string', 'document');
        AppSetting::setValue('billing.receipt_prefix', 'RCPT', 'string', 'document');

        // UI Detail
        AppSetting::setValue('app.last_updated_by', 'Sistem', 'string', 'system');
        AppSetting::setValue('app.last_updated_at', date('d/m/Y H:i'), 'string', 'system');
    }
}
