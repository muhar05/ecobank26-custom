<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\FundCategory;
use App\Models\Kk;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillService
{
    /**
     * Generate monthly bills for all active and contract KKs
     * for all mandatory fund categories.
     *
     * @param int $month
     * @param int $year
     * @return int Number of bills successfully generated
     */
    public function generateMonthlyBills(int $month, int $year): int
    {
        return DB::transaction(function () use ($month, $year) {
            // 1. Get all active and contract KKs
            $kks = Kk::with('rt')->activeOrContract()->get();

            // 2. Get all mandatory fund categories
            $mandatoryCategories = FundCategory::where('is_mandatory', true)
                ->where('monthly_amount', '>', 0)
                ->get();

            if ($kks->isEmpty() || $mandatoryCategories->isEmpty()) {
                return 0;
            }

            $generatedCount = 0;
            $dueDays = config('billing.default_due_days', env('DEFAULT_DUE_DAYS', 10));
            $dueDate = now()->addDays($dueDays)->toDateString();

            // Format year and month for bill code: YYYYMM (e.g. 202605)
            $periodString = $year . str_pad($month, 2, '0', STR_PAD_LEFT);

            foreach ($kks as $kk) {
                foreach ($mandatoryCategories as $category) {
                    // Check if bill already exists to prevent duplication
                    $exists = Bill::where([
                        'kk_id' => $kk->id,
                        'fund_category_id' => $category->id,
                        'month' => $month,
                        'year' => $year,
                    ])->exists();

                    if (!$exists) {
                        // Count current bills in the system for this month & year to get incremental counter
                        $currentCount = Bill::where('month', $month)
                            ->where('year', $year)
                            ->count();

                        $increment = str_pad($currentCount + $generatedCount + 1, 4, '0', STR_PAD_LEFT);
                        $rtString = 'RT' . str_pad(preg_replace('/[^0-9]/', '', $kk->rt->rt_number), 3, '0', STR_PAD_LEFT);
                        
                        // Example format: BILL-202605-RT001-0001
                        $billCode = "BILL-{$periodString}-{$rtString}-{$increment}";

                        Bill::create([
                            'kk_id' => $kk->id,
                            'fund_category_id' => $category->id,
                            'bill_code' => $billCode,
                            'amount' => $category->monthly_amount,
                            'due_date' => $dueDate,
                            'month' => $month,
                            'year' => $year,
                            'status' => 'unpaid',
                        ]);

                        $generatedCount++;
                    }
                }
            }

            return $generatedCount;
        });
    }

    /**
     * Record a payment for a specific bill.
     *
     * @param int $billId
     * @param array $data
     * @return \App\Models\BillPayment
     */
    public function payBill(int $billId, array $data): \App\Models\BillPayment
    {
        return DB::transaction(function () use ($billId, $data) {
            $bill = Bill::findOrFail($billId);
            $amountPaid = (float) $data['amount_paid'];

            // 1. Validate if amount paid doesn't exceed outstanding balance
            $outstanding = $bill->outstanding_balance;
            if ($amountPaid > $outstanding) {
                throw new \InvalidArgumentException("Nominal pembayaran melebihi sisa tagihan (Sisa: Rp " . number_format($outstanding, 0, ',', '.') . ").");
            }

            // 2. Generate receipt number automatically: RCPT-YYYYMM-[4-digit increment]
            $periodString = date('Ym');
            $rcptCount = \App\Models\BillPayment::where('receipt_number', 'like', "RCPT-{$periodString}-%")->count();
            $increment = str_pad($rcptCount + 1, 4, '0', STR_PAD_LEFT);
            $receiptNumber = "RCPT-{$periodString}-{$increment}";

            // 3. Find/get member_id if exists for this KK (use head of household or first member if any)
            $kk = $bill->kk;
            $member = $kk->members()->first();

            // 4. Record contribution into the community cash ledger using CommunityCashService
            $cashService = app(\App\Services\CommunityCashService::class);
            
            $monthName = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ][$bill->month] ?? $bill->month;

            $description = "Pembayaran " . $bill->fundCategory->name . " Periode " . $monthName . " " . $bill->year . " - KK " . $kk->family_head . " (" . $bill->bill_code . ")";
            
            $contribution = $cashService->recordContribution([
                'fund_category_id' => $bill->fund_category_id,
                'member_id' => $member ? $member->id : null,
                'member_name' => $kk->family_head,
                'amount' => $amountPaid,
                'date' => $data['paid_at'] ?? now()->toDateString(),
                'description' => $description,
                'recorded_by' => auth()->id(),
            ]);

            // 5. Save payment to bill_payments
            $payment = \App\Models\BillPayment::create([
                'bill_id' => $bill->id,
                'community_contribution_id' => $contribution->id,
                'receipt_number' => $receiptNumber,
                'amount_paid' => $amountPaid,
                'payment_method' => $data['payment_method'],
                'paid_at' => $data['paid_at'] ?? now(),
            ]);

            // 6. Update bill status based on total paid
            // Wait, we need to refresh the relation or calculate outstanding balance manually to avoid stale model cache
            $currentTotalPaid = (float) \App\Models\BillPayment::where('bill_id', $bill->id)->sum('amount_paid');
            if ($currentTotalPaid >= (float) $bill->amount) {
                $bill->update(['status' => 'paid']);
            } else {
                $bill->update(['status' => 'partially_paid']);
            }

            // EXPLICIT TRANSACTION LOGGING: bill.payment
            app(\App\Services\ActivityLogService::class)->logInfo(
                'bill.payment',
                "Mencatat pembayaran tagihan {$bill->bill_code} sebesar Rp " . number_format($amountPaid, 0, ',', '.') . " untuk KK {$kk->family_head}.",
                [
                    'bill_id' => $bill->id,
                    'bill_code' => $bill->bill_code,
                    'receipt_number' => $receiptNumber,
                    'amount_paid' => $amountPaid,
                    'payment_method' => $data['payment_method'],
                ]
            );
 
            return $payment;
        });
    }
}
