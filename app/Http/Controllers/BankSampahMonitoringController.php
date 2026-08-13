<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\SavingsLedger;
use App\Models\WasteCustomer;
use App\Models\Withdrawal;
use App\Services\BankSampahAuditService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BankSampahMonitoringController extends Controller
{
    protected BankSampahAuditService $auditService;

    public function __construct(BankSampahAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        // 1. Gather Core Metrics
        $totalCustomers = WasteCustomer::count();
        
        $totalCredit = SavingsLedger::where('type', 'credit')->sum('amount');
        $totalDebit = SavingsLedger::where('type', 'debit')->sum('amount');
        $totalSavings = $totalCredit - $totalDebit;

        $today = now()->toDateString();
        $todayDepositsCount = Deposit::query()
            ->whereDate('date', '=', $today)
            ->count();

        $todayWithdrawalsCount = Withdrawal::query()
            ->whereDate('date', '=', $today)
            ->count();
        $todayTransactions = $todayDepositsCount + $todayWithdrawalsCount;

        $thisMonthDeposits = Deposit::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total_amount');

        $thisMonthWithdrawals = Withdrawal::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        // 2. Run Audit Service
        $auditResult = $this->auditService->runAudit();

        // 3. Paginate Anomalies Lists for Dashboard
        $anomalies = $auditResult['anomalies'];
        
        $balanceMismatches = $this->paginateArray($anomalies['balance_mismatches'], 5, 'page_mismatch');
        $duplicateLedgers = $this->paginateArray($anomalies['duplicate_ledgers'], 5, 'page_duplicate');
        $negativeBalances = $this->paginateArray($anomalies['negative_balances'], 5, 'page_negative');
        $orphanTransactions = $this->paginateArray($anomalies['orphan_transactions'], 5, 'page_orphan_tx');
        $orphanLedgers = $this->paginateArray($anomalies['orphan_ledgers'], 5, 'page_orphan_ledger');
        $legacyUnmapped = $this->paginateArray($anomalies['legacy_unmapped_transactions'], 5, 'page_legacy');

        return view('bank-sampah.monitoring', [
            'totalCustomers' => $totalCustomers,
            'totalSavings' => $totalSavings,
            'todayTransactions' => $todayTransactions,
            'thisMonthDeposits' => $thisMonthDeposits,
            'thisMonthWithdrawals' => $thisMonthWithdrawals,
            'healthScore' => $auditResult['health_score'],
            'exitCode' => $auditResult['exit_code'],
            'severitySummary' => $auditResult['severity_summary'],
            'metrics' => $auditResult['metrics'],
            
            // Paginated anomalies
            'balanceMismatches' => $balanceMismatches,
            'duplicateLedgers' => $duplicateLedgers,
            'negativeBalances' => $negativeBalances,
            'orphanTransactions' => $orphanTransactions,
            'orphanLedgers' => $orphanLedgers,
            'legacyUnmapped' => $legacyUnmapped,
        ]);
    }

    /**
     * Helper to manually paginate an array.
     */
    private function paginateArray(array $items, int $perPage = 5, string $pageName = 'page'): LengthAwarePaginator
    {
        $page = request()->input($pageName, 1);
        $offset = ($page - 1) * $perPage;
        $sliced = array_slice($items, $offset, $perPage);
        
        return new LengthAwarePaginator($sliced, count($items), $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
            'pageName' => $pageName,
        ]);
    }
}
