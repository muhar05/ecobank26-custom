<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CollectorPriceTemplateExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithColumnFormatting
{
    public function title(): string
    {
        return 'Data Harga';
    }

    public function headings(): array
    {
        return [
            'Jenis Barang',
            'Harga Lapak',
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 32,
            'B' => 18,
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
        $sheet->getStyle('B2:B500')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }
}
