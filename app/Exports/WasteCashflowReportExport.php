<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\WasteBankExpense;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class WasteCashflowReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithCustomStartCell
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Laporan Arus Kas';
    }

    private function getFilters()
    {
        $startDateRaw = $this->request->input('start_date');
        $endDateRaw = $this->request->input('end_date');
        
        if (empty($startDateRaw) || empty($endDateRaw)) {
            $startDate = now()->subDays(30);
            $endDate = now();
        } else {
            $startDate = Carbon::parse($startDateRaw);
            $endDate = Carbon::parse($endDateRaw);
        }

        return [
            'Periode' => $startDate->toDateString() . ' s/d ' . $endDate->toDateString(),
            'Dibuat Pada' => now()->toDateTimeString(),
            'Dibuat Oleh' => auth()->user() ? auth()->user()->name : 'System'
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Transaksi',
            'Tipe',
            'Deskripsi',
            'Pemasukan (Sales)',
            'Pengeluaran (Operational)'
        ];
    }

    public function array(): array
    {
        $startDateRaw = $this->request->input('start_date');
        $endDateRaw = $this->request->input('end_date');
        
        if (empty($startDateRaw) || empty($endDateRaw)) {
            $startDate = now()->subDays(30)->startOfDay();
            $endDate = now()->endOfDay();
        } else {
            $startDate = Carbon::parse($startDateRaw)->startOfDay();
            $endDate = Carbon::parse($endDateRaw)->endOfDay();
        }

        $salesList = Sale::with('collector')->whereBetween('date', [$startDate, $endDate])->get()->map(function($item) {
            return [
                'date' => Carbon::instance($item->date)->toDateString(),
                'code' => 'SAL-' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
                'type' => 'Pemasukan',
                'description' => 'Penjualan ke agregator ' . ($item->collector->name ?? '-'),
                'in' => (float) $item->total_amount,
                'out' => 0.0
            ];
        });

        $expensesList = WasteBankExpense::whereBetween('expense_date', [$startDate, $endDate])->get()->map(function($item) {
            return [
                'date' => Carbon::instance($item->expense_date)->toDateString(),
                'code' => $item->expense_code ?? 'EXP-' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
                'type' => 'Pengeluaran',
                'description' => $item->description,
                'in' => 0.0,
                'out' => (float) $item->amount
            ];
        });

        $cashbook = $salesList->concat($expensesList)->sortBy('date')->values();

        $rows = [];
        $totalIn = 0;
        $totalOut = 0;

        foreach ($cashbook as $item) {
            $totalIn += $item['in'];
            $totalOut += $item['out'];
            $rows[] = [
                $item['date'],
                $item['code'],
                $item['type'],
                $item['description'],
                $item['in'] > 0 ? $item['in'] : '',
                $item['out'] > 0 ? $item['out'] : ''
            ];
        }

        // Summary row
        $rows[] = [
            'TOTAL',
            '',
            '',
            'Net Flow: Rp ' . number_format($totalIn - $totalOut, 0, ',', '.'),
            $totalIn,
            $totalOut
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setCellValue('A1', 'LAPORAN ARUS KAS BANK SAMPAH (OPERASIONAL)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $filters = $this->getFilters();
        $row = 2;
        foreach ($filters as $label => $value) {
            $sheet->setCellValue('A' . $row, $label . ':');
            $sheet->setCellValue('B' . $row, $value);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
        }

        $headingsRow = 6;
        
        $sheet->getStyle('A' . $headingsRow . ':F' . $headingsRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '059669'] // Emerald Green
            ]
        ]);

        $lastRow = $sheet->getHighestRow();
        
        $sheet->getStyle('E7:F' . $lastRow)->getNumberFormat()->setFormatCode('"Rp" #,##0');

        $sheet->getStyle('A' . $lastRow . ':F' . $lastRow)->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'F1F5F9']
            ]
        ]);

        return [];
    }

    public function startCell(): string
    {
        return 'A6';
    }
}
