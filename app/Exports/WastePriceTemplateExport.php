<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WastePriceTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new WastePriceDataImportSheet(),
            new WastePriceExampleSheet(),
            new WastePriceInstructionsSheet(),
        ];
    }
}

class WastePriceDataImportSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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
            '* Satuan',
            '* Harga Beli dari Nasabah',
            '* Harga Jual ke Agregator',
            'Tanggal Berlaku'
        ];
    }

    public function array(): array
    {
        return []; // Keep empty for user input
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

class WastePriceExampleSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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
            '* Satuan',
            '* Harga Beli dari Nasabah',
            '* Harga Jual ke Agregator',
            'Tanggal Berlaku'
        ];
    }

    public function array(): array
    {
        return [
            ['PLS', 'Plastik', 'PLS.01', 'Botol Plastik Bersih', 'kg', '3000', '3500', '2026-05-28'],
            ['KRT', 'Kertas', 'KRT.01', 'Kardus Tebal', 'kg', '1500', '1800', '2026-05-28'],
            ['LOG', 'Logam', 'LOG.01', 'Besi Tua', 'kg', '4000', '4500', '2026-05-28'],
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

class WastePriceInstructionsSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'PETUNJUK';
    }

    public function array(): array
    {
        return [
            ['PETUNJUK PENGISIAN TEMPLATE IMPORT HARGA SAMPAH'],
            [''],
            ['⚠️ PERINGATAN PENTING:'],
            ['1. JANGAN UBAH HEADER KOLOM atau susunan kolom pada sheet DATA IMPORT.'],
            ['2. JANGAN MENGHAPUS baris pertama (header) yang berwarna hijau.'],
            ['3. Simpan file tetap dalam format Excel (.xlsx) sebelum diunggah.'],
            ['4. Kolom dengan tanda bintang (*) wajib diisi.'],
            ['5. Sistem tidak akan membuat kategori baru secara otomatis. Kategori wajib terdaftar di master data terlebih dahulu.'],
            [''],
            ['PANDUAN INPUT KOLOM:'],
            ['- Kode Grup: Opsional. Kode grup kategori (contoh: PLS, KRT, LOG).'],
            ['- Nama Grup: Opsional. Nama grup kategori (contoh: Plastik, Kertas, Logam).'],
            ['- Kode Kategori: Opsional tetapi sangat direkomendasikan. Kode kategori sampah (contoh: PLS.01). Jika diisi, pencarian akan menggunakan kode ini.'],
            ['- Nama Kategori Sampah: Wajib diisi. Nama kategori sampah yang ingin diupdate harganya.'],
            ['- Satuan: Wajib diisi. Contoh: kg, pcs, liter.'],
            ['- Harga Beli dari Nasabah: Wajib diisi. Harga beli (angka >= 0, tanpa titik/koma sebagai pemisah ribuan).'],
            ['- Harga Jual ke Agregator: Wajib diisi. Harga jual (angka >= 0, harus lebih besar atau sama dengan Harga Beli).'],
            ['- Tanggal Berlaku: Opsional. Format: YYYY-MM-DD (Contoh: 2026-05-28). Jika kosong, otomatis terisi tanggal hari ini.'],
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
