<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\MinimumDepositException;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SavingsLedger;
use App\Models\WasteBankCashLedger;
use App\Models\WasteBankExpense;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Models\WastePrice;
use App\Models\WastePriceHistory;
use App\Models\Withdrawal;
use Carbon\Carbon;
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
            foreach ($data['details'] as $detail) {
                if (empty($detail['waste_category_id']) && empty($detail['material_label'])) {
                    throw new \InvalidArgumentException('Setiap detail setoran wajib memiliki waste_category_id (operasional) atau material_label (historical exception).');
                }
            }

            $customer = WasteCustomer::findOrFail($data['waste_customer_id']);

            $totalAmount = collect($data['details'])->sum('subtotal');

            $deposit = Deposit::create([
                'waste_customer_id' => $customer->id,
                'date' => $data['date'],
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['details'] as $detail) {
                DepositDetail::create([
                    'deposit_id' => $deposit->id,
                    'waste_category_id' => $detail['waste_category_id'] ?? null,
                    'material_label' => $detail['material_label'] ?? null,
                    'weight' => $detail['weight'],
                    'price_per_unit' => $detail['price_per_unit'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }

            SavingsLedger::create([
                'waste_customer_id' => $customer->id,
                'type' => 'credit',
                'amount' => $totalAmount,
                'description' => 'Setoran sampah',
                'reference_type' => Deposit::class,
                'reference_id' => $deposit->id,
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
        $customer = $deposit->_customer;
        $totalAmount = $deposit->_totalAmount;

        app(ActivityLogService::class)->logInfo(
            'deposit.create',
            'Mencatat setoran sampah sebesar Rp '.number_format($totalAmount, 0, ',', '.')." untuk nasabah {$customer->name}.",
            [
                'deposit_id' => $deposit->id,
                'waste_customer_id' => $customer->id,
                'customer_name' => $customer->name,   // ✅ BUG FIX #1: key yang hilang
                'total_amount' => (float) $totalAmount,
                'details' => $data['details'],
            ]
        );

        return $deposit;
    }

    public function recordWithdrawal(array $data): Withdrawal
    {
        // BUG FIX #4: Pisahkan activity logging dari DB::transaction (sama dengan recordDeposit).
        $result = DB::transaction(function () use ($data) {
            $customer = WasteCustomer::findOrFail($data['waste_customer_id']);

            $depositCount = Deposit::where('waste_customer_id', $customer->id)->count();

            if ($depositCount < self::MIN_DEPOSITS_BEFORE_WITHDRAWAL) {
                throw new MinimumDepositException($depositCount, self::MIN_DEPOSITS_BEFORE_WITHDRAWAL);
            }

            $balance = $this->getCustomerBalance($customer->id);

            if ($data['amount'] > $balance) {
                throw new InsufficientBalanceException($balance);
            }

            $withdrawal = Withdrawal::create([
                'waste_customer_id' => $customer->id,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'notes' => $data['notes'] ?? null,
            ]);

            SavingsLedger::create([
                'waste_customer_id' => $customer->id,
                'type' => 'debit',
                'amount' => $data['amount'],
                'description' => $data['notes'] ?? 'Penarikan saldo',
                'reference_type' => Withdrawal::class,
                'reference_id' => $withdrawal->id,
            ]);

            return ['withdrawal' => $withdrawal, 'customer' => $customer];
        });

        $withdrawal = $result['withdrawal'];
        $customer = $result['customer'];

        // BUG FIX #2 & #4: Activity log ditulis SETELAH transaction commit.
        // Tambahkan 'customer_name' ke payload — kunci ini dibaca oleh
        // ActivityLog::getHumanDescriptionAttribute() untuk menampilkan nama nasabah.
        // Sebelumnya key ini tidak ada, sehingga log selalu menampilkan 'Warga' (fallback).
        app(ActivityLogService::class)->logInfo(
            'withdrawal.create',
            'Mencatat penarikan saldo sebesar Rp '.number_format($data['amount'], 0, ',', '.')." untuk nasabah {$customer->name}.",
            [
                'withdrawal_id' => $withdrawal->id,
                'waste_customer_id' => $customer->id,
                'customer_name' => $customer->name,   // ✅ BUG FIX #2: key yang hilang
                'amount' => (float) $data['amount'],
            ]
        );

        return $withdrawal;
    }

    public function getCustomerBalance(int $customerId): float
    {
        $customer = WasteCustomer::findOrFail($customerId);

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
     * Calculate cumulative remaining stock for each category as of a given date.
     * remaining_weight(cat, asOf) = SUM deposit weight (deposit.date <= asOf)
     *                             - SUM sale weight    (sale.date <= asOf)
     * Carry-over from earlier dates is included. Includes categories with
     * remaining > 0 even without a collector_price (NULL).
     */
    public function getDailyRemainingWaste(string $date, int $collectorId, ?int $excludeSaleId = null): Collection
    {
        // 1. Cumulative deposited weight up to and including the date.
        //    Group also by material_label so historical exception rows
        //    (waste_category_id NULL) tetap terhitung & terpisah labelnya.
        $deposited = DepositDetail::whereHas('deposit', fn ($q) => $q->whereDate('date', '<=', $date))
            ->select('waste_category_id', 'material_label', DB::raw('SUM(weight) as total_deposited'))
            ->groupBy('waste_category_id', 'material_label')
            ->get();

        if ($deposited->isEmpty()) {
            return collect();
        }

        // 2. Cumulative sold weight up to and including the date, per category
        // (excluding $excludeSaleId if editing an existing sale)
        $sold = SaleDetail::whereHas('sale', function ($q) use ($date, $excludeSaleId) {
            $q->whereDate('date', '<=', $date);
            if ($excludeSaleId) {
                $q->where('id', '!=', $excludeSaleId);
            }
        })
            ->select('waste_category_id', DB::raw('SUM(weight) as total_sold'))
            ->groupBy('waste_category_id')
            ->pluck('total_sold', 'waste_category_id');

        $categoryIds = $deposited->pluck('waste_category_id')->filter()->unique()->values();

        // 3. Collector prices for this collector
        $collectorPrices = WastePrice::where('collector_id', $collectorId)
            ->whereIn('waste_category_id', $categoryIds)
            ->pluck('collector_price', 'waste_category_id');

        // 4. Waste categories info
        $categories = WasteCategory::whereIn('id', $categoryIds)->get()->keyBy('id');

        $result = collect();
        foreach ($deposited as $row) {
            $catId = $row->waste_category_id;
            $cat = $catId ? ($categories[$catId] ?? null) : null;

            // Kategori terhapus: lewati. Historical exception (catId null) tetap lanjut.
            if ($catId !== null && ! $cat) {
                continue;
            }

            $depWeight = (float) $row->total_deposited;
            $soldWeight = (float) ($sold->get($catId) ?? 0);
            $remaining = round($depWeight - $soldWeight, 2);

            // Show all categories with remaining > 0, even without a collector_price
            if ($remaining > 0) {
                $colPrice = ($catId !== null && isset($collectorPrices[$catId]))
                    ? (float) $collectorPrices[$catId]
                    : null;

                $result->push([
                    'waste_category_id' => $catId,
                    'material_label' => $row->material_label,
                    'name' => $cat?->name ?? $row->material_label,
                    'code' => $cat?->code ?? 'HIST',
                    'unit' => $cat?->unit ?? 'kg',
                    'member_price' => (float) ($cat?->member_price ?? 0),
                    'collector_price' => $colPrice,
                    'total_deposited' => $depWeight,
                    'total_sold' => $soldWeight,
                    'remaining_weight' => $remaining,
                    'is_historical_exception' => $catId === null,
                ]);
            }
        }

        return $result;
    }

    public function recordSale(array $data, ?int $saleId = null): Sale
    {
        return DB::transaction(function () use ($data, $saleId) {
            $rawDetails = collect($data['details'] ?? [])
                ->filter(fn ($d) => ! empty($d['waste_category_id']) && ! empty($d['weight']) && (float) $d['weight'] > 0);

            if ($rawDetails->isEmpty()) {
                throw new \InvalidArgumentException('Minimal satu detail penjualan harus diisi dengan berat lebih dari 0.');
            }

            // Aggregate payload based on waste_category_id (prevent duplicate category over-selling)
            $aggregatedWeights = $rawDetails->groupBy('waste_category_id')
                ->map(fn ($rows) => round((float) $rows->sum('weight'), 2));

            $collectorId = (int) $data['collector_id'];
            $date = $data['date'];
            $categoryIds = $aggregatedWeights->keys()->map(fn ($id) => (int) $id)->sort()->values();

            // Concurrency locking: lock the material stock rows deterministically
            DepositDetail::whereIn('waste_category_id', $categoryIds)->lockForUpdate()->get();
            WasteCategory::whereIn('id', $categoryIds)->orderBy('id')->lockForUpdate()->get();
            SaleDetail::whereIn('waste_category_id', $categoryIds)->lockForUpdate()->get();

            // 1. Cumulative deposited weight up to and including the sale date
            $deposited = DepositDetail::whereHas('deposit', fn ($q) => $q->whereDate('date', '<=', $date))
                ->whereIn('waste_category_id', $categoryIds)
                ->select('waste_category_id', DB::raw('SUM(weight) as total_deposited'))
                ->groupBy('waste_category_id')
                ->pluck('total_deposited', 'waste_category_id');

            // 1b. All-time deposited weight (guards against backdated over-selling)
            $depositedAll = DepositDetail::whereIn('waste_category_id', $categoryIds)
                ->select('waste_category_id', DB::raw('SUM(weight) as total_deposited'))
                ->groupBy('waste_category_id')
                ->pluck('total_deposited', 'waste_category_id');

            // 2. Cumulative sold weight up to and including the sale date (excluding this sale if updating)
            $sold = SaleDetail::whereHas('sale', function ($q) use ($date, $saleId) {
                $q->whereDate('date', '<=', $date);
                if ($saleId) {
                    $q->where('id', '!=', $saleId);
                }
            })
                ->whereIn('waste_category_id', $categoryIds)
                ->select('waste_category_id', DB::raw('SUM(weight) as total_sold'))
                ->groupBy('waste_category_id')
                ->pluck('total_sold', 'waste_category_id');

            // 2b. All-time sold weight (excluding this sale if updating)
            $soldAll = SaleDetail::whereIn('waste_category_id', $categoryIds)
                ->when($saleId, fn ($q) => $q->whereHas('sale', fn ($q2) => $q2->where('id', '!=', $saleId)))
                ->select('waste_category_id', DB::raw('SUM(weight) as total_sold'))
                ->groupBy('waste_category_id')
                ->pluck('total_sold', 'waste_category_id');

            $collectorPrices = WastePrice::where('collector_id', $collectorId)
                ->pluck('collector_price', 'waste_category_id');

            $categories = WasteCategory::whereIn('id', $categoryIds)
                ->get()
                ->keyBy('id');

            // If updating, load existing details to preserve historical price snapshots
            $existingDetailsMap = collect();
            $existingSale = null;
            if ($saleId) {
                $existingSale = Sale::with(['details', 'collector'])->findOrFail($saleId);
                $existingDetailsMap = $existingSale->details->keyBy('waste_category_id');
            }

            // 3. Validate aggregated weight per material against cumulative stock.
            // B(D) and B(infinity) checks give informative errors; the timeline scan
            // below guarantees no intermediate date dips below zero.
            $pendingStockChanges = [];
            $affectedCategoryIds = $categoryIds->map(fn ($id) => (int) $id)->all();

            if ($saleId && $existingSale) {
                // The stored sale is still in the DB; neutralise it so edits are not
                // counted twice (returns stock it currently withholds).
                $oldSaleDate = $existingSale->date->format('Y-m-d');
                foreach ($existingSale->details as $existingDetail) {
                    $pendingStockChanges[] = [
                        'waste_category_id' => (int) $existingDetail->waste_category_id,
                        'date' => $oldSaleDate,
                        'weight' => (float) $existingDetail->weight,
                        'direction' => 'in',
                    ];
                    $affectedCategoryIds[] = (int) $existingDetail->waste_category_id;
                }
            }

            foreach ($aggregatedWeights as $catId => $requestedWeight) {
                $cat = $categories->get($catId);
                if (! $cat) {
                    throw new \InvalidArgumentException("Kategori sampah dengan ID {$catId} tidak ditemukan.");
                }

                $remainingAsOf = round((float) ($deposited[$catId] ?? 0) - (float) ($sold[$catId] ?? 0), 2);
                $remainingAllTime = round((float) ($depositedAll[$catId] ?? 0) - (float) ($soldAll[$catId] ?? 0), 2);
                $remaining = min($remainingAsOf, $remainingAllTime);

                if ($remaining <= 0) {
                    throw new \InvalidArgumentException("Kategori '{$cat->name}' tidak dapat dijual karena sisa stok sampai tanggal {$date} sudah habis (0 kg).");
                }

                if ($requestedWeight > $remaining) {
                    throw new \InvalidArgumentException("Berat penjualan untuk '{$cat->name}' ({$requestedWeight} kg) melebihi sisa stok yang tersedia sampai tanggal {$date} ({$remaining} kg).");
                }

                $pendingStockChanges[] = [
                    'waste_category_id' => (int) $catId,
                    'date' => $date,
                    'weight' => (float) $requestedWeight,
                    'direction' => 'out',
                ];
            }

            // Full-timeline guard: reject any sale that would make cumulative stock
            // negative at any date point (e.g. backdated sales).
            $this->assertCumulativeStockNotNegative(
                array_values(array_unique($affectedCategoryIds)),
                $pendingStockChanges
            );

            $totalAmount = 0;
            $totalMargin = 0;
            $processedDetails = [];

            // Build detail lookup by category_id for custom_price / save_as_master_price
            $detailByCat = collect();
            foreach ($data['details'] as $idx => $d) {
                $cid = (int) $d['waste_category_id'];
                if ($cid > 0) {
                    $detailByCat[$cid] = $d;
                }
            }

            // 4. Calculate prices, subtotals, and margins (preserving snapshots on edit)
            foreach ($aggregatedWeights as $catId => $weight) {
                $cat = $categories->get($catId);

                $detailEntry = $detailByCat[$catId] ?? [];
                $customPrice = $detailEntry['custom_price'] ?? null;
                $hasCustom = $customPrice !== null && $customPrice !== '' && (float) $customPrice > 0;

                if ($saleId && isset($existingDetailsMap[$catId]) && ! $hasCustom) {
                    // PRESERVE historical snapshot from existing detail!
                    $collectorPrice = (float) $existingDetailsMap[$catId]->price_per_unit;
                    $memberPrice = (float) $existingDetailsMap[$catId]->member_price;
                } else {
                    // New item, or explicit price override from inline edit:
                    // custom_price > master price > reject
                    if ($hasCustom) {
                        $collectorPrice = (float) $customPrice;
                    } elseif (isset($collectorPrices[$catId])) {
                        $collectorPrice = (float) $collectorPrices[$catId];
                    } else {
                        throw new \InvalidArgumentException("Harga penjualan untuk '{$cat->name}' belum ditentukan.");
                    }
                    $memberPrice = (float) $cat->member_price;

                    if ($collectorPrice < $memberPrice) {
                        throw new \InvalidArgumentException('Harga jual ke agregator (Rp '.number_format($collectorPrice, 0, ',', '.').") untuk '{$cat->name}' tidak boleh lebih rendah dari harga beli nasabah (Rp ".number_format($memberPrice, 0, ',', '.').').');
                    }
                }

                // Integer rupiah rounding for consistent totals, subtotals, and ledger entries
                $subtotal = (float) round($weight * $collectorPrice, 0);
                $margin = (float) round(($collectorPrice - $memberPrice) * $weight, 0);

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
                $sale = $existingSale;
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

            // Save-as-master-price: create/update WastePrice for items with flag
            $changedByUserId = $data['changed_by_user_id'] ?? null;
            foreach ($aggregatedWeights as $catId => $weight) {
                $detailEntry = $detailByCat[$catId] ?? [];
                $saveAsMaster = $detailEntry['save_as_master_price'] ?? null;
                if ($saveAsMaster !== '1' && $saveAsMaster !== true) {
                    continue;
                }

                $cat = $categories->get((int) $catId);
                if (! $cat) {
                    continue;
                }

                $existingPrice = WastePrice::where('collector_id', $collectorId)
                    ->where('waste_category_id', (int) $catId)
                    ->first();

                $pdEntry = collect($processedDetails)->firstWhere('waste_category_id', (int) $catId);
                $newPrice = $pdEntry ? (float) $pdEntry['price_per_unit'] : 0;
                if ($newPrice <= 0) {
                    continue;
                }

                if ($existingPrice) {
                    $oldPrice = (float) $existingPrice->collector_price;
                    $existingPrice->update(['collector_price' => $newPrice]);
                    if (abs($oldPrice - $newPrice) > 0.001) {
                        WastePriceHistory::create([
                            'waste_price_id' => $existingPrice->id,
                            'collector_id' => $collectorId,
                            'waste_category_id' => (int) $catId,
                            'old_price' => $oldPrice,
                            'new_price' => $newPrice,
                            'unit' => $cat->unit,
                            'changed_by_user_id' => $changedByUserId,
                            'changed_at' => now(),
                            'source' => WastePriceHistory::SOURCE_MANUAL,
                        ]);
                    }
                } else {
                    $wp = WastePrice::create([
                        'waste_category_id' => (int) $catId,
                        'collector_id' => $collectorId,
                        'collector_price' => $newPrice,
                    ]);
                    WastePriceHistory::create([
                        'waste_price_id' => $wp->id,
                        'collector_id' => $collectorId,
                        'waste_category_id' => (int) $catId,
                        'old_price' => null,
                        'new_price' => $newPrice,
                        'unit' => $cat->unit,
                        'changed_by_user_id' => $changedByUserId,
                        'changed_at' => now(),
                        'source' => WastePriceHistory::SOURCE_MANUAL,
                    ]);
                }
            }

            // Update cash ledger
            $ledger = WasteBankCashLedger::where('reference_type', Sale::class)
                ->where('reference_id', $sale->id)
                ->first();

            $sale->loadMissing('collector');
            $collectorName = $sale->collector->name ?? 'Agregator';

            if ($totalMargin > 0) {
                $currentBalance = $this->getWasteBankBalance();
                if ($ledger) {
                    $ledger->update([
                        'amount' => $totalMargin,
                        'date' => $date,
                        'description' => 'Keuntungan penjualan sampah ke '.$collectorName,
                    ]);
                } else {
                    WasteBankCashLedger::create([
                        'type' => 'in',
                        'amount' => $totalMargin,
                        'balance' => $currentBalance + $totalMargin,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'date' => $date,
                        'description' => 'Keuntungan penjualan sampah ke '.$collectorName,
                    ]);
                }
            } elseif ($ledger) {
                $ledger->delete();
            }

            $this->recalculateWasteBankBalances();

            return $sale;
        });
    }

    /**
     * Ensure cumulative stock for the given categories never drops below zero
     * across the full timeline, including optional pending changes.
     *
     * @param  array<int>  $categoryIds
     * @param  array<int, array{waste_category_id:int|string,date:string,weight:float|int,direction:string}>  $pendingChanges
     */
    private function assertCumulativeStockNotNegative(array $categoryIds, array $pendingChanges = []): void
    {
        $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));
        if (empty($categoryIds)) {
            return;
        }

        $timeline = []; // 'Y-m-d' => [categoryId => delta]

        $depositDetails = DepositDetail::query()
            ->whereIn('waste_category_id', $categoryIds)
            ->with('deposit:id,date')
            ->get(['id', 'deposit_id', 'waste_category_id', 'weight']);

        foreach ($depositDetails as $detail) {
            if (! $detail->deposit) {
                continue;
            }
            $date = $detail->deposit->date->format('Y-m-d');
            $timeline[$date][$detail->waste_category_id] = ($timeline[$date][$detail->waste_category_id] ?? 0) + (float) $detail->weight;
        }

        $saleDetails = SaleDetail::query()
            ->whereIn('waste_category_id', $categoryIds)
            ->with('sale:id,date')
            ->get(['id', 'sale_id', 'waste_category_id', 'weight']);

        foreach ($saleDetails as $detail) {
            if (! $detail->sale) {
                continue;
            }
            $date = $detail->sale->date->format('Y-m-d');
            $timeline[$date][$detail->waste_category_id] = ($timeline[$date][$detail->waste_category_id] ?? 0) - (float) $detail->weight;
        }

        foreach ($pendingChanges as $change) {
            $catId = (int) $change['waste_category_id'];
            $date = Carbon::parse($change['date'])->format('Y-m-d');
            $delta = ($change['direction'] === 'out' ? -1 : 1) * (float) $change['weight'];
            $timeline[$date][$catId] = ($timeline[$date][$catId] ?? 0) + $delta;
        }

        ksort($timeline);

        $running = array_fill_keys($categoryIds, 0.0);
        foreach ($timeline as $date => $deltas) {
            foreach ($deltas as $catId => $delta) {
                $running[$catId] = round(($running[$catId] ?? 0) + $delta, 2);
            }
            foreach ($categoryIds as $catId) {
                if (($running[$catId] ?? 0) < -0.0001) {
                    $cat = WasteCategory::find($catId);
                    $name = $cat?->name ?? "Kategori #{$catId}";
                    throw new \InvalidArgumentException("Transaksi ini akan membuat stok '{$name}' negatif pada {$date} ({$running[$catId]} kg).");
                }
            }
        }
    }

    public function validateDepositCanBeDeleted(Deposit $deposit): void
    {
        $deposit->loadMissing('details.wasteCategory');

        $date = $deposit->date->format('Y-m-d');
        $categoryIds = $deposit->details->pluck('waste_category_id')->map(fn ($id) => (int) $id)->all();

        $pending = [];
        foreach ($deposit->details as $detail) {
            $pending[] = [
                'waste_category_id' => $detail->waste_category_id,
                'date' => $date,
                'weight' => (float) $detail->weight,
                'direction' => 'out',
            ];
        }

        $this->assertCumulativeStockNotNegative($categoryIds, $pending);
    }

    public function validateDepositCanBeUpdated(Deposit $deposit, array $newDetails, string $newDate): void
    {
        $deposit->loadMissing('details');

        $oldDate = $deposit->date->format('Y-m-d');
        $newDateFormatted = Carbon::parse($newDate)->format('Y-m-d');

        // Remove old contribution, add the new contribution at the new date.
        $pending = [];
        foreach ($deposit->details as $detail) {
            $pending[] = [
                'waste_category_id' => $detail->waste_category_id,
                'date' => $oldDate,
                'weight' => (float) $detail->weight,
                'direction' => 'out',
            ];
        }
        foreach ($newDetails as $detail) {
            $pending[] = [
                'waste_category_id' => (int) $detail['waste_category_id'],
                'date' => $newDateFormatted,
                'weight' => (float) $detail['weight'],
                'direction' => 'in',
            ];
        }

        $categoryIds = collect($pending)->pluck('waste_category_id')->map(fn ($id) => (int) $id)->all();

        $this->assertCumulativeStockNotNegative($categoryIds, $pending);
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

    public function recordExpense(array $data, ?int $recordedBy = null): WasteBankExpense
    {
        return DB::transaction(function () use ($data, $recordedBy) {
            $amount = (float) $data['amount'];

            $currentBalance = $this->getWasteBankBalance();
            if ($amount > $currentBalance) {
                // If allow_negative_balance config is implemented later, it will be checked here.
                throw new InsufficientBalanceException($currentBalance);
            }

            $expense = WasteBankExpense::create([
                'expense_code' => $data['expense_code'] ?? WasteBankExpense::generateExpenseCode(),
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
                'reference_type' => WasteBankExpense::class,
                'reference_id' => $expense->id,
                'date' => $expense->expense_date,
                'description' => 'Pengeluaran operasional: '.$expense->expense_code,
            ]);

            app(ActivityLogService::class)->logInfo(
                'waste_bank_expense.create',
                'Mencatat pengeluaran operasional bank sampah sebesar Rp '.number_format($amount, 0, ',', '.')." [{$expense->expense_code}]",
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
