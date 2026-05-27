<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\SavingsLedger;
use App\Models\WasteCustomer;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BankSampahAuditService
{
    /**
     * Run the complete data consistency audit.
     *
     * @param string|null $customerCode
     * @return array
     */
    public function runAudit(?string $customerCode = null): array
    {
        $startTime = microtime(true);

        // 1. Gather Target Customers
        $query = WasteCustomer::query();
        if ($customerCode) {
            $query->where('customer_code', $customerCode);
        }
        $customers = $query->get();
        $totalCustomersChecked = $customers->count();

        // Initialize anomaly logs
        $mismatchBalances = [];
        $negativeBalances = [];
        $orphanTransactions = [];
        $orphanLedgers = [];
        $duplicateLedgers = [];
        $relationMismatches = [];
        $legacyUnmappedDetails = [];

        if ($totalCustomersChecked > 0) {
            // 2. Perform Scans
            // A. Per Customer Audits
            foreach ($customers as $customer) {
                // Calculated Balance from core transaction tables
                $sumDeposits = Deposit::where('waste_customer_id', $customer->id)->sum('total_amount');
                $sumWithdrawals = Withdrawal::where('waste_customer_id', $customer->id)->sum('amount');
                $calculatedBalance = (float) ($sumDeposits - $sumWithdrawals);

                // Ledger balance sum
                $ledgerCredit = SavingsLedger::where('waste_customer_id', $customer->id)->where('type', 'credit')->sum('amount');
                $ledgerDebit = SavingsLedger::where('waste_customer_id', $customer->id)->where('type', 'debit')->sum('amount');
                $ledgerBalance = (float) ($ledgerCredit - $ledgerDebit);

                if (abs($calculatedBalance - $ledgerBalance) > 0.001) {
                    $mismatchBalances[] = [
                        'customer_code' => $customer->customer_code,
                        'name' => $customer->name,
                        'calculated' => $calculatedBalance,
                        'ledger' => $ledgerBalance,
                        'diff' => abs($calculatedBalance - $ledgerBalance)
                    ];
                }

                if ($ledgerBalance < 0) {
                    $negativeBalances[] = [
                        'customer_code' => $customer->customer_code,
                        'name' => $customer->name,
                        'balance' => $ledgerBalance
                    ];
                }

                // Relationship mismatches check
                if ($customer->member_id) {
                    $mismatchedDeposits = Deposit::where('waste_customer_id', $customer->id)
                        ->where('member_id', '!=', $customer->member_id)
                        ->pluck('id')->toArray();
                    
                    $mismatchedWithdrawals = Withdrawal::where('waste_customer_id', $customer->id)
                        ->where('member_id', '!=', $customer->member_id)
                        ->pluck('id')->toArray();

                    $mismatchedLedgers = SavingsLedger::where('waste_customer_id', $customer->id)
                        ->where('member_id', '!=', $customer->member_id)
                        ->pluck('id')->toArray();

                    if (!empty($mismatchedDeposits) || !empty($mismatchedWithdrawals) || !empty($mismatchedLedgers)) {
                        $relationMismatches[] = [
                            'customer_code' => $customer->customer_code,
                            'name' => $customer->name,
                            'deposit_ids' => $mismatchedDeposits,
                            'withdrawal_ids' => $mismatchedWithdrawals,
                            'ledger_ids' => $mismatchedLedgers
                        ];
                    }
                }
            }
        }

        // B. Orphan Transactions Scan
        $depositsWithoutLedger = Deposit::whereNotNull('waste_customer_id')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('savings_ledgers')
                  ->whereRaw('savings_ledgers.reference_type = ? AND savings_ledgers.reference_id = deposits.id', [Deposit::class]);
            })->pluck('id')->map(fn($id) => "Deposit #{$id}")->toArray();

        $withdrawalsWithoutLedger = Withdrawal::whereNotNull('waste_customer_id')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('savings_ledgers')
                  ->whereRaw('savings_ledgers.reference_type = ? AND savings_ledgers.reference_id = withdrawals.id', [Withdrawal::class]);
            })->pluck('id')->map(fn($id) => "Withdrawal #{$id}")->toArray();

        $orphanTransactions = array_merge($depositsWithoutLedger, $withdrawalsWithoutLedger);

        // C. Orphan Ledgers Scan
        $ledgersWithoutDeposit = SavingsLedger::whereNotNull('waste_customer_id')
            ->where('reference_type', Deposit::class)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('deposits')
                  ->whereRaw('deposits.id = savings_ledgers.reference_id');
            })->pluck('id')->map(fn($id) => "Ledger #{$id} (Deposit source missing)")->toArray();

        $ledgersWithoutWithdrawal = SavingsLedger::whereNotNull('waste_customer_id')
            ->where('reference_type', Withdrawal::class)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('withdrawals')
                  ->whereRaw('withdrawals.id = savings_ledgers.reference_id');
            })->pluck('id')->map(fn($id) => "Ledger #{$id} (Withdrawal source missing)")->toArray();

        $orphanLedgers = array_merge($ledgersWithoutDeposit, $ledgersWithoutWithdrawal);

        // D. Duplicate Ledgers Scan (grouped precisely by waste_customer_id, amount, reference_type, reference_id)
        $duplicates = SavingsLedger::select('waste_customer_id', 'amount', 'reference_type', 'reference_id')
            ->whereNotNull('reference_type')
            ->whereNotNull('reference_id')
            ->groupBy('waste_customer_id', 'amount', 'reference_type', 'reference_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $duplicateLedgers[] = [
                'waste_customer_id' => $dup->waste_customer_id,
                'amount' => (float) $dup->amount,
                'type' => class_basename($dup->reference_type),
                'id' => $dup->reference_id,
                'count' => SavingsLedger::where('waste_customer_id', $dup->waste_customer_id)
                    ->where('amount', $dup->amount)
                    ->where('reference_type', $dup->reference_type)
                    ->where('reference_id', $dup->reference_id)
                    ->count()
            ];
        }

        // E. Legacy Unmapped Transactions Detail Scanning
        $legacyDeposits = Deposit::whereNull('waste_customer_id')->get(['id', 'member_id', 'created_at']);
        foreach ($legacyDeposits as $d) {
            $legacyUnmappedDetails[] = [
                'table' => 'deposits',
                'transaction_id' => $d->id,
                'member_id' => $d->member_id,
                'created_at' => $d->created_at ? $d->created_at->toIso8601String() : null
            ];
        }

        $legacyWithdrawals = Withdrawal::whereNull('waste_customer_id')->get(['id', 'member_id', 'created_at']);
        foreach ($legacyWithdrawals as $w) {
            $legacyUnmappedDetails[] = [
                'table' => 'withdrawals',
                'transaction_id' => $w->id,
                'member_id' => $w->member_id,
                'created_at' => $w->created_at ? $w->created_at->toIso8601String() : null
            ];
        }

        $legacyLedgers = SavingsLedger::whereNull('waste_customer_id')->get(['id', 'member_id', 'created_at']);
        foreach ($legacyLedgers as $l) {
            $legacyUnmappedDetails[] = [
                'table' => 'savings_ledgers',
                'transaction_id' => $l->id,
                'member_id' => $l->member_id,
                'created_at' => $l->created_at ? $l->created_at->toIso8601String() : null
            ];
        }

        // Health Score Calculation
        // Weights:
        // Critical: Balance mismatch (20), Duplicate ledger (15), Negative Balance (15)
        // High: Orphan transaction (10), Orphan ledger (10), Relation mismatch (8)
        // Warning: Legacy unmapped (2)
        $totalDeductions = (count($mismatchBalances) * 20)
            + (count($duplicateLedgers) * 15)
            + (count($negativeBalances) * 15)
            + (count($orphanTransactions) * 10)
            + (count($orphanLedgers) * 10)
            + (count($relationMismatches) * 8)
            + (count($legacyUnmappedDetails) * 2);

        $healthScore = max(0.0, 100.0 - $totalDeductions);

        // Process Metrics
        $endTime = microtime(true);
        $durationMs = round(($endTime - $startTime) * 1000, 2);
        $startedAt = Carbon::createFromTimestampMs($startTime * 1000)->toIso8601String();
        $finishedAt = Carbon::createFromTimestampMs($endTime * 1000)->toIso8601String();

        $metrics = [
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'duration_ms' => $durationMs
        ];

        // Anomaly Status check
        $hasCritical = !empty($mismatchBalances) || !empty($duplicateLedgers) || !empty($negativeBalances);
        $hasHigh = !empty($orphanTransactions) || !empty($orphanLedgers) || !empty($relationMismatches);

        if ($healthScore === 100.0) {
            $exitCode = 0; // Healthy
        } elseif ($hasCritical || $hasHigh || $healthScore < 80.0) {
            $exitCode = 2; // Critical
        } else {
            $exitCode = 1; // Warning
        }

        return [
            'health_score' => $healthScore,
            'total_customers_checked' => $totalCustomersChecked,
            'metrics' => $metrics,
            'exit_code' => $exitCode,
            'severity_summary' => [
                'critical_count' => count($mismatchBalances) + count($duplicateLedgers) + count($negativeBalances),
                'high_count' => count($orphanTransactions) + count($orphanLedgers) + count($relationMismatches),
                'warning_count' => count($legacyUnmappedDetails)
            ],
            'anomalies' => [
                'balance_mismatches' => $mismatchBalances,
                'orphan_transactions' => $orphanTransactions,
                'orphan_ledgers' => $orphanLedgers,
                'duplicate_ledgers' => $duplicateLedgers,
                'relation_mismatches' => $relationMismatches,
                'negative_balances' => $negativeBalances,
                'legacy_unmapped_transactions' => $legacyUnmappedDetails
            ]
        ];
    }
}
