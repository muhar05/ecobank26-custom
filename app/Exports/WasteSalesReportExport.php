<?php

namespace App\Exports;

use App\Models\SaleDetail;
use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class WasteSalesReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithCustomStartCell
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Laporan Penjualan Agregator';
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

        $collectorName = 'Semua Agregator';
        if ($id = $this->request->input('collector_id')) {
            $collectorName = Collector::find($id)->name ?? 'Semua Agregator';
        }

        $groupName = 'Semua Grup';
        if ($id = $this->request->input('waste_category_group_id')) {
            $groupName = WasteCategoryGroup::find($id)->name ?? 'Semua Grup';
        }

        $categoryName = 'Semua Kategori';
        if ($id = $this->request->input('waste_category_id')) {
            $categoryName = WasteCategory::find($id)->name ?? 'Semua Kategori';
        }

        return [
            'Periode' => $startDate->toDateString() . ' s/d ' . $endDate->toDateString(),
            'Agregator' => $collectorName,
            'Grup Kategori' => $groupName,
            'Kategori' => $categoryName,
            'Dibuat Pada' => now()->toDateTimeString(),
            'Dibuat Oleh' => auth()->user() ? auth()->user()->name : 'System'
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Penjualan',
            'Agregator',
            'Kategori Sampah',
            'Grup Kategori',
            'Berat',
            'Harga Jual Agregator',
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

        $collectorId = $this->request->input('collector_id');
        $groupId = $this->request->input('waste_category_group_id');
        $categoryId = $this->request->input('waste_category_id');

        $query = SaleDetail::with(['sale.collector', 'wasteCategory.wasteCategoryGroup'])
            ->whereHas('sale', function($q) use ($startDate, $endDate, $collectorId) {
                $q->whereBetween('date', [$startDate, $endDate])
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
            $date = Carbon::instance($d->sale->date)->toDateString();
            $code = 'SAL-' . str_pad($d->sale->id, 5, '0', STR_PAD_LEFT);
            $agregator = $d->sale->collector->name ?? '-';
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
                $agregator,
                $catName,
                $groupName,
                $weight,
                $price,
                $subtotal
            ];
        }

        // Total row
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
        $sheet->setCellValue('A1', 'LAPORAN PENJUALAN SAMPAH KE AGREGATOR');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $filters = $this->getFilters();
        $row = 2;
        foreach ($filters as $label => $value) {
            $sheet->setCellValue('A' . $row, $label . ':');
            $sheet->setCellValue('B' . $row, $value);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
        }

        $headingsRow = 9;
        
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

        $lastRow = $sheet->getHighestRow();
        
        $sheet->getStyle('F10:F' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00 "kg"');
        $sheet->getStyle('G10:H' . $lastRow)->getNumberFormat()->setFormatCode('"Rp" #,##0');

        $sheet->getStyle('A' . $lastRow . ':H' . $lastRow)->applyFromArray([
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
        return 'A9';
    }
}
