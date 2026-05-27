<?php

namespace App\Console\Commands;

use App\Services\BankSampahAuditService;
use Illuminate\Console\Command;

class AuditBankSampahConsistency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank-sampah:audit {--customer-code=} {--json} {--summary-only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit Bank Sampah transaction balance consistency, orphan records, duplicates, and relations (Read-Only)';

    /**
     * Execute the console command.
     */
    public function handle(BankSampahAuditService $auditService): int
    {
        $customerCode = $this->option('customer-code');
        $isJson = $this->option('json');
        $summaryOnly = $this->option('summary-only');
        $isVerbose = $this->getOutput()->isVerbose();

        if (!$isJson) {
            $this->info('Starting read-only database consistency audit for Bank Sampah...');
        }

        $result = $auditService->runAudit($customerCode);

        $healthScore = $result['health_score'];
        $totalCustomersChecked = $result['total_customers_checked'];
        $metrics = $result['metrics'];
        $exitCode = $result['exit_code'];
        $severitySummary = $result['severity_summary'];
        $anomalies = $result['anomalies'];

        if ($totalCustomersChecked === 0 && !$isJson) {
            $this->warn('No waste customers found to audit.');
            return self::SUCCESS;
        }

        // Output Generation
        if ($isJson) {
            $report = [
                'health_score' => $healthScore,
                'total_customers_checked' => $totalCustomersChecked,
                'metrics' => $metrics,
                'exit_code' => $exitCode,
                'severity_summary' => $severitySummary
            ];

            if (!$summaryOnly) {
                $report['anomalies'] = $anomalies;
            }

            $this->line(json_encode($report, JSON_PRETTY_PRINT));
            return $exitCode;
        }

        // Console Output Format
        $this->info('==================================================');
        $this->info(" BANK SAMPAH DATA CONSISTENCY REPORT ");
        $this->info('==================================================');
        
        $this->warn("Health Score: {$healthScore}%");
        $this->line("Customers Checked:      {$totalCustomersChecked}");
        $this->line("Balance Mismatches [CRT]: " . count($anomalies['balance_mismatches']));
        $this->line("Duplicate Ledgers  [CRT]: " . count($anomalies['duplicate_ledgers']));
        $this->line("Negative Balances  [CRT]: " . count($anomalies['negative_balances']));
        $this->line("Orphan Transactions[HI] : " . count($anomalies['orphan_transactions']));
        $this->line("Orphan Ledgers     [HI] : " . count($anomalies['orphan_ledgers']));
        $this->line("Relation Mismatches[HI] : " . count($anomalies['relation_mismatches']));
        $this->line("Legacy Unmapped    [WRN]: " . count($anomalies['legacy_unmapped_transactions']));
        $this->info('==================================================');
        $this->line("Exit Code Status:       " . ($exitCode === 0 ? 'HEALTHY (0)' : ($exitCode === 1 ? 'WARNING (1)' : 'CRITICAL (2)')));
        $this->line("Duration (ms):          {$metrics['duration_ms']}ms");
        $this->info('==================================================');

        if (!$summaryOnly && ($isVerbose || $exitCode > 0)) {
            if (!empty($anomalies['balance_mismatches'])) {
                $this->error("\n[CRITICAL] Balance Mismatches:");
                foreach ($anomalies['balance_mismatches'] as $m) {
                    $this->line("  Nasabah {$m['customer_code']} ({$m['name']}): Kalkulasi Rp " . number_format($m['calculated'], 2) . " vs Ledger Rp " . number_format($m['ledger'], 2));
                }
            }

            if (!empty($anomalies['duplicate_ledgers'])) {
                $this->error("\n[CRITICAL] Duplicate Ledgers:");
                foreach ($anomalies['duplicate_ledgers'] as $dl) {
                    $this->line("  Customer ID {$dl['waste_customer_id']} with Amount Rp " . number_format($dl['amount'], 2) . " on {$dl['type']} #{$dl['id']} has {$dl['count']} ledger entries!");
                }
            }

            if (!empty($anomalies['negative_balances'])) {
                $this->error("\n[CRITICAL] Negative Balances:");
                foreach ($anomalies['negative_balances'] as $nb) {
                    $this->line("  Nasabah {$nb['customer_code']} ({$nb['name']}): Saldo Rp " . number_format($nb['balance'], 2));
                }
            }

            if (!empty($anomalies['orphan_transactions'])) {
                $this->error("\n[HIGH] Orphan Transactions (Missing Ledger):");
                foreach ($anomalies['orphan_transactions'] as $ot) {
                    $this->line("  {$ot}");
                }
            }

            if (!empty($anomalies['orphan_ledgers'])) {
                $this->error("\n[HIGH] Orphan Ledgers (Missing source Transaction):");
                foreach ($anomalies['orphan_ledgers'] as $ol) {
                    $this->line("  {$ol}");
                }
            }

            if (!empty($anomalies['relation_mismatches'])) {
                $this->error("\n[HIGH] Relation Mismatches (mismatched member_id pointers):");
                foreach ($anomalies['relation_mismatches'] as $rm) {
                    $this->line("  Nasabah {$rm['customer_code']} ({$rm['name']}):");
                    if (!empty($rm['deposit_ids'])) $this->line("    Deposits: " . implode(', ', $rm['deposit_ids']));
                    if (!empty($rm['withdrawal_ids'])) $this->line("    Withdrawals: " . implode(', ', $rm['withdrawal_ids']));
                    if (!empty($rm['ledger_ids'])) $this->line("    Ledgers: " . implode(', ', $rm['ledger_ids']));
                }
            }

            if (!empty($anomalies['legacy_unmapped_transactions'])) {
                $this->warn("\n[WARNING] Legacy Unmapped Transactions:");
                foreach ($anomalies['legacy_unmapped_transactions'] as $lud) {
                    $this->line("  Table: {$lud['table']} | ID: {$lud['transaction_id']} | Member ID: " . ($lud['member_id'] ?? 'NULL') . " | Created At: " . ($lud['created_at'] ?? 'N/A'));
                }
            }
        }

        return $exitCode;
    }
}
