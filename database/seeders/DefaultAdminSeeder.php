<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DefaultAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Define users list with raw phone numbers
        $demoUsers = [
            [
                'name' => 'Admin Bank Sampah',
                'raw_phone' => '620844444444',
                'roles' => ['admin_bank_sampah'],
            ],
            [
                'name' => 'Admin Bank Sampah 2',
                'raw_phone' => '620855555555',
                'roles' => ['admin_bank_sampah'],
            ],
            [
                'name' => 'Admin Bank Sampah 3',
                'raw_phone' => '620866666666',
                'roles' => ['admin_bank_sampah'],
            ],
        ];

        // Create users
        $createdUsers = [];
        foreach ($demoUsers as $du) {
            $phone = $this->normalizePhone($du['raw_phone']);

            // Use updateOrCreate on phone to be safe and unique
            $user = User::updateOrCreate(
                ['phone' => $phone],
                [
                    'name' => $du['name'],
                    'email' => null, // Avoid email dependency
                    'password' => Hash::make('password'),
                ]
            );

            // Assign roles
            $user->syncRoles($du['roles']);
            $createdUsers[$du['raw_phone']] = $user;
        }
    }

    /**
     * Normalize raw phone number consistently.
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '620')) {
            $phone = '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '62')) {
            $phone = '0' . substr($phone, 2);
        }
        return $phone;
    }
}
