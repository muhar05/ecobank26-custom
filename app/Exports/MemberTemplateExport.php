<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MemberTemplateExport implements WithMultipleSheets
{
    protected bool $includeExamples;

    public function __construct(bool $includeExamples = false)
    {
        $this->includeExamples = $includeExamples;
    }

    public function sheets(): array
    {
        return [
            new MemberDataImportSheet($this->includeExamples),
            new MemberInstructionsSheet(),
        ];
    }
}

class MemberDataImportSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected bool $includeExamples;

    public function __construct(bool $includeExamples)
    {
        $this->includeExamples = $includeExamples;
    }

    public function title(): string
    {
        return 'Data Import';
    }

    public function headings(): array
    {
        return [
            '* RT Number (rt_number)',
            'KK Number (kk_number) [16 Digits]',
            'Family Head Fallback (family_head)',
            '* Name (name)',
            '* Relationship (relationship: Kepala Keluarga/Istri/Anak/Lainnya)',
            'Gender (gender: Laki-laki/Perempuan/L/P)',
            'Birth Date (birth_date: YYYY-MM-DD)',
            'Phone (phone)',
            'Address (address)'
        ];
    }

    public function array(): array
    {
        if ($this->includeExamples) {
            return [
                ['001', '3201234567890001', '', 'Ahmad Subarjo', 'Kepala Keluarga', 'Laki-laki', '1985-05-15', '08123456789', 'Jl. Clean No. 12'],
                ['001', '3201234567890001', '', 'Siti Aminah', 'Istri', 'Perempuan', '1988-10-22', '08133333333', 'Jl. Clean No. 12'],
                ['002', '', 'Dedi Hermawan', 'Dedi Hermawan', 'Kepala Keluarga', 'L', '1990-01-01', '08124444444', 'Jl. Asri No. 1'],
            ];
        }
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '059669'] // Emerald Green
                ]
            ]
        ];
    }
}

class MemberInstructionsSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'Petunjuk Pengisian';
    }

    public function array(): array
    {
        return [
            ['PETUNJUK PENGISIAN IMPORT ANGGOTA WARGA'],
            [''],
            ['1. Kolom dengan tanda bintang (*) wajib diisi.'],
            ['2. rt_number: Masukkan nomor RT, contoh: "001", "002".'],
            ['3. kk_number: Boleh kosong jika kk belum terdaftar di database, namun jika diisi harus 16 digit angka.'],
            ['4. Jika kk_number tidak diisi, maka wajib mengisi kolom "Family Head Fallback (family_head)" untuk mencocokkan dengan Kepala Keluarga di database.'],
            ['5. name: Nama lengkap anggota keluarga (wajib diisi).'],
            ['6. relationship: Hubungan dalam keluarga (wajib diisi). Contoh: "Kepala Keluarga", "Istri", "Anak", "Lainnya".'],
            ['7. gender: Jenis kelamin (boleh dikosongkan). Diisi dengan salah satu nilai berikut: Laki-laki, Perempuan, L, P.'],
            ['8. birth_date: Tanggal lahir (boleh dikosongkan). Format wajib: YYYY-MM-DD (Contoh: 1990-12-31).'],
            ['9. phone & address: Nomor HP dan Alamat (boleh dikosongkan).'],
            ['10. Maksimal data yang diizinkan dalam sekali unggah adalah 1000 baris.'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => '059669']
                ]
            ]
        ];
    }
}
