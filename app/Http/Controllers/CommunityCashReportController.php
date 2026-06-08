<?php

namespace App\Http\Controllers;

use App\Models\CommunityCashLedger;
use App\Models\FundCategory;
use Illuminate\Http\Request;

class CommunityCashReportController extends Controller
{
    public function index(Request $request)
    {
        return $this->buildReport($request, 'community-cash.report.index');
    }

    public function publicReport(Request $request)
    {
        return $this->buildReport($request, 'warga.cash-report');
    }

    public function pdf(Request $request)
    {
        $query = CommunityCashLedger::with('fundCategory');
        $this->applyFilters($query, $request);

        $ledgers = $query->orderBy('id', 'desc')->get();
        $totalIn = (clone $query)->where('type', 'in')->sum('amount');
        $totalOut = (clone $query)->where('type', 'out')->sum('amount');
        $currentBalance = $totalIn - $totalOut;

        $view = 'community-cash.report.pdf';
        
        // This is a simple print-view; a full PDF library (like Snappy/DomPDF) 
        // would typically be used here, but we will render the view for print.
        return view($view, compact(
            'ledgers', 'totalIn', 'totalOut', 'currentBalance'
        ));
    }

    public function export(Request $request)
    {
        $query = CommunityCashLedger::with('fundCategory');
        $this->applyFilters($query, $request);

        $ledgers = $query->orderBy('id', 'desc')->get();
        
        // Dynamic filename
        $filename = 'laporan-kas-' . $this->getPeriodLabel($request) . '.csv';

        return response()->streamDownload(function () use ($ledgers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Keterangan', 'Kategori Dana', 'Masuk', 'Keluar', 'Saldo Berjalan']);

            foreach ($ledgers as $l) {
                fputcsv($handle, [
                    $l->date->format('Y-m-d'),
                    $l->description,
                    $l->fundCategory->name,
                    $l->type === 'in' ? $l->amount : '',
                    $l->type === 'out' ? $l->amount : '',
                    $l->balance,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildReport(Request $request, string $view)
    {
        $query = CommunityCashLedger::with('fundCategory');
        $this->applyFilters($query, $request);

        if ($request->has('export')) {
            return $this->export($request);
        }

        $totalIn = (clone $query)->where('type', 'in')->sum('amount');
        $totalOut = (clone $query)->where('type', 'out')->sum('amount');
        $currentBalance = $totalIn - $totalOut;

        $ledgers = $query->orderBy('id', 'desc')->paginate(15)->withQueryString()->fragment('table-section');

        // Balance per category (from all ledgers, not filtered)
        $categoryBalances = CommunityCashLedger::select('fund_category_id')
            ->selectRaw('MAX(id) as last_id')
            ->groupBy('fund_category_id')
            ->pluck('last_id');

        $balancePerCategory = CommunityCashLedger::whereIn('id', $categoryBalances)
            ->with('fundCategory')
            ->get();

        $categories = FundCategory::where('is_active', true)->get();

        return view($view, compact(
            'ledgers', 'totalIn', 'totalOut', 'currentBalance',
            'balancePerCategory', 'categories'
        ));
    }

    private function applyFilters($query, Request $request)
    {
        $periodType = $request->input('period_type', 'monthly');

        switch ($periodType) {
            case 'daily':
                $query->whereDate('date', $request->input('date', date('Y-m-d')));
                break;
            case 'weekly':
                $query->whereBetween('date', [$request->input('start_date', date('Y-m-d', strtotime('-7 days'))), $request->input('end_date', date('Y-m-d'))]);
                break;
            case 'monthly':
                $query->whereYear('date', $request->input('year', date('Y')))
                      ->whereMonth('date', $request->input('month', date('n')));
                break;
            case 'yearly':
                $query->whereYear('date', $request->input('year', date('Y')));
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
                }
                break;
        }

        if ($request->filled('fund_category_id')) {
            $query->where('fund_category_id', $request->fund_category_id);
        }
    }

    private function getPeriodLabel(Request $request)
    {
        switch ($request->input('period_type')) {
            case 'daily': return 'harian-' . $request->input('date', date('Y-m-d'));
            case 'weekly': return 'mingguan-' . $request->input('start_date', date('Y-m-d', strtotime('-7 days'))) . '-sd-' . $request->input('end_date', date('Y-m-d'));
            case 'monthly': return 'bulanan-' . $request->input('year', date('Y')) . '-' . str_pad($request->input('month', date('n')), 2, '0', STR_PAD_LEFT);
            case 'yearly': return 'tahunan-' . $request->input('year', date('Y'));
            case 'custom': return 'kustom-' . $request->input('start_date', 'awal') . '-sd-' . $request->input('end_date', 'akhir');
            default: return 'kas-' . now()->format('Y-m-d');
        }
    }
}
