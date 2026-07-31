<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function bankSampah()
    {
        $totalMembers = \App\Models\Member::count();
        $totalCredit = \App\Models\SavingsLedger::where('type', 'credit')->sum('amount');
        $totalDebit = \App\Models\SavingsLedger::where('type', 'debit')->sum('amount');
        $totalSales = \App\Models\Sale::sum('total_amount');
        $wasteBankCashBalance = \App\Models\WasteBankCashLedger::latest('id')->value('balance') ?? 0;
        $recentLedgers = \App\Models\SavingsLedger::with('member')
            ->latest('id')->limit(5)->get();
        $recentCashLedgers = \App\Models\WasteBankCashLedger::latest('id')->limit(5)->get();

        return view('dashboard.admin-bank-sampah', [
            'totalMembers' => $totalMembers,
            'totalSavings' => $totalCredit - $totalDebit,
            'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit,
            'totalSales' => $totalSales,
            'wasteBankCashBalance' => $wasteBankCashBalance,
            'recentLedgers' => $recentLedgers,
            'recentCashLedgers' => $recentCashLedgers,
        ]);
    }
}