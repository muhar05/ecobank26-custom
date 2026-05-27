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
            // User/Admin
            'manage_users',
            'manage_roles',
            // Community Cash
            'view_community_cash',
            'manage_fund_categories',
            'manage_contributions',
            'manage_expenses',
            'view_cash_reports',
            // Bank Sampah
            'view_waste_bank',
            'manage_members',
            'manage_deposits',
            'manage_withdrawals',
            'manage_sales',
            'manage_waste_prices',
            'view_waste_reports',
            'manage_waste_customers',
            // Warga
            'view_own_dashboard',
            'view_own_savings',
            'view_public_cash_report',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRw = Role::firstOrCreate(['name' => 'admin_rw']);
        $adminRw->syncPermissions($permissions); // All permissions for RW admin

        $adminRt = Role::firstOrCreate(['name' => 'admin_rt']);
        $adminRt->syncPermissions([
            'manage_users',
            'manage_roles',
            'view_community_cash',
            'manage_fund_categories',
            'manage_contributions',
            'manage_expenses',
            'view_cash_reports',
            'manage_members',
            'view_waste_bank',
            'view_waste_reports',
            'view_own_dashboard',
            'view_own_savings',
            'view_public_cash_report',
        ]);

        $bendahara = Role::firstOrCreate(['name' => 'bendahara']);
        $bendahara->syncPermissions([
            'view_community_cash',
            'manage_fund_categories',
            'manage_contributions',
            'manage_expenses',
            'view_cash_reports',
            'manage_members',
        ]);

        $bendaharaRw = Role::firstOrCreate(['name' => 'bendahara_rw']);
        $bendaharaRw->syncPermissions([
            'view_community_cash',
            'manage_fund_categories',
            'manage_contributions',
            'manage_expenses',
            'view_cash_reports',
            'manage_members',
        ]);

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

        $warga = Role::firstOrCreate(['name' => 'warga']);
        $warga->syncPermissions([
            'view_own_dashboard',
            'view_own_savings',
            'view_public_cash_report',
        ]);
    }
}
