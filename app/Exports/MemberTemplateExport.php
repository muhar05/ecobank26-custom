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
            new MemberDataImportSheet(),
            new MemberExampleSheet(),
            new MemberInstructionsSheet(),
        ];
    }
}

class MemberDataImportSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'DATA IMPORT';
    }

    public function headings(): array
    {
        return [
            '* Nomor RT',
            'Nomor KK (16 Digit)',
            'Nama Kepala Keluarga Fallback',
            '* Nama Lengkap Anggota',
            '* Hubungan Dalam Keluarga',
            'Jenis Kelamin (Laki-laki/Perempuan)',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Nomor HP',
            'Alamat Domisili'
        ];
    }

    public function array(): array
    {
        return []; // Keep empty for input
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');
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

class MemberExampleSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'CONTOH PENGISIAN';
    }

    public function headings(): array
    {
        return [
            '* Nomor RT',
            'Nomor KK (16 Digit)',
            'Nama Kepala Keluarga Fallback',
            '* Nama Lengkap Anggota',
            '* Hubungan Dalam Keluarga',
            'Jenis Kelamin (Laki-laki/Perempuan)',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Nomor HP',
            'Alamat Domisili'
        ];
    }

    public function array(): array
    {
        return [
            ['001', '3201234567890001', '', 'Ahmad Subarjo', 'Kepala Keluarga', 'Laki-laki', '1985-05-15', '08123456789', 'Jl. Clean No. 12'],
            ['001', '3201234567890001', '', 'Siti Aminah', 'Istri', 'Perempuan', '1988-10-22', '08133333333', 'Jl. Clean No. 12'],
            ['002', '', 'Dedi Hermawan', 'Dedi Hermawan', 'Kepala Keluarga', 'Laki-laki', '1990-01-01', '08124444444', 'Jl. Asri No. 1'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '1E293B'] // Dark slate grey for examples
                ]
            ]
        ];
    }
}

class MemberInstructionsSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'PETUNJUK';
    }

    public function array(): array
    {
        return [
            ['PETUNJUK PENGISIAN IMPORT ANGGOTA WARGA'],
            [''],
            ['⚠️ PERINGATAN PENTING:'],
            ['1. JANGAN UBAH NAMA HEADER KOLOM atau susunan kolom pada sheet DATA IMPORT.'],
            ['2. JANGAN MENGHAPUS baris pertama (header) yang berwarna hijau.'],
            ['3. Simpan file tetap dalam format Excel (.xlsx) atau CSV (.csv) sebelum diunggah.'],
            ['4. KHUSUS KELUARGA: Dalam satu KK hanya diperbolehkan memiliki satu "Kepala Keluarga".'],
            [''],
            ['PANDUAN INPUT KOLOM:'],
            ['* Nomor RT: Masukkan nomor RT, contoh: "001", "002" (Wajib diisi).'],
            ['* Nomor KK (16 Digit): Masukkan 16 digit angka nomor KK. Atur format cell sebagai TEXT agar angka tidak berubah.'],
            ['* Nama Kepala Keluarga Fallback: Nama lengkap Kepala Keluarga (Wajib diisi jika Nomor KK dikosongkan).'],
            ['* Nama Lengkap Anggota: Nama lengkap warga yang didaftarkan (Wajib diisi).'],
            ['* Hubungan Dalam Keluarga: Hubungan dalam keluarga (Wajib diisi). Contoh: "Kepala Keluarga", "Istri", "Anak", "Lainnya".'],
            ['* Jenis Kelamin (Laki-laki/Perempuan): Opsional (boleh diisi Laki-laki / Perempuan / L / P).'],
            ['* Tanggal Lahir (YYYY-MM-DD): Opsional (Format wajib: YYYY-MM-DD, Contoh: 1995-12-31).'],
            ['* Nomor HP & Alamat Domisili: Opsional (boleh dikosongkan).'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'DC2626'] // Warning Red title
                ]
            ]
        ];
    }
}
