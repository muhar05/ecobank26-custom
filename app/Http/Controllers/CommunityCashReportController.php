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

    private function buildReport(Request $request, string $view)
    {
        $query = CommunityCashLedger::with('fundCategory');

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        if ($request->filled('fund_category_id')) {
            $query->where('fund_category_id', $request->fund_category_id);
        }

        $ledgers = $query->orderBy('id')->get();

        $totalIn = $ledgers->where('type', 'in')->sum('amount');
        $totalOut = $ledgers->where('type', 'out')->sum('amount');
        $currentBalance = $totalIn - $totalOut;

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
}
