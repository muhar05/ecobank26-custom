<?php

namespace App\Exports;

use App\Models\SavingsLedger;
use App\Models\WasteCustomer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class WasteSavingsJournalReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithCustomStartCell
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Jurnal Tabungan Nasabah';
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

        $customerName = 'Semua Nasabah';
        if ($id = $this->request->input('waste_customer_id')) {
            $customerName = WasteCustomer::find($id)->name ?? 'Semua Nasabah';
        }

        $type = 'Semua Tipe';
        if ($t = $this->request->input('type')) {
            $type = $t === 'credit' ? 'Setoran (Credit)' : 'Penarikan (Debit)';
        }

        return [
            'Periode' => $startDate->toDateString() . ' s/d ' . $endDate->toDateString(),
            'Nasabah' => $customerName,
            'Tipe Transaksi' => $type,
            'Dibuat Pada' => now()->toDateTimeString(),
            'Dibuat Oleh' => auth()->user() ? auth()->user()->name : 'System'
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nasabah',
            'Tipe',
            'Deskripsi',
            'Nominal',
            'Running Balance'
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

        $customerId = $this->request->input('waste_customer_id');
        $type = $this->request->input('type');

        $query = SavingsLedger::with('wasteCustomer')
            ->when($customerId, fn($q) => $q->where('waste_customer_id', $customerId))
            ->when($type, fn($q) => $q->where('type', $type))
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Get chronological list for balance calculations
        $ledgers = $query->orderBy('created_at', 'asc')->orderBy('id', 'asc')->get();

        // Preceding starting balance for running balance
        $bal = 0;
        if ($customerId) {
            $startingBalance = SavingsLedger::where('waste_customer_id', $customerId)
                ->where('created_at', '<', $startDate)
                ->sum(DB::raw("case when type = 'credit' then amount else -amount end"));
            $bal = (float) $startingBalance;
        }

        $rows = [];
        $totalCredit = 0;
        $totalDebit = 0;

        foreach ($ledgers as $l) {
            $date = Carbon::instance($l->created_at)->toDateString();
            
            $nasabah = $l->wasteCustomer?->name ?? '-';

            $tipe = $l->type === 'credit' ? 'Setoran (Credit)' : 'Penarikan (Debit)';
            $desc = $l->description ?? '-';
            $amount = (float) $l->amount;

            if ($l->type === 'credit') {
                $bal += $amount;
                $totalCredit += $amount;
            } else {
                $bal -= $amount;
                $totalDebit += $amount;
            }

            $rows[] = [
                $date,
                $nasabah,
                $tipe,
                $desc,
                $amount,
                $customerId ? $bal : '-'
            ];
        }

        // Return latest first for Excel reporting (visual standard)
        $rows = array_reverse($rows);

        // Add summary row
        $rows[] = [
            'TOTAL SETORAN / PENARIKAN',
            '',
            '',
            'Setor: Rp ' . number_format($totalCredit, 0, ',', '.') . ' | Tarik: Rp ' . number_format($totalDebit, 0, ',', '.'),
            $totalCredit - $totalDebit,
            $customerId ? $bal : '-'
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setCellValue('A1', 'JURNAL TABUNGAN NASABAH BANK SAMPAH');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $filters = $this->getFilters();
        $row = 2;
        foreach ($filters as $label => $value) {
            $sheet->setCellValue('A' . $row, $label . ':');
            $sheet->setCellValue('B' . $row, $value);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
        }

        $headingsRow = 8;
        
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
        
        $sheet->getStyle('E9:F' . $lastRow)->getNumberFormat()->setFormatCode('"Rp" #,##0');

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
        return 'A8';
    }
}
