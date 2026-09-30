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

class DepositTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new DepositDataSheet(),
            new DepositInstructionsSheet(),
        ];
    }
}

class DepositDataSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    public function title(): string
    {
        return 'Data Setoran';
    }

    public function headings(): array
    {
        return [
            'Nasabah',
            'Material',
            'Berat',
            'Harga',
            'Total',
        ];
    }

    public function array(): array
    {
        return [
            ['Budi Santoso', 'Kardus Bersih', 2.50, 1200, 3000],
            ['Budi Santoso', 'Botol Putih Bersih', 3.00, 2800, 8400],
            ['Bu Sarti', 'Kertas Campur', 5.00, 800, 4000],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_00,
            'D' => '#,##0',
            'E' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

        // Style header row
        $sheet->getStyle('A1:E1')->applyFromArray([
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

        // Alignments
        $sheet->getStyle('C2:E10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }
}

class DepositInstructionsSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'Petunjuk';
    }

    public function headings(): array
    {
        return [
            'No',
            'Panduan Pengisian Sheet "Data Setoran"',
        ];
    }

    public function array(): array
    {
        return [
            [1, 'Jangan mengubah atau menghapus baris header (Nama Kolom) pada sheet "Data Setoran".'],
            [2, 'Satu baris mewakili satu jenis material pada satu transaksi penimbangan nasabah.'],
            [3, 'Kolom "Nasabah": Isi nama nasabah atau kode nasabah (contoh: Bu Sarti atau NSB-000001).'],
            [4, 'Kolom "Material": Gunakan nama jenis sampah (contoh: Kardus Bersih, Botol Putih Bersih, Emberan, Asoy). Material yang belum cocok dapat dipetakan saat proses impor.'],
            [5, 'Kolom "Berat": Isi dengan angka bobot timbangan dalam satuan kilogram (contoh: 2.5 atau 0.75).'],
            [6, 'Kolom "Harga": Isi harga beli resmi per satuan saat penimbangan (contoh: 1200).'],
            [7, 'Kolom "Total": Isi nilai total (Berat × Harga). Sistem akan melakukan verifikasi otomatis.'],
            [8, 'Baris milik nasabah yang sama pada satu tanggal impor otomatis digabung menjadi 1 setoran multi-material.'],
            [9, 'Hapus baris contoh sebelum mengisi data timbangan Anda yang sebenarnya.'],
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
