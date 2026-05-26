<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Member;
use App\Models\Rt;
use App\Models\Kk;
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
                'name' => 'Admin RW',
                'raw_phone' => '620811111111',
                'roles' => ['admin_rw'],
            ],
            [
                'name' => 'Bendahara RW',
                'raw_phone' => '620822222222',
                'roles' => ['bendahara_rw', 'bendahara'],
            ],
            [
                'name' => 'Admin RT',
                'raw_phone' => '620833333333',
                'roles' => ['admin_rt'],
            ],
            [
                'name' => 'Admin Bank Sampah',
                'raw_phone' => '620844444444',
                'roles' => ['admin_bank_sampah'],
            ],
            [
                'name' => 'Warga Demo',
                'raw_phone' => '620855555555',
                'roles' => ['warga'],
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

        // Setup RT, KK, and Warga Demo connection
        $rt = Rt::firstOrCreate(
            ['rt_number' => '001'],
            ['description' => 'RT 001 Kelurahan Eco']
        );

        $kk = Kk::firstOrCreate(
            ['kk_number' => '3201234567890001'],
            [
                'rt_id' => $rt->id,
                'family_head' => 'Warga Demo',
                'address' => 'Jl. Kebersihan No. 26',
                'phone' => '0855555555',
                'status' => 'active',
            ]
        );

        // Connected member for warga
        $wargaUser = $createdUsers['620855555555'];
        Member::updateOrCreate(
            ['user_id' => $wargaUser->id],
            [
                'member_code' => 'WRG026',
                'name' => 'Warga Demo',
                'phone' => '0855555555',
                'address' => 'Jl. Kebersihan No. 26',
                'kk_id' => $kk->id,
                'relationship' => 'Kepala Keluarga',
            ]
        );
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
