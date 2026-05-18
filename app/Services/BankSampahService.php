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
    public function recordDeposit(array $data): Deposit
    {
        return DB::transaction(function () use ($data) {
            $totalAmount = collect($data['details'])->sum('subtotal');

            $deposit = Deposit::create([
                'member_id' => $data['member_id'],
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
                'member_id' => $data['member_id'],
                'type' => 'credit',
                'amount' => $totalAmount,
                'description' => 'Setoran sampah',
                'reference_type' => Deposit::class,
                'reference_id' => $deposit->id,
            ]);

            return $deposit;
        });
    }

    public function recordWithdrawal(array $data): Withdrawal
    {
        return DB::transaction(function () use ($data) {
            $balance = $this->getMemberBalance($data['member_id']);

            if ($data['amount'] > $balance) {
                throw new \App\Exceptions\InsufficientBalanceException($balance);
            }

            $withdrawal = Withdrawal::create($data);

            SavingsLedger::create([
                'member_id' => $data['member_id'],
                'type' => 'debit',
                'amount' => $data['amount'],
                'description' => $data['notes'] ?? 'Penarikan saldo',
                'reference_type' => Withdrawal::class,
                'reference_id' => $withdrawal->id,
            ]);

            return $withdrawal;
        });
    }

    public function getMemberBalance(int $memberId): float
    {
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

            $details = $details->map(function ($d) {
                $d['subtotal'] = round((float) $d['weight'] * (float) $d['price_per_unit'], 2);
                return $d;
            });

            $totalAmount = $details->sum('subtotal');

            $sale = Sale::create([
                'collector_id' => $data['collector_id'],
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

            $currentBalance = $this->getWasteBankBalance();

            WasteBankCashLedger::create([
                'type' => 'in',
                'amount' => $totalAmount,
                'balance' => $currentBalance + $totalAmount,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'date' => $data['date'],
                'description' => 'Penjualan sampah ke ' . $sale->collector->name,
            ]);

            return $sale;
        });
    }

    public function getWasteBankBalance(): float
    {
        $latest = WasteBankCashLedger::latest('id')->first();

        return $latest ? (float) $latest->balance : 0;
    }
}
