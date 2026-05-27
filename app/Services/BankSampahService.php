<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SavingsLedger;
use App\Models\WasteBankCashLedger;
use App\Models\Withdrawal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BankSampahService
{
    public const MIN_DEPOSITS_BEFORE_WITHDRAWAL = 2;
    public function recordDeposit(array $data): Deposit
    {
        return DB::transaction(function () use ($data) {
            $customer = \App\Models\WasteCustomer::findOrFail($data['waste_customer_id']);
            $memberId = $customer->member_id;
            
            $totalAmount = collect($data['details'])->sum('subtotal');

            $deposit = Deposit::create([
                'member_id' => $memberId,
                'waste_customer_id' => $customer->id,
                'collector_id' => $data['collector_id'],
                'date' => $data['date'],
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['details'] as $detail) {
                DepositDetail::create([
                    'deposit_id' => $deposit->id,
                    'waste_category_id' => $detail['waste_category_id'],
                    'weight' => $detail['weight'],
                    'price_per_unit' => $detail['price_per_unit'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }

            SavingsLedger::create([
                'member_id' => $memberId,
                'waste_customer_id' => $customer->id,
                'type' => 'credit',
                'amount' => $totalAmount,
                'description' => 'Setoran sampah',
                'reference_type' => Deposit::class,
                'reference_id' => $deposit->id,
            ]);

            // EXPLICIT TRANSACTION LOGGING: deposit.create
            app(\App\Services\ActivityLogService::class)->logInfo(
                'deposit.create',
                "Mencatat setoran sampah sebesar Rp " . number_format($totalAmount, 0, ',', '.') . " untuk nasabah {$customer->name}.",
                [
                    'deposit_id' => $deposit->id,
                    'waste_customer_id' => $customer->id,
                    'total_amount' => $totalAmount,
                    'details' => $data['details']
                ]
            );

            return $deposit;
        });
    }

    public function recordWithdrawal(array $data): Withdrawal
    {
        return DB::transaction(function () use ($data) {
            $customer = \App\Models\WasteCustomer::findOrFail($data['waste_customer_id']);
            $memberId = $customer->member_id;

            $depositCount = Deposit::where(function($q) use ($customer) {
                $q->where('waste_customer_id', $customer->id);
                if ($customer->member_id) {
                    $q->orWhere(fn($q2) => $q2->whereNull('waste_customer_id')->where('member_id', $customer->member_id));
                }
            })->count();

            if ($depositCount < self::MIN_DEPOSITS_BEFORE_WITHDRAWAL) {
                throw new \App\Exceptions\MinimumDepositException($depositCount, self::MIN_DEPOSITS_BEFORE_WITHDRAWAL);
            }

            $balance = $this->getCustomerBalance($customer->id);

            if ($data['amount'] > $balance) {
                throw new \App\Exceptions\InsufficientBalanceException($balance);
            }

            $withdrawal = Withdrawal::create([
                'member_id' => $memberId,
                'waste_customer_id' => $customer->id,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'notes' => $data['notes'] ?? null,
            ]);

            SavingsLedger::create([
                'member_id' => $memberId,
                'waste_customer_id' => $customer->id,
                'type' => 'debit',
                'amount' => $data['amount'],
                'description' => $data['notes'] ?? 'Penarikan saldo',
                'reference_type' => Withdrawal::class,
                'reference_id' => $withdrawal->id,
            ]);

            // EXPLICIT TRANSACTION LOGGING: withdrawal.create
            app(\App\Services\ActivityLogService::class)->logInfo(
                'withdrawal.create',
                "Mencatat penarikan saldo sebesar Rp " . number_format($data['amount'], 0, ',', '.') . " untuk nasabah {$customer->name}.",
                [
                    'withdrawal_id' => $withdrawal->id,
                    'waste_customer_id' => $customer->id,
                    'amount' => $data['amount']
                ]
            );

            return $withdrawal;
        });
    }

    public function getCustomerBalance(int $customerId): float
    {
        $customer = \App\Models\WasteCustomer::findOrFail($customerId);

        $credit = SavingsLedger::where(function($q) use ($customer) {
            $q->where('waste_customer_id', $customer->id);
            if ($customer->member_id) {
                $q->orWhere(fn($q2) => $q2->whereNull('waste_customer_id')->where('member_id', $customer->member_id));
            }
        })->where('type', 'credit')->sum('amount');

        $debit = SavingsLedger::where(function($q) use ($customer) {
            $q->where('waste_customer_id', $customer->id);
            if ($customer->member_id) {
                $q->orWhere(fn($q2) => $q2->whereNull('waste_customer_id')->where('member_id', $customer->member_id));
            }
        })->where('type', 'debit')->sum('amount');

        return (float) ($credit - $debit);
    }

    public function getMemberBalance(int $memberId): float
    {
        $customer = \App\Models\WasteCustomer::where('member_id', $memberId)->first();
        if ($customer) {
            return $this->getCustomerBalance($customer->id);
        }

        $credit = SavingsLedger::where('member_id', $memberId)->where('type', 'credit')->sum('amount');
        $debit = SavingsLedger::where('member_id', $memberId)->where('type', 'debit')->sum('amount');

        return (float) ($credit - $debit);
    }

    public function getAllBalances(): Collection
    {
        return SavingsLedger::select('member_id')
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) - SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as balance")
            ->groupBy('member_id')
            ->with('member')
            ->get();
    }

    public function recordSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $details = collect($data['details'])->filter(fn ($d) => !empty($d['waste_category_id']) && !empty($d['weight']));

            if ($details->isEmpty()) {
                throw new \InvalidArgumentException('Minimal satu detail penjualan harus diisi.');
            }

            $collectorId = $data['collector_id'];

            // Load member prices for margin calculation
            $wastePrices = \App\Models\WastePrice::where('collector_id', $collectorId)
                ->pluck('member_price', 'waste_category_id');

            $totalMargin = 0;

            $details = $details->map(function ($d) use ($wastePrices, &$totalMargin) {
                $weight = (float) $d['weight'];
                $collectorPrice = (float) $d['price_per_unit'];
                $memberPrice = (float) ($wastePrices[$d['waste_category_id']] ?? 0);
                $subtotal = round($weight * $collectorPrice, 2);
                $margin = round(($collectorPrice - $memberPrice) * $weight, 2);
                $totalMargin += $margin;

                $d['subtotal'] = $subtotal;
                return $d;
            });

            if ($totalMargin < 0) {
                throw new \InvalidArgumentException('Total margin negatif. Periksa harga pengepul dan harga nasabah.');
            }

            $totalAmount = $details->sum('subtotal');

            $sale = Sale::create([
                'collector_id' => $collectorId,
                'date' => $data['date'],
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($details as $detail) {
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'waste_category_id' => $detail['waste_category_id'],
                    'weight' => $detail['weight'],
                    'price_per_unit' => $detail['price_per_unit'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }

            if ($totalMargin > 0) {
                $currentBalance = $this->getWasteBankBalance();

                WasteBankCashLedger::create([
                    'type' => 'in',
                    'amount' => $totalMargin,
                    'balance' => $currentBalance + $totalMargin,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'date' => $data['date'],
                    'description' => 'Keuntungan penjualan sampah ke ' . $sale->collector->name,
                ]);
            }

            return $sale;
        });
    }

    public function getWasteBankBalance(): float
    {
        $latest = WasteBankCashLedger::latest('id')->first();

        return $latest ? (float) $latest->balance : 0;
    }
}
