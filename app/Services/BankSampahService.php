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
        // BUG FIX #4: Pisahkan activity logging dari DB::transaction.
        // Jika logging dilakukan di dalam transaction dan terjadi exception setelah logging
        // (misalnya di hook/observer lain), log ikut dirollback sehingga transaksi
        // yang berhasil tidak pernah tercatat sama sekali (silent data loss).
        $deposit = DB::transaction(function () use ($data) {
            $customer = \App\Models\WasteCustomer::findOrFail($data['waste_customer_id']);
            $memberId = $customer->member_id;

            $totalAmount = collect($data['details'])->sum('subtotal');

            $deposit = Deposit::create([
                'member_id'        => $memberId,
                'waste_customer_id'=> $customer->id,
                'collector_id'     => $data['collector_id'],
                'date'             => $data['date'],
                'total_amount'     => $totalAmount,
                'notes'            => $data['notes'] ?? null,
            ]);

            foreach ($data['details'] as $detail) {
                DepositDetail::create([
                    'deposit_id'        => $deposit->id,
                    'waste_category_id' => $detail['waste_category_id'],
                    'weight'            => $detail['weight'],
                    'price_per_unit'    => $detail['price_per_unit'],
                    'subtotal'          => $detail['subtotal'],
                ]);
            }

            SavingsLedger::create([
                'member_id'        => $memberId,
                'waste_customer_id'=> $customer->id,
                'type'             => 'credit',
                'amount'           => $totalAmount,
                'description'      => 'Setoran sampah',
                'reference_type'   => Deposit::class,
                'reference_id'     => $deposit->id,
            ]);

            // Simpan referensi ke deposit agar bisa diakses setelah transaction commit.
            $deposit->_customer = $customer;
            $deposit->_totalAmount = $totalAmount;

            return $deposit;
        });

        // BUG FIX #1 & #4: Activity log ditulis SETELAH transaction commit.
        // Tambahkan 'customer_name' ke payload — kunci ini dibaca oleh
        // ActivityLog::getHumanDescriptionAttribute() untuk menampilkan nama nasabah.
        // Sebelumnya key ini tidak ada, sehingga log selalu menampilkan 'Warga' (fallback).
        $customer    = $deposit->_customer;
        $totalAmount = $deposit->_totalAmount;

        app(\App\Services\ActivityLogService::class)->logInfo(
            'deposit.create',
            "Mencatat setoran sampah sebesar Rp " . number_format($totalAmount, 0, ',', '.') . " untuk nasabah {$customer->name}.",
            [
                'deposit_id'       => $deposit->id,
                'waste_customer_id'=> $customer->id,
                'customer_name'    => $customer->name,   // ✅ BUG FIX #1: key yang hilang
                'total_amount'     => (float) $totalAmount,
                'details'          => $data['details'],
            ]
        );

        return $deposit;
    }

    public function recordWithdrawal(array $data): Withdrawal
    {
        // BUG FIX #4: Pisahkan activity logging dari DB::transaction (sama dengan recordDeposit).
        $result = DB::transaction(function () use ($data) {
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
                'member_id'        => $memberId,
                'waste_customer_id'=> $customer->id,
                'amount'           => $data['amount'],
                'date'             => $data['date'],
                'notes'            => $data['notes'] ?? null,
            ]);

            SavingsLedger::create([
                'member_id'        => $memberId,
                'waste_customer_id'=> $customer->id,
                'type'             => 'debit',
                'amount'           => $data['amount'],
                'description'      => $data['notes'] ?? 'Penarikan saldo',
                'reference_type'   => Withdrawal::class,
                'reference_id'     => $withdrawal->id,
            ]);

            return ['withdrawal' => $withdrawal, 'customer' => $customer];
        });

        $withdrawal = $result['withdrawal'];
        $customer   = $result['customer'];

        // BUG FIX #2 & #4: Activity log ditulis SETELAH transaction commit.
        // Tambahkan 'customer_name' ke payload — kunci ini dibaca oleh
        // ActivityLog::getHumanDescriptionAttribute() untuk menampilkan nama nasabah.
        // Sebelumnya key ini tidak ada, sehingga log selalu menampilkan 'Warga' (fallback).
        app(\App\Services\ActivityLogService::class)->logInfo(
            'withdrawal.create',
            "Mencatat penarikan saldo sebesar Rp " . number_format($data['amount'], 0, ',', '.') . " untuk nasabah {$customer->name}.",
            [
                'withdrawal_id'    => $withdrawal->id,
                'waste_customer_id'=> $customer->id,
                'customer_name'    => $customer->name,   // ✅ BUG FIX #2: key yang hilang
                'amount'           => (float) $data['amount'],
            ]
        );

        return $withdrawal;
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

    public function recordExpense(array $data, ?int $recordedBy = null): \App\Models\WasteBankExpense
    {
        return DB::transaction(function () use ($data, $recordedBy) {
            $amount = (float) $data['amount'];
            
            $currentBalance = $this->getWasteBankBalance();
            if ($amount > $currentBalance) {
                // If allow_negative_balance config is implemented later, it will be checked here.
                throw new \App\Exceptions\InsufficientBalanceException($currentBalance);
            }

            $expense = \App\Models\WasteBankExpense::create([
                'expense_code' => $data['expense_code'] ?? \App\Models\WasteBankExpense::generateExpenseCode(),
                'amount' => $amount,
                'description' => $data['description'],
                'expense_date' => $data['expense_date'],
                'recorded_by' => $recordedBy ?? auth()->id(),
                'proof_path' => $data['proof_path'] ?? null,
            ]);

            WasteBankCashLedger::create([
                'type' => 'out',
                'amount' => $amount,
                'balance' => $currentBalance - $amount,
                'reference_type' => \App\Models\WasteBankExpense::class,
                'reference_id' => $expense->id,
                'date' => $expense->expense_date,
                'description' => 'Pengeluaran operasional: ' . $expense->expense_code,
            ]);

            app(\App\Services\ActivityLogService::class)->logInfo(
                'waste_bank_expense.create',
                "Mencatat pengeluaran operasional bank sampah sebesar Rp " . number_format($amount, 0, ',', '.') . " [{$expense->expense_code}]",
                [
                    'expense_code' => $expense->expense_code,
                    'amount' => $amount,
                    'recorded_by' => $expense->recorded_by,
                    'balance_before' => $currentBalance,
                    'balance_after' => $currentBalance - $amount,
                ]
            );

            return $expense;
        });
    }
}
