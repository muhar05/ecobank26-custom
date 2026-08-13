<?php

namespace App\Exports;

use App\Models\DepositDetail;
use App\Models\WasteCustomer;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use App\Models\Collector;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class WasteDepositReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithCustomStartCell
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Laporan Setoran Sampah';
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

        $groupName = 'Semua Grup';
        if ($id = $this->request->input('waste_category_group_id')) {
            $groupName = WasteCategoryGroup::find($id)->name ?? 'Semua Grup';
        }

        $categoryName = 'Semua Kategori';
        if ($id = $this->request->input('waste_category_id')) {
            $categoryName = WasteCategory::find($id)->name ?? 'Semua Kategori';
        }

        $collectorName = 'Semua Agregator';
        if ($id = $this->request->input('collector_id')) {
            $collectorName = Collector::find($id)->name ?? 'Semua Agregator';
        }

        return [
            'Periode' => $startDate->toDateString() . ' s/d ' . $endDate->toDateString(),
            'Nasabah' => $customerName,
            'Grup Kategori' => $groupName,
            'Kategori' => $categoryName,
            'Agregator' => $collectorName,
            'Dibuat Pada' => now()->toDateTimeString(),
            'Dibuat Oleh' => auth()->user() ? auth()->user()->name : 'System'
        ];
    }

    public function headings(): array
    {
        // Headers start at row 8 (after filter info)
        return [
            'Tanggal',
            'Kode Setoran',
            'Nasabah',
            'Kategori Sampah',
            'Grup Kategori',
            'Berat',
            'Harga Beli Nasabah',
            'Subtotal'
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
        $groupId = $this->request->input('waste_category_group_id');
        $categoryId = $this->request->input('waste_category_id');
        $collectorId = $this->request->input('collector_id');

        $query = DepositDetail::with(['deposit.wasteCustomer', 'deposit.collector', 'wasteCategory.wasteCategoryGroup'])
            ->whereHas('deposit', function($q) use ($startDate, $endDate, $customerId, $collectorId) {
                $q->whereBetween('date', [$startDate, $endDate])
                  ->when($customerId, fn($q2) => $q2->where('waste_customer_id', $customerId))
                  ->when($collectorId, fn($q2) => $q2->where('collector_id', $collectorId));
            })
            ->when($categoryId, fn($q) => $q->where('waste_category_id', $categoryId))
            ->when($groupId, function($q) use ($groupId) {
                $q->whereHas('wasteCategory', function($q2) use ($groupId) {
                    $q2->where('waste_category_group_id', $groupId);
                });
            });

        $details = $query->latest('id')->get();

        $rows = [];
        $totalWeight = 0;
        $totalAmount = 0;

        foreach ($details as $d) {
            $date = Carbon::instance($d->deposit->date)->toDateString();
            $code = 'DEP-' . str_pad($d->deposit->id, 5, '0', STR_PAD_LEFT);
            
            $nasabah = $d->deposit->wasteCustomer->name ?? '-';

            $catName = $d->wasteCategory->name ?? '-';
            $groupName = '-';
            if ($d->wasteCategory && $d->wasteCategory->wasteCategoryGroup) {
                $groupName = $d->wasteCategory->wasteCategoryGroup->name;
            } elseif ($d->wasteCategory && $d->wasteCategory->category_group) {
                $groupName = $d->wasteCategory->category_group . ' (Legacy)';
            }

            $weight = (float) $d->weight;
            $price = (float) $d->price_per_unit;
            $subtotal = (float) $d->subtotal;

            $totalWeight += $weight;
            $totalAmount += $subtotal;

            $rows[] = [
                $date,
                $code,
                $nasabah,
                $catName,
                $groupName,
                $weight,
                $price,
                $subtotal
            ];
        }

        // Add summary row
        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            $totalWeight,
            '',
            $totalAmount
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // 1. Add filter information at the top
        $sheet->setCellValue('A1', 'LAPORAN SETORAN SAMPAH BANK SAMPAH');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $filters = $this->getFilters();
        $row = 2;
        foreach ($filters as $label => $value) {
            $sheet->setCellValue('A' . $row, $label . ':');
            $sheet->setCellValue('B' . $row, $value);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
        }

        // Leave an empty row, headings start at row 10
        $headingsRow = 10;
        
        // Style headings
        $sheet->getStyle('A' . $headingsRow . ':H' . $headingsRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '059669'] // Emerald Green
            ]
        ]);

        // Format Weight & Currency Columns for data rows
        $lastRow = $sheet->getHighestRow();
        
        // Weight formatting (Col F)
        $sheet->getStyle('F11:F' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00 "kg"');
        
        // Currency formatting (Col G, H)
        $sheet->getStyle('G11:H' . $lastRow)->getNumberFormat()->setFormatCode('"Rp" #,##0');

        // Style Total Row
        $sheet->getStyle('A' . $lastRow . ':H' . $lastRow)->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'F1F5F9'] // Light Slate Grey
            ]
        ]);

        return [];
    }

    public function startCell(): string
    {
        return 'A10';
    }
}
