<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KkTemplateExport implements WithMultipleSheets
{
    protected bool $includeExamples;

    public function __construct(bool $includeExamples = false)
    {
        $this->includeExamples = $includeExamples;
    }

    public function sheets(): array
    {
        return [
            new KkDataImportSheet(),
            new KkExampleSheet(),
            new KkInstructionsSheet(),
        ];
    }
}

class KkDataImportSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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
            '* Nama Kepala Keluarga',
            'Alamat Rumah',
            'Nomor HP Aktif',
            '* Status Rumah (Aktif/Kontrak/Pindah/Kosong)',
            'Jumlah Anggota Keluarga'
        ];
    }

    public function array(): array
    {
        return []; // Keep empty for user inputs
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

class KkExampleSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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
            '* Nama Kepala Keluarga',
            'Alamat Rumah',
            'Nomor HP Aktif',
            '* Status Rumah (Aktif/Kontrak/Pindah/Kosong)',
            'Jumlah Anggota Keluarga'
        ];
    }

    public function array(): array
    {
        return [
            ['001', '3201234567890001', 'Ahmad Subarjo', 'Jl. Clean No. 12', '08123456789', 'Aktif', '4'],
            ['002', '3201234567890002', 'Siti Aminah', 'Jl. Hijau No. 8', '08133333333', 'Kontrak', '3'],
            ['001', '', 'Dedi Hermawan', 'Jl. Asri No. 1', '08124444444', 'Aktif', '2'],
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

class KkInstructionsSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'PETUNJUK';
    }

    public function array(): array
    {
        return [
            ['PETUNJUK PENGISIAN IMPORT KARTU KELUARGA (KK)'],
            [''],
            ['⚠️ PERINGATAN PENTING:'],
            ['1. JANGAN UBAH NAMA HEADER KOLOM atau susunan kolom pada sheet DATA IMPORT.'],
            ['2. JANGAN MENGHAPUS baris pertama (header) yang berwarna hijau.'],
            ['3. Simpan file tetap dalam format Excel (.xlsx) atau CSV (.csv) sebelum diunggah.'],
            [''],
            ['PANDUAN INPUT KOLOM:'],
            ['* Nomor RT: Masukkan nomor RT, contoh: "001", "002" (Wajib diisi).'],
            ['* Nomor KK (16 Digit): Masukkan 16 digit angka nomor KK. Atur format cell sebagai TEXT agar angka tidak berubah.'],
            ['* Nama Kepala Keluarga: Nama lengkap Kepala Keluarga (Wajib diisi).'],
            ['* Alamat Rumah & Nomor HP Aktif: Opsional (boleh dikosongkan).'],
            ['* Status Rumah: Status tempat tinggal (Wajib diisi). Pilih salah satu dari:'],
            ['  - Aktif   (Tempat tinggal tetap)'],
            ['  - Kontrak (Sewa/Mengontrak)'],
            ['  - Pindah  (Sudah pindah domisili)'],
            ['  - Kosong  (Rumah kosong)'],
            ['* Jumlah Anggota Keluarga: Opsional (angka >= 0).'],
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
