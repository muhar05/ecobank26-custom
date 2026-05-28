<?php

namespace App\Http\Controllers;

use App\Models\CommunityCashLedger;
use App\Models\FundCategory;
use App\Models\Rt;
use App\Models\Bill;
use App\Services\RtScopeService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(private RtScopeService $rtScope) {}
    public function adminRw()
    {
        // Agregasi Lintas RT
        $totalRts = \App\Models\Rt::count();
        $totalKks = \App\Models\Kk::count();
        $totalMembers = \App\Models\Member::count();
        
        $unpaidBills = \App\Models\Bill::whereIn('status', ['unpaid', 'partially_paid'])->with('payments')->get();
        $totalTunggakan = $unpaidBills->sum(fn($b) => $b->outstanding_balance);

        $cashSummary = $this->getCashSummary();
        $totalKasWarga = $cashSummary['balance'];

        // Bank Sampah Summary
        $totalCredit = \App\Models\SavingsLedger::where('type', 'credit')->sum('amount');
        $totalDebit = \App\Models\SavingsLedger::where('type', 'debit')->sum('amount');
        $savingsBalance = $totalCredit - $totalDebit;
        $wasteBankCashBalance = \App\Models\WasteBankCashLedger::latest('id')->value('balance') ?? 0;
        $totalSales = \App\Models\Sale::sum('total_amount');

        // Charts & Recents for RW
        $categoryBalances = $this->getCategoryBalances();
        $recentLedgers = CommunityCashLedger::with('fundCategory')
            ->latest('id')->limit(5)->get();
        $recentSavings = \App\Models\SavingsLedger::with('member')
            ->latest('id')->limit(5)->get();

        // RT Comparison Summary
        $rtsSummary = Rt::withCount('kks')->orderBy('rt_number')->get()->map(function ($rt) {
            $rtBills = Bill::whereHas('kk', fn($q) => $q->where('rt_id', $rt->id));
            $rt->bills_amount   = (float) $rtBills->sum('amount');
            $rt->payments_amount = (float) $rtBills->where('status', 'paid')->sum('amount');
            $rt->arrears_amount  = max(0.00, $rt->bills_amount - $rt->payments_amount);
            return $rt;
        });

        $bestRtPayment     = $rtsSummary->sortByDesc('payments_amount')->first();
        $highestRtArrears  = $rtsSummary->sortByDesc('arrears_amount')->first();

        return view('dashboard.admin-rw', [
            'totalRts' => $totalRts,
            'totalKks' => $totalKks,
            'totalMembers' => $totalMembers,
            'totalTunggakan' => $totalTunggakan,
            'totalKasWarga' => $totalKasWarga,
            'totalIn' => $cashSummary['totalIn'],
            'totalOut' => $cashSummary['totalOut'],
            'balance' => $totalKasWarga,
            
            // Bank Sampah
            'savingsBalance' => $savingsBalance,
            'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit,
            'wasteBankCashBalance' => $wasteBankCashBalance,
            'totalSales' => $totalSales,

            // Lists
            'categoryBalances' => $categoryBalances,
            'recentLedgers' => $recentLedgers,
            'recentSavings' => $recentSavings,
            'totalCategories' => FundCategory::where('is_active', true)->count(),

            // RT Comparison
            'rtsSummary' => $rtsSummary,
            'bestRtPayment' => $bestRtPayment,
            'highestRtArrears' => $highestRtArrears,
        ]);
    }

    public function adminRt()
    {
        $user = auth()->user();
        $rtId = $this->rtScope->getUserRtId($user);

        $data = $this->getCashSummary();
        $data['totalCategories'] = FundCategory::where('is_active', true)->visibleToRt($rtId)->count();
        $data['categoryBalances'] = $this->getCategoryBalances();
        $data['recentLedgers'] = CommunityCashLedger::with('fundCategory')
            ->latest('id')->limit(5)->get();

        // Statistik iuran scoped ke RT ini
        if ($rtId) {
            $rtBills = \App\Models\Bill::whereHas('kk', fn($q) => $q->where('rt_id', $rtId))->with('payments');
            $data['totalKkInRt'] = \App\Models\Kk::where('rt_id', $rtId)->count();
            $data['totalBillsRt'] = (float) $rtBills->sum('amount');
            $unpaidRt = (clone $rtBills)->whereIn('status', ['unpaid', 'partially_paid'])->get();
            $data['totalTunggakanRt'] = $unpaidRt->sum(fn($b) => $b->outstanding_balance);
            $data['rt'] = \App\Models\Rt::find($rtId);
        } else {
            $data['totalKkInRt'] = 0;
            $data['totalBillsRt'] = 0;
            $data['totalTunggakanRt'] = 0;
            $data['rt'] = null;
        }

        // Bank Sampah
        $data['totalMembers'] = \App\Models\Member::count();
        $totalCredit = \App\Models\SavingsLedger::where('type', 'credit')->sum('amount');
        $totalDebit = \App\Models\SavingsLedger::where('type', 'debit')->sum('amount');
        $data['savingsBalance'] = $totalCredit - $totalDebit;
        $data['totalCredit'] = $totalCredit;
        $data['totalDebit'] = $totalDebit;
        $data['recentSavings'] = \App\Models\SavingsLedger::with('member')
            ->latest('id')->limit(5)->get();

        // Sales & Waste Bank Cash
        $data['totalSales'] = \App\Models\Sale::sum('total_amount');
        $data['wasteBankCashBalance'] = \App\Models\WasteBankCashLedger::latest('id')->value('balance') ?? 0;

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
        $data['categoryBalances'] = $this->getCategoryBalances();

        return view('dashboard.bendahara', $data);
    }

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

    public function warga()
    {
        $data = $this->getCashSummary();
        $data['recentLedgers'] = CommunityCashLedger::with('fundCategory')
            ->latest('id')->limit(5)->get();

        // Savings balance for linked member & waste customers
        $user = auth()->user();
        $member = $user->member;
        $data['member'] = $member;
        $data['kk'] = null;

        $customerIds = \App\Models\WasteCustomer::where(function($q) use ($user, $member) {
            $q->where('user_id', $user->id);
            if ($member) {
                $q->orWhere('member_id', $member->id);
            }
        })->pluck('id');

        $hasCustomer = !$customerIds->isEmpty();

        if ($hasCustomer || $member) {
            $credit = \App\Models\SavingsLedger::where(function($q) use ($customerIds, $member) {
                $q->whereIn('waste_customer_id', $customerIds);
                if ($member) {
                    $q->orWhere(fn($q2) => $q2->whereNull('waste_customer_id')->where('member_id', $member->id));
                }
            })->where('type', 'credit')->sum('amount');

            $debit = \App\Models\SavingsLedger::where(function($q) use ($customerIds, $member) {
                $q->whereIn('waste_customer_id', $customerIds);
                if ($member) {
                    $q->orWhere(fn($q2) => $q2->whereNull('waste_customer_id')->where('member_id', $member->id));
                }
            })->where('type', 'debit')->sum('amount');

            $data['savingsBalance'] = $credit - $debit;
            $data['savingsCredit'] = $credit;
            $data['savingsDebit'] = $debit;

            // KK Billing integration
            if ($member && $member->kk_id) {
                $kk = \App\Models\Kk::with('rt')->find($member->kk_id);
                $data['kk'] = $kk;

                if ($kk) {
                    $currentMonth = date('n');
                    $currentYear = date('Y');

                    $currentMonthBills = \App\Models\Bill::where('kk_id', $kk->id)
                        ->where('month', $currentMonth)
                        ->where('year', $currentYear)
                        ->get();

                    $data['totalBillCurrentMonth'] = $currentMonthBills->sum('amount');
                    $data['totalPaidCurrentMonth'] = $currentMonthBills->sum(fn($b) => $b->total_paid);
                    $data['sisaTunggakanCurrentMonth'] = $currentMonthBills->sum(fn($b) => $b->outstanding_balance);

                    $allKkBills = \App\Models\Bill::where('kk_id', $kk->id)->with(['fundCategory', 'payments'])->get();
                    $data['totalKkTunggakan'] = $allKkBills->sum(fn($b) => $b->outstanding_balance);
                    $data['totalKkPaid'] = $allKkBills->sum(fn($b) => $b->total_paid);

                    $data['recentBills'] = \App\Models\Bill::where('kk_id', $kk->id)
                        ->with(['fundCategory', 'payments'])
                        ->latest()
                        ->limit(5)
                        ->get();

                    $data['recentPayments'] = \App\Models\BillPayment::whereHas('bill', fn($q) => $q->where('kk_id', $kk->id))
                        ->with('bill.fundCategory')
                        ->latest()
                        ->limit(5)
                        ->get();
                }
            }
        } else {
            $data['savingsBalance'] = null;
            $data['savingsCredit'] = 0;
            $data['savingsDebit'] = 0;
        }

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
