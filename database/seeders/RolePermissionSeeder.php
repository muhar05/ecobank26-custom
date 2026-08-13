<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Bank Sampah
            'view_waste_bank',
            'manage_deposits',
            'manage_withdrawals',
            'manage_sales',
            'manage_waste_prices',
            'view_waste_reports',
            'manage_waste_customers',
            // Warga
            'view_own_savings',
            'view_own_dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminBankSampah = Role::firstOrCreate(['name' => 'admin_bank_sampah']);
        $adminBankSampah->syncPermissions([
            'view_waste_bank',
            'manage_deposits',
            'manage_withdrawals',
            'manage_sales',
            'manage_waste_prices',
            'view_waste_reports',
            'manage_waste_customers',
        ]);

        $adminRw = Role::firstOrCreate(['name' => 'admin_rw']);
        $adminRw->syncPermissions([
            'view_waste_bank',
            'view_waste_reports',
        ]);

        // Role legacy dari modul RT/RW; dipertahankan agar authorization test tetap valid.
        // Tidak diberikan permission Bank Sampah (tidak bisa mengakses laporan dsb).
        Role::firstOrCreate(['name' => 'admin_rt']);

        $warga = Role::firstOrCreate(['name' => 'warga']);
        $warga->syncPermissions([
            'view_own_savings',
            'view_own_dashboard',
        ]);
    }
}
