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

            $totalAmount = collect($data['details'])->sum('subtotal');

            $deposit = Deposit::create([
                'waste_customer_id'=> $customer->id,
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

            $depositCount = Deposit::where('waste_customer_id', $customer->id)->count();

            if ($depositCount < self::MIN_DEPOSITS_BEFORE_WITHDRAWAL) {
                throw new \App\Exceptions\MinimumDepositException($depositCount, self::MIN_DEPOSITS_BEFORE_WITHDRAWAL);
            }

            $balance = $this->getCustomerBalance($customer->id);

            if ($data['amount'] > $balance) {
                throw new \App\Exceptions\InsufficientBalanceException($balance);
            }

            $withdrawal = Withdrawal::create([
                'waste_customer_id'=> $customer->id,
                'amount'           => $data['amount'],
                'date'             => $data['date'],
                'notes'            => $data['notes'] ?? null,
            ]);

            SavingsLedger::create([
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

        $credit = SavingsLedger::where('waste_customer_id', $customer->id)->where('type', 'credit')->sum('amount');

        $debit = SavingsLedger::where('waste_customer_id', $customer->id)->where('type', 'debit')->sum('amount');

        return (float) ($credit - $debit);
    }

    public function getAllBalances(): Collection
    {
        // ponytail: legacy method. Prefer SavingsReportController which uses waste_customer_id.
        return SavingsLedger::select('waste_customer_id')
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) - SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as balance")
            ->groupBy('waste_customer_id')
            ->with('wasteCustomer')
            ->get();
    }

    /**
     * Calculate remaining deposit weight for each category on a given date.
     * remaining_weight = total_deposited - total_sold
     * Only returns categories with remaining_weight > 0 that have a collector_price for the given collector.
     */
    public function getDailyRemainingWaste(string $date, int $collectorId, ?int $excludeSaleId = null): Collection
    {
        // 1. Total deposited on date per category
        $deposited = DepositDetail::whereHas('deposit', fn ($q) => $q->whereDate('date', $date))
            ->select('waste_category_id', DB::raw('SUM(weight) as total_deposited'))
            ->groupBy('waste_category_id')
            ->pluck('total_deposited', 'waste_category_id');

        if ($deposited->isEmpty()) {
            return collect();
        }

        // 2. Total sold on date per category (excluding $excludeSaleId if editing an existing sale)
        $sold = SaleDetail::whereHas('sale', function ($q) use ($date, $excludeSaleId) {
            $q->whereDate('date', $date);
            if ($excludeSaleId) {
                $q->where('id', '!=', $excludeSaleId);
            }
        })
        ->select('waste_category_id', DB::raw('SUM(weight) as total_sold'))
        ->groupBy('waste_category_id')
        ->pluck('total_sold', 'waste_category_id');

        // 3. Collector prices for this collector
        $collectorPrices = \App\Models\WastePrice::where('collector_id', $collectorId)
            ->whereIn('waste_category_id', $deposited->keys())
            ->pluck('collector_price', 'waste_category_id');

        // 4. Waste categories info
        $categories = \App\Models\WasteCategory::whereIn('id', $deposited->keys())->get()->keyBy('id');

        $result = collect();
        foreach ($deposited as $catId => $depWeight) {
            $soldWeight = (float) ($sold[$catId] ?? 0);
            $remaining = round((float) $depWeight - $soldWeight, 2);

            // Only offer if remaining > 0 and aggregator has a collector_price
            if ($remaining > 0 && isset($collectorPrices[$catId])) {
                $cat = $categories[$catId];
                $colPrice = (float) $collectorPrices[$catId];
                $memPrice = (float) $cat->member_price;

                $result->push([
                    'waste_category_id' => $cat->id,
                    'name' => $cat->name,
                    'code' => $cat->code,
                    'unit' => $cat->unit,
                    'member_price' => $memPrice,
                    'collector_price' => $colPrice,
                    'total_deposited' => (float) $depWeight,
                    'total_sold' => $soldWeight,
                    'remaining_weight' => $remaining,
                ]);
            }
        }

        return $result;
    }

    public function recordSale(array $data, ?int $saleId = null): Sale
    {
        return DB::transaction(function () use ($data, $saleId) {
            $rawDetails = collect($data['details'] ?? [])
                ->filter(fn ($d) => !empty($d['waste_category_id']) && !empty($d['weight']) && (float)$d['weight'] > 0);

            if ($rawDetails->isEmpty()) {
                throw new \InvalidArgumentException('Minimal satu detail penjualan harus diisi dengan berat lebih dari 0.');
            }

            $collectorId = (int) $data['collector_id'];
            $date = $data['date'];

            // 1. Fetch deposited weights on this date
            $deposited = DepositDetail::whereHas('deposit', fn ($q) => $q->whereDate('date', $date))
                ->select('waste_category_id', DB::raw('SUM(weight) as total_deposited'))
                ->groupBy('waste_category_id')
                ->pluck('total_deposited', 'waste_category_id');

            // 2. Fetch sold weights on this date (excluding this sale if updating)
            $sold = SaleDetail::whereHas('sale', function ($q) use ($date, $saleId) {
                $q->whereDate('date', $date);
                if ($saleId) {
                    $q->where('id', '!=', $saleId);
                }
            })
            ->select('waste_category_id', DB::raw('SUM(weight) as total_sold'))
            ->groupBy('waste_category_id')
            ->pluck('total_sold', 'waste_category_id');

            $collectorPrices = \App\Models\WastePrice::where('collector_id', $collectorId)
                ->pluck('collector_price', 'waste_category_id');

            $categories = \App\Models\WasteCategory::whereIn('id', $rawDetails->pluck('waste_category_id'))
                ->get()
                ->keyBy('id');

            $totalAmount = 0;
            $totalMargin = 0;
            $processedDetails = [];

            foreach ($rawDetails as $d) {
                $catId = (int) $d['waste_category_id'];
                $weight = (float) $d['weight'];

                $cat = $categories->get($catId);
                if (! $cat) {
                    throw new \InvalidArgumentException("Kategori sampah dengan ID {$catId} tidak ditemukan.");
                }

                // Check remaining weight
                $totalDep = (float) ($deposited[$catId] ?? 0);
                $totalSld = (float) ($sold[$catId] ?? 0);
                $remaining = round($totalDep - $totalSld, 2);

                if ($remaining <= 0) {
                    throw new \InvalidArgumentException("Kategori '{$cat->name}' tidak dapat dijual karena sisa setoran pada tanggal {$date} sudah habis (0 kg).");
                }

                if ($weight > $remaining) {
                    throw new \InvalidArgumentException("Berat penjualan untuk '{$cat->name}' ({$weight} kg) melebihi sisa setoran yang tersedia ({$remaining} kg) pada tanggal {$date}.");
                }

                // Check collector price
                if (! isset($collectorPrices[$catId])) {
                    throw new \InvalidArgumentException("Agregator belum memiliki harga penawaran untuk kategori '{$cat->name}'.");
                }

                $collectorPrice = (float) $collectorPrices[$catId];
                $memberPrice = (float) $cat->member_price;

                if ($collectorPrice < $memberPrice) {
                    throw new \InvalidArgumentException("Harga jual ke agregator (Rp " . number_format($collectorPrice, 0, ',', '.') . ") untuk '{$cat->name}' tidak boleh lebih rendah dari harga beli nasabah (Rp " . number_format($memberPrice, 0, ',', '.') . ").");
                }

                $subtotal = round($weight * $collectorPrice, 2);
                $margin = round(($collectorPrice - $memberPrice) * $weight, 2);

                $totalAmount += $subtotal;
                $totalMargin += $margin;

                $processedDetails[] = [
                    'waste_category_id' => $catId,
                    'weight' => $weight,
                    'price_per_unit' => $collectorPrice,
                    'member_price' => $memberPrice,
                    'subtotal' => $subtotal,
                    'margin' => $margin,
                ];
            }

            if ($saleId) {
                $sale = Sale::findOrFail($saleId);
                $sale->update([
                    'collector_id' => $collectorId,
                    'date' => $date,
                    'total_amount' => $totalAmount,
                    'notes' => $data['notes'] ?? null,
                ]);
                $sale->details()->delete();
            } else {
                $sale = Sale::create([
                    'collector_id' => $collectorId,
                    'date' => $date,
                    'total_amount' => $totalAmount,
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            foreach ($processedDetails as $pd) {
                SaleDetail::create(array_merge($pd, ['sale_id' => $sale->id]));
            }

            // Update cash ledger
            $ledger = WasteBankCashLedger::where('reference_type', Sale::class)
                ->where('reference_id', $sale->id)
                ->first();

            if ($totalMargin > 0) {
                $currentBalance = $this->getWasteBankBalance();
                if ($ledger) {
                    $ledger->update([
                        'amount' => $totalMargin,
                        'date' => $date,
                        'description' => 'Keuntungan penjualan sampah ke ' . $sale->collector->name,
                    ]);
                } else {
                    WasteBankCashLedger::create([
                        'type' => 'in',
                        'amount' => $totalMargin,
                        'balance' => $currentBalance + $totalMargin,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'date' => $date,
                        'description' => 'Keuntungan penjualan sampah ke ' . $sale->collector->name,
                    ]);
                }
            } elseif ($ledger) {
                $ledger->delete();
            }

            $this->recalculateWasteBankBalances();

            return $sale;
        });
    }

    public function recalculateWasteBankBalances(): void
    {
        $ledgers = WasteBankCashLedger::orderBy('date')->orderBy('id')->get();
        $balance = 0;
        foreach ($ledgers as $ledger) {
            $balance = $ledger->type === 'in' ? $balance + $ledger->amount : $balance - $ledger->amount;
            $ledger->update(['balance' => $balance]);
        }
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
