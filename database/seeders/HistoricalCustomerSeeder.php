<?php

namespace Database\Seeders;

use App\Models\WasteCustomer;
use Illuminate\Database\Seeder;

/**
 * Customer master historis dari FINAL CLEANUP 122 nama
 * (Tabungan Nasabah vs Form Data Nasabah Master).
 *
 * Keputusan final yang diterapkan:
 * 1. Bu Wulandari dan Bu Wulan = BERBEDA. Bu Wulandari dibuat sendiri,
 *    tanpa alias. (Bu Wulan tidak ada di 122 baris, tidak dibuat.)
 * 2. Dipo = Dipo RT 6 (register Tabungan + seluruh transaksi RT 6; tidak ada
 *    bukti RT 7). Dibuat sebagai customer baru. Dipo RT 7 tidak dibuat.
 * 3. NON_NASABAH (Kas, Karang Taruna, Sahabat Qur'an) tidak dibuat.
 *    Tambahan File B: `Kas Bank Sampah` dan `Bank Sampah Dasana UB`
 *    (organisasi) => excluded, tidak dibuat.
 * 4. Nama sama beda RT tidak digabung (dibedakan via address).
 * 5. RT/address sebagai identifikasi, bukan unique ID.
 * 6. Audit File B menambah 17 customer baru (lihat customers()) dan 5 alias
 *    nama (lihat aliases()). `Bu Tuti RT 1` (typo) = "Bu Tuti RW 17",
 *    dibuat + alias, jadi tidak ada lagi ambiguous.
 *
 * Total: 135 customer (118 cleanup + 17 File B).
 * TIDAK menyentuh: users, transaksi, business logic, schema.
 * TANPA data sensitif (tanpa HP/rekening/alamat lengkap).
 *
 * Idempotent: kunci natural (name, address) exact-match. Dijalankan ulang
 * tidak membuat duplikat. customer_code NSB-xxxxxx via generator aplikasi.
 */
