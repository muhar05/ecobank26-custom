<?php

namespace App\Http\Controllers;

use App\Models\CommunityCashLedger;
use App\Models\FundCategory;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function adminRt()
    {
        $data = $this->getCashSummary();
        $data['totalCategories'] = FundCategory::where('is_active', true)->count();
        $data['categoryBalances'] = $this->getCategoryBalances();
        $data['recentLedgers'] = CommunityCashLedger::with('fundCategory')
            ->latest('id')->limit(5)->get();

        // Bank Sampah
        $data['totalMembers'] = \App\Models\Member::count();
        $totalCredit = \App\Models\SavingsLedger::where('type', 'credit')->sum('amount');
        $totalDebit = \App\Models\SavingsLedger::where('type', 'debit')->sum('amount');
        $data['savingsBalance'] = $totalCredit - $totalDebit;
        $data['totalCredit'] = $totalCredit;
        $data['totalDebit'] = $totalDebit;
        $data['recentSavings'] = \App\Models\SavingsLedger::with('member')
            ->latest('id')->limit(5)->get();

        return view('dashboard.admin-rt', $data);
    }

    public function bendahara()
    {
        $data = $this->getCashSummary();
        $month = Carbon::now();
        $data['monthIn'] = CommunityCashLedger::where('type', 'in')
            ->whereMonth('date', $month->month)->whereYear('date', $month->year)->sum('amount');
        $data['monthOut'] = CommunityCashLedger::where('type', 'out')
            ->whereMonth('date', $month->month)->whereYear('date', $month->year)->sum('amount');
        $data['recentLedgers'] = CommunityCashLedger::with('fundCategory')
            ->latest('id')->limit(5)->get();

        return view('dashboard.bendahara', $data);
    }

    public function bankSampah()
    {
        $totalMembers = \App\Models\Member::count();
        $totalCredit = \App\Models\SavingsLedger::where('type', 'credit')->sum('amount');
        $totalDebit = \App\Models\SavingsLedger::where('type', 'debit')->sum('amount');
        $recentLedgers = \App\Models\SavingsLedger::with('member')
            ->latest('id')->limit(5)->get();

        return view('dashboard.admin-bank-sampah', [
            'totalMembers' => $totalMembers,
            'totalSavings' => $totalCredit - $totalDebit,
            'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit,
            'recentLedgers' => $recentLedgers,
        ]);
    }

    public function warga()
    {
        $data = $this->getCashSummary();
        $data['recentLedgers'] = CommunityCashLedger::with('fundCategory')
            ->latest('id')->limit(5)->get();

        return view('dashboard.warga', $data);
    }

    private function getCashSummary(): array
    {
        $totalIn = CommunityCashLedger::where('type', 'in')->sum('amount');
        $totalOut = CommunityCashLedger::where('type', 'out')->sum('amount');

        return [
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'balance' => $totalIn - $totalOut,
        ];
    }

    private function getCategoryBalances()
    {
        $lastIds = CommunityCashLedger::select('fund_category_id')
            ->selectRaw('MAX(id) as last_id')
            ->groupBy('fund_category_id')
            ->pluck('last_id');

        return CommunityCashLedger::whereIn('id', $lastIds)->with('fundCategory')->get();
    }
}
