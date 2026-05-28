<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WasteCategoryTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new WasteCategoryDataImportSheet(),
            new WasteCategoryExampleSheet(),
            new WasteCategoryInstructionsSheet(),
        ];
    }
}

class WasteCategoryDataImportSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'DATA IMPORT';
    }

    public function headings(): array
    {
        return [
            'Kode Grup',
            'Nama Grup',
            'Kode Kategori',
            '* Nama Kategori Sampah',
            '* Satuan'
        ];
    }

    public function array(): array
    {
        return []; // Empty for data entry
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

class WasteCategoryExampleSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'CONTOH PENGISIAN';
    }

    public function headings(): array
    {
        return [
            'Kode Grup',
            'Nama Grup',
            'Kode Kategori',
            '* Nama Kategori Sampah',
            '* Satuan'
        ];
    }

    public function array(): array
    {
        return [
            ['PLS', 'Plastik', 'PLS.01', 'Botol Plastik Bersih', 'kg'],
            ['KRT', 'Kertas', 'KRT.01', 'Arsip', 'kg'],
            ['KRT', 'Kertas', 'KRT.02', 'HVS/Putihan', 'kg'],
            ['LOG', 'Logam', 'LOG.01', 'Aluminium', 'kg'],
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
                    'startColor' => ['rgb' => '1E293B'] // Dark slate grey
                ]
            ]
        ];
    }
}

class WasteCategoryInstructionsSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'PETUNJUK';
    }

    public function array(): array
    {
        return [
            ['PETUNJUK PENGISIAN TEMPLATE IMPORT KATEGORI SAMPAH'],
            [''],
            ['⚠️ PERINGATAN PENTING:'],
            ['1. JANGAN UBAH HEADER KOLOM atau susunan kolom pada sheet DATA IMPORT.'],
            ['2. JANGAN MENGHAPUS baris pertama (header) yang berwarna hijau.'],
            ['3. Simpan file tetap dalam format Excel (.xlsx) sebelum diunggah.'],
            ['4. Kolom dengan tanda bintang (*) wajib diisi.'],
            ['5. Grup Kategori harus dibuat terlebih dahulu di menu "Kelola Grup". Import ini tidak membuat grup baru secara otomatis.'],
            [''],
            ['PANDUAN INPUT KOLOM:'],
            ['- Kode Grup / Nama Grup: Wajib diisi salah satu. Digunakan untuk mendeteksi grup kategori sampah yang valid. Pencarian akan mencocokkan Kode Grup terlebih dahulu, lalu Nama Grup secara case-insensitive.'],
            ['- Kode Kategori: Opsional. Kode unik untuk kategori sampah (contoh: PLS.01). Jika dikosongkan, sistem akan meng-generate kode secara otomatis berdasarkan grup.'],
            ['- Nama Kategori Sampah: Wajib diisi. Contoh: Botol Plastik Bersih, HVS/Putihan.'],
            ['- Satuan: Wajib diisi. Contoh: kg, pcs, liter.'],
            [''],
            ['PILIHAN PILIHAN IMPORT MODE:'],
            ['- Insert Only: Hanya memasukkan kategori baru. Jika kode kategori sudah ada di sistem, baris akan ditolak.'],
            ['- Insert or Update: Memasukkan kategori baru atau memperbarui data kategori yang sudah ada jika kodenya cocok.'],
            ['- Skip Duplicate: Mengabaikan baris kategori jika kodenya sudah terdaftar di sistem.'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'DC2626'] // Red color for title
                ]
            ]
        ];
    }
}
