<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KkFailedRowsExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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
            '* Family Head (family_head)',
            'Address (address)',
            'Phone (phone)',
            '* Status (status: active/kontrak/pindah/kosong)',
            'Jumlah Anggota (jumlah_anggota)',
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
