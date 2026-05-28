<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MemberFailedRowsExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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
            '* RT Number (rt_number)',
            'KK Number (kk_number)',
            'Family Head Fallback (family_head)',
            '* Name (name)',
            '* Relationship (relationship)',
            'Gender (gender)',
            'Birth Date (birth_date)',
            'Phone (phone)',
            'Address (address)',
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
                    'startColor' => ['rgb' => 'DC2626'] // High-visibility Crimson Red for errors
                ]
            ]
        ];
    }
}
