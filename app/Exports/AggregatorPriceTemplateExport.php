<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AggregatorPriceTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new AggregatorPriceDataSheet(),
            new AggregatorPriceInstructionsSheet(),
        ];
    }
}

class AggregatorPriceDataSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    public function title(): string
    {
        return 'Harga';
    }

    public function headings(): array
    {
        return [
            'Material',
            'Harga',
        ];
    }

    public function array(): array
    {
        return [
            ['Kardus Bersih', 2200],
            ['Botol Putih Bersih', 4000],
            ['Kertas Campur', 1700],
            ['Gelas Plastik Bersih', 2500],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

        // Style header row
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '047857'], // Emerald-700
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getStyle('B2:B10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }
}

class AggregatorPriceInstructionsSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'Petunjuk';
    }

    public function headings(): array
    {
        return [
            'No',
            'Panduan Pengisian Sheet "Harga" Agregator',
        ];
    }

    public function array(): array
    {
        return [
            [1, 'Pilih mitra Agregator/Pengepul tujuan terlebih dahulu pada halaman aplikasi sebelum mengunggah.'],
            [2, 'Jangan mengubah atau menghapus baris header (Nama Kolom) pada sheet "Harga".'],
            [3, 'Satu baris mewakili harga penawaran beli satu jenis material dari agregator ke Bank Sampah.'],
            [4, 'Kolom "Material": Dapat menggunakan nama resmi kategori atau istilah dagang agregator.'],
            [5, 'Kolom "Harga": Isi dengan angka harga per satuan (Rp / kg) tanpa titik pemisah atau simbol mata uang.'],
            [6, 'Material yang belum dikenali sistem dapat dipetakan langsung pada aplikasi melalui fitur alias agregator.'],
            [7, 'Harga yang tidak mengalami perubahan dari harga saat ini di sistem tidak akan mencatat riwayat duplikat.'],
            [8, 'Perubahan harga yang terjadi akan tercatat di riwayat perubahan harga beserta audit perangkat.'],
            [9, 'Hapus baris contoh sebelum mengisi daftar harga yang sebenarnya.'],
            [10, 'Pastikan berkas disimpan dalam format Excel (.xlsx).'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // Slate-800
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getStyle('A2:A12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
