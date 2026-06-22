<?php

namespace Database\Seeders;

use App\Models\Kk;
use App\Models\Member;
use App\Models\Rt;
use Illuminate\Database\Seeder;

class DummyWargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada RT terlebih dahulu untuk referensi
        $rt = Rt::firstOrCreate(['rt_number' => '01']);
        
        // Buat 2 KK dummy
        $kk1 = Kk::firstOrCreate(['kk_number' => '3201234567890001'], [
            'rt_id' => $rt->id,
            'family_head' => 'Budi Santoso',
            'status' => 'active',
            'address' => 'Jl. Merdeka No. 1, RT 01/RW 01'
        ]);

        $kk2 = Kk::firstOrCreate(['kk_number' => '3201234567890002'], [
            'rt_id' => $rt->id,
            'family_head' => 'Siti Aminah',
            'status' => 'active',
            'address' => 'Jl. Merdeka No. 2, RT 01/RW 01'
        ]);

        // Daftar 5 Dummy Warga
        $dummies = [
            [
                'kk_id' => $kk1->id,
                'name' => 'Budi Santoso',
                'relationship' => 'Kepala Keluarga',
                'gender' => 'L',
                'phone' => '081234567890',
                'birth_date' => '1980-05-15',
                'address' => 'Jl. Merdeka No. 1, RT 01/RW 01',
            ],
            [
                'kk_id' => $kk1->id,
                'name' => 'Ani Lestari',
                'relationship' => 'Istri',
                'gender' => 'P',
                'phone' => '081234567891',
                'birth_date' => '1982-10-20',
                'address' => 'Jl. Merdeka No. 1, RT 01/RW 01',
            ],
            [
                'kk_id' => $kk1->id,
                'name' => 'Cahyo Santoso',
                'relationship' => 'Anak',
                'gender' => 'L',
                'phone' => '081234567892',
                'birth_date' => '2005-03-10',
                'address' => 'Jl. Merdeka No. 1, RT 01/RW 01',
            ],
            [
                'kk_id' => $kk2->id,
                'name' => 'Siti Aminah',
                'relationship' => 'Kepala Keluarga',
                'gender' => 'P',
                'phone' => '081234567893',
                'birth_date' => '1975-12-05',
                'address' => 'Jl. Merdeka No. 2, RT 01/RW 01',
            ],
            [
                'kk_id' => $kk2->id,
                'name' => 'Dewi Rahmawati',
                'relationship' => 'Anak',
                'gender' => 'P',
                'phone' => '081234567894',
                'birth_date' => '2000-08-25',
                'address' => 'Jl. Merdeka No. 2, RT 01/RW 01',
            ],
        ];

        foreach ($dummies as $data) {
            $data['member_code'] = Member::generateNextCode();
            Member::create($data);
        }
    }
}
