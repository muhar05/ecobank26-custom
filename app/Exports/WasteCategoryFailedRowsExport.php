<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WasteCategoryFailedRowsExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function title(): string
    {
        return 'Gagal di-Import';
    }

    public function headings(): array
    {
        return [
            'Kode Grup',
            'Nama Grup',
            'Kode Kategori',
            'Nama Kategori Sampah',
            'Satuan',
            'Alasan Gagal (Error Reason)'
        ];
    }

    public function array(): array
    {
        return $this->rows;
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
                    'startColor' => ['rgb' => 'DC2626'] // Crimson Red
                ]
            ]
        ];
    }
}