class HistoricalCustomerSeeder extends Seeder
{
    /**
     * @return array<int, array{name: string, address: string|null}>
     */
    public static function customers(): array
    {
        $rt = static function (int|string $rt, array $names): array {
            $addr = is_numeric($rt) ? 'RT '.$rt : (string) $rt;

            return array_map(fn ($n) => ['name' => $n, 'address' => $addr], $names);
        };

        return array_merge(
            $rt(1, ['Bu Admini', 'Bu Betria', 'Bu Mala', 'Bu Maryani', 'Bu Nita',
                'Bu Nurjanah', 'Bu Sunarti', 'Bu Tetty Hamdi', 'Bu Wulandari',
                'Bu Yenvitriati', 'Pak Danny', 'Mama Wahyu', 'Okta',
                'Bu Suhermiati', 'Bu Susi', 'Bu Termin']),
            $rt(2, ['Akhsan', 'Bela', 'Bu Alim', 'Bu Angga', 'Bu Fardani',
                'Bu Hartinah', 'Bu Lia RT 2', 'Bu Martinah', 'Bu Nurhidayah',
                'Bu Ratu Tuti', 'Bu Rustinah', 'Bu Widodo', 'Bu Anwar',
                'Mbak Nurhayati', 'Mbak Sari', 'Mama Demas', 'Pak Suprayitno',
                'Novem', 'Sela', 'Vena', 'Pak Wawan']),
            $rt(3, ['Bu Indrayani', 'Bu Lidia', 'Bu Maria Miski', 'Bu Minah',
                'Bu Purwani', 'Bu Rumiyati', 'Bu Sukayah', 'Bu Yayuk', 'Bu Wiwin',
                'Bu Fatimah']),
            $rt(4, ['Bu Aminah', 'Bu Eva', 'Bu Evi', 'Bu Ani', 'Bu Febri', 'Bu Hana',
                'Bu Ida Amanah', 'Bu Fisca', 'Bu Lia', 'Bu Linda', 'Bu Mimin',
                'Bu Irma', 'Bu Nining', 'Bu Neni', 'Bu Rina', 'Bu Nurlayli',
                'Bu Riris', 'Bu Rindra', 'Bu Sandra', 'Bu Sarmiyah', 'Bu Sarti',
                'Bu Sariyanti', 'Bu Satini', 'Bu Siti Mutmainah', 'Bu Siti Alfiyah',
                'Bu Suci', 'Bu Titin', 'Bu Suriya', 'Bu Wuri', 'Dame', 'Bu Tunut',
                'Mama Adam', 'Pak Sumarman', 'Selly', 'Mba Dian', 'Pak Giri']),
            $rt(5, ['Bu Arsudin', 'Bu Dida', 'Bu Kamiem', 'Bu Ecin Dwi',
                'Bu Nurhayati', 'Bu Sri Retno', 'Bu Uum', 'Bu Yanti', 'Bu Lilis',
                'Rafi', 'Syifa Kamila', 'Pak Soleh', 'Oma Carrisa', 'Mas Angga',
                'Bu Virgiawan', 'Bu Natira Salmawati']),
            $rt(6, ['Nabil', 'Bu Hadiyana', 'Bu Vivi']),
            $rt(7, ['Eshan', 'Bu Hartati', 'Bu Maesaroh', 'Bu Halimah', 'Pak Ade K',
                'Bu Maryati', 'Bu Rina RT 7', 'Bu Ani RT 7']),
            $rt('Kp Bambu', ['Bu Devilie', 'Bu Lena']),
            $rt('TD', ['Bu Mutia']),
            $rt('UE', ['Bu Larasati', 'Bu Sri Rukiyati']),
            $rt('UB', ['Bu Yanti Bela']),
            $rt('RW 21', ['Bu Ani RW 21']),
            $rt('RW 19', ['Mama Zura']),
            // --- File B: customer NEW hasil audit (Bank Sampah NEW.xlsx) ---
            // Konteks lokasi disimpan di address. Nama dipertahankan agar
            // normalize() importer cocok dengan label transaksi.
            // Dibungkus satu list agar array_merge menambah (bukan merge key).
            [
                ['name' => 'Joel RT 6', 'address' => 'RT 6'],
                ['name' => 'Bu Sinta UE', 'address' => 'UE'],
                ['name' => 'Bu Tria Blok Uc', 'address' => 'UC'],
                ['name' => 'Gloria RT 5', 'address' => 'RT 5'],
                ['name' => 'Aruna RT 5', 'address' => 'RT 5'],
                ['name' => 'Vina RT 3', 'address' => 'RT 3'],
                ['name' => 'Bu Linda RW 19', 'address' => 'RW 19'],
                ['name' => 'Bu Ratih', 'address' => 'RT 5'],
                ['name' => 'Pak Rudi', 'address' => 'Warmindo'],
                ['name' => 'Pak Jamil', 'address' => 'Warmindo'],
                ['name' => 'Warbo', 'address' => 'Warmindo'],
                ['name' => 'Popi', 'address' => 'Warmindo'],
                ['name' => 'Warbi', 'address' => 'Warmindo'],
                ['name' => 'Danang', 'address' => 'Warmindo'],
                ['name' => 'Bu Yanti Warmindo', 'address' => 'Warmindo'],
                // Dipo: register Tabungan RT 6 + seluruh transaksi RT 6 (bukan RT 7).
                ['name' => 'Dipo', 'address' => 'RT 6'],
                // Final audit: label transaksi "BU TUTI RT 1" (typo) = "Bu Tuti RW 17"
                // (Tabungan r141; pivot 1-8-26 "BU TUTI RT 17" cocok 26,15 kg / Rp30.728).
                ['name' => 'Bu Tuti RW 17', 'address' => 'RW 17'],
            ],
        );
    }

    /**
     * Alias nama nasabah: label varian di workbook => nama kanonik
     * WasteCustomer. Mencegah customer duplikat saat historical import.
     * Kunci memakai normalisasi yang sama dengan importer
     * (lowercase, spasi dirapatkan): HistoricalDepositDryRunService::normalize.
     *
     * @return array<string, string>
     */
    public static function aliases(): array
    {
        return [
            'danang warmindo' => 'Danang',
            'pak jamil warmindo' => 'Pak Jamil',
            'warbi warmindo' => 'Warbi',
            'bu ratih rt 5' => 'Bu Ratih',
            // Final audit: "RT 1" typo dari RT 17.
            'bu tuti rt 1' => 'Bu Tuti RW 17',
        ];
    }

    /**
     * @return array<int, string> label yang disengaja TIDAK dibuat
     */
    public static function excluded(): array
    {
        return [
            'Kas', 'Karang Taruna', "Sahabat Qur'an",
            // File B: NON_NASABAH / organisasi (audit customer).
            'Kas Bank Sampah', 'Bank Sampah Dasana UB',
        ];
    }

    /**
     * @return array<int, string> label ambiguous yang DITUNDA (NEEDS_REVIEW)
     */
    public static function ambiguous(): array
    {
        // "Bu Tuti RT 1" sudah terselesaikan (final audit) => tidak ada lagi.
        return [];
    }

    public function run(): void
    {
        foreach (self::customers() as $item) {
            $exists = WasteCustomer::where('name', $item['name'])
                ->where('address', $item['address'])
                ->first();
            if ($exists) {
                continue;
            }
            WasteCustomer::create([
                'customer_code' => WasteCustomer::generateNextCustomerCode(),
                'name' => $item['name'],
                'address' => $item['address'],
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }
    }
}
