<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DummyUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ecobank026.test'],
            [
                'name' => 'Admin RT',
                'password' => bcrypt('password'),
            ]
        )->syncRoles(['admin_rt']);

        User::updateOrCreate(
            ['email' => 'bendahara@test.com'],
            [
                'name' => 'Bendahara',
                'password' => bcrypt('password'),
            ]
        )->syncRoles(['bendahara']);

        User::updateOrCreate(
            ['email' => 'banksampah@test.com'],
            [
                'name' => 'Admin Bank Sampah',
                'password' => bcrypt('password'),
            ]
        )->syncRoles(['admin_bank_sampah']);

        User::updateOrCreate(
            ['email' => 'warga@test.com'],
            [
                'name' => 'Warga Test',
                'password' => bcrypt('password'),
            ]
        )->syncRoles(['warga']);
    }
}