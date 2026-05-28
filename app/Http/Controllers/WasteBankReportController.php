<?php

namespace App\Http\Controllers;

use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use App\Models\WasteCustomer;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SavingsLedger;
use App\Models\WasteBankExpense;
use App\Models\WasteBankCashLedger;
use App\Services\ActivityLogService;
use App\Exports\WasteDepositReportExport;
use App\Exports\WasteSalesReportExport;
use App\Exports\WasteSavingsJournalReportExport;
use App\Exports\WasteCashflowReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class WasteBankReportController extends Controller
{
    // Constructor middleware removed, handled via route middleware instead.

    private function parseDateRange(Request $request)
    {
        $startDateRaw = $request->input('start_date');
        $endDateRaw = $request->input('end_date');

        if (empty($startDateRaw) || empty($endDateRaw)) {
            $startDate = now()->subDays(30)->startOfDay();
            $endDate = now()->endOfDay();
        } else {
            try {
                $startDate = Carbon::parse($startDateRaw)->startOfDay();
                $endDate = Carbon::parse($endDateRaw)->endOfDay();
            } catch (\Exception $e) {
                $startDate = now()->subDays(30)->startOfDay();
                $endDate = now()->endOfDay();
            }

            if ($startDate->diffInDays($endDate) > 366) {
                throw new \InvalidArgumentException("Periode laporan maksimal adalah 1 tahun (366 hari).");
            }
        }

        return [$startDate, $endDate];
    }

    /**
     * Scope 1: Laporan Setoran Sampah
     */
    public function deposits(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        $customerId = $request->input('waste_customer_id');
        $groupId = $request->input('waste_category_group_id');
        $categoryId = $request->input('waste_category_id');
        $collectorId = $request->input('collector_id');

        $query = DepositDetail::with(['deposit.wasteCustomer', 'deposit.member', 'deposit.collector', 'wasteCategory.wasteCategoryGroup'])
            ->whereHas('deposit', function($q) use ($startDate, $endDate, $customerId, $collectorId) {
                $q->whereBetween('date', [$startDate, $endDate])
                  ->when($customerId, fn($q2) => $q2->where('waste_customer_id', $customerId))
                  ->when($collectorId, fn($q2) => $q2->where('collector_id', $collectorId));
            })
            ->when($categoryId, fn($q) => $q->where('waste_category_id', $categoryId))
            ->when($groupId, function($q) use ($groupId) {
                $q->whereHas('wasteCategory', function($q2) use ($groupId) {
                    $q2->where('waste_category_group_id', $groupId);
                });
            });

        $details = $query->latest('id')->paginate(20)->withQueryString();

        // Calculate summaries on all matching rows (not paginated)
        $allMatching = $query->get();
        $totalTransactions = $allMatching->pluck('deposit_id')->unique()->count();
        $totalWeight = $allMatching->sum('weight');
        $totalAmount = $allMatching->sum('subtotal');
        $averageTransaction = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;

        $customers = WasteCustomer::orderBy('name')->get();
        $groups = WasteCategoryGroup::orderBy('name')->get();
        $categories = WasteCategory::orderBy('name')->get();
        $collectors = Collector::orderBy('name')->get();

        return view('bank-sampah.reports.deposits', compact(
            'details', 'startDate', 'endDate', 'customerId', 'groupId', 'categoryId', 'collectorId',
            'totalTransactions', 'totalWeight', 'totalAmount', 'averageTransaction',
            'customers', 'groups', 'categories', 'collectors'
        ));
    }

    public function depositsExcel(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        app(ActivityLogService::class)->logInfo('report.export', 'Export Excel Laporan Setoran Sampah', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ]);

        return Excel::download(new WasteDepositReportExport($request), 'laporan-setoran-sampah.xlsx');
    }

    public function depositsPrint(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        app(ActivityLogService::class)->logInfo('report.print', 'Print Laporan Setoran Sampah', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ]);

        $customerId = $request->input('waste_customer_id');
        $groupId = $request->input('waste_category_group_id');
        $categoryId = $request->input('waste_category_id');
        $collectorId = $request->input('collector_id');

        $query = DepositDetail::with(['deposit.wasteCustomer', 'deposit.member', 'deposit.collector', 'wasteCategory.wasteCategoryGroup'])
            ->whereHas('deposit', function($q) use ($startDate, $endDate, $customerId, $collectorId) {
                $q->whereBetween('date', [$startDate, $endDate])
                  ->when($customerId, fn($q2) => $q2->where('waste_customer_id', $customerId))
                  ->when($collectorId, fn($q2) => $q2->where('collector_id', $collectorId));
            })
            ->when($categoryId, fn($q) => $q->where('waste_category_id', $categoryId))
            ->when($groupId, function($q) use ($groupId) {
                $q->whereHas('wasteCategory', function($q2) use ($groupId) {
                    $q2->where('waste_category_group_id', $groupId);
                });
            });

        $details = $query->latest('id')->get();
        $totalTransactions = $details->pluck('deposit_id')->unique()->count();
        $totalWeight = $details->sum('weight');
        $totalAmount = $details->sum('subtotal');
        $averageTransaction = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;

        return view('bank-sampah.reports.deposits_print', compact(
            'details', 'startDate', 'endDate',
            'totalTransactions', 'totalWeight', 'totalAmount', 'averageTransaction'
        ));
    }

    /**
     * Scope 2: Laporan Penjualan ke Agregator
     */
    public function sales(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        $collectorId = $request->input('collector_id');
        $groupId = $request->input('waste_category_group_id');
        $categoryId = $request->input('waste_category_id');

        $query = SaleDetail::with(['sale.collector', 'wasteCategory.wasteCategoryGroup'])
            ->whereHas('sale', function($q) use ($startDate, $endDate, $collectorId) {
                $q->whereBetween('date', [$startDate, $endDate])
                  ->when($collectorId, fn($q2) => $q2->where('collector_id', $collectorId));
            })
            ->when($categoryId, fn($q) => $q->where('waste_category_id', $categoryId))
            ->when($groupId, function($q) use ($groupId) {
                $q->whereHas('wasteCategory', function($q2) use ($groupId) {
                    $q2->where('waste_category_group_id', $groupId);
                });
            });

        $details = $query->latest('id')->paginate(20)->withQueryString();

        // Calculate summaries
        $allMatching = $query->get();
        $totalSales = $allMatching->pluck('sale_id')->unique()->count();
        $totalWeight = $allMatching->sum('weight');
        $totalRevenue = $allMatching->sum('subtotal');

        // Agregator terbanyak
        $topAgregator = Sale::whereBetween('date', [$startDate, $endDate])
            ->select('collector_id', DB::raw('count(*) as transaction_count'))
            ->groupBy('collector_id')
            ->orderBy('transaction_count', 'desc')
            ->with('collector')
            ->first();
        
        $topAgregatorName = $topAgregator && $topAgregator->collector ? "{$topAgregator->collector->name} ({$topAgregator->transaction_count})" : '-';

        $collectors = Collector::orderBy('name')->get();
        $groups = WasteCategoryGroup::orderBy('name')->get();
        $categories = WasteCategory::orderBy('name')->get();

        return view('bank-sampah.reports.sales', compact(
            'details', 'startDate', 'endDate', 'collectorId', 'groupId', 'categoryId',
            'totalSales', 'totalWeight', 'totalRevenue', 'topAgregatorName',
            'collectors', 'groups', 'categories'
        ));
    }

    public function salesExcel(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        app(ActivityLogService::class)->logInfo('report.export', 'Export Excel Laporan Penjualan', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ]);

        return Excel::download(new WasteSalesReportExport($request), 'laporan-penjualan-agregator.xlsx');
    }

    public function salesPrint(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        app(ActivityLogService::class)->logInfo('report.print', 'Print Laporan Penjualan', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ]);

        $collectorId = $request->input('collector_id');
        $groupId = $request->input('waste_category_group_id');
        $categoryId = $request->input('waste_category_id');

        $query = SaleDetail::with(['sale.collector', 'wasteCategory.wasteCategoryGroup'])
            ->whereHas('sale', function($q) use ($startDate, $endDate, $collectorId) {
                $q->whereBetween('date', [$startDate, $endDate])
                  ->when($collectorId, fn($q2) => $q2->where('collector_id', $collectorId));
            })
            ->when($categoryId, fn($q) => $q->where('waste_category_id', $categoryId))
            ->when($groupId, function($q) use ($groupId) {
                $q->whereHas('wasteCategory', function($q2) use ($groupId) {
                    $q2->where('waste_category_group_id', $groupId);
                });
            });

        $details = $query->latest('id')->get();
        $totalSales = $details->pluck('sale_id')->unique()->count();
        $totalWeight = $details->sum('weight');
        $totalRevenue = $details->sum('subtotal');

        return view('bank-sampah.reports.sales_print', compact(
            'details', 'startDate', 'endDate',
            'totalSales', 'totalWeight', 'totalRevenue'
        ));
    }

    /**
     * Scope 3: Jurnal Tabungan Nasabah (Savings Journal)
     */
    public function savingsJournal(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        $customerId = $request->input('waste_customer_id');
        $type = $request->input('type'); // credit or debit

        $linkedMemberId = null;
        if ($customerId) {
            $customer = WasteCustomer::find($customerId);
            if ($customer) {
                $linkedMemberId = $customer->member_id;
            }
        }

        // We eagerly calculate running balances in PHP for safety and backward compatibility
        $query = SavingsLedger::with(['wasteCustomer', 'member'])
            ->when($customerId, function($q) use ($customerId, $linkedMemberId) {
                $q->where(function($sub) use ($customerId, $linkedMemberId) {
                    $sub->where('waste_customer_id', $customerId);
                    if ($linkedMemberId) {
                        $sub->orWhere(function($sub2) use ($linkedMemberId) {
                            $sub2->where('member_id', $linkedMemberId)
                                 ->whereNull('waste_customer_id');
                        });
                    }
                });
            })
            ->when($type, fn($q) => $q->where('type', $type))
            ->whereBetween('created_at', [$startDate, $endDate]);

        $ledgers = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate(25)->withQueryString();

        // Totals calculated on current filters
        $totalSetor = SavingsLedger::whereBetween('created_at', [$startDate, $endDate])
            ->when($customerId, function($q) use ($customerId, $linkedMemberId) {
                $q->where(function($sub) use ($customerId, $linkedMemberId) {
                    $sub->where('waste_customer_id', $customerId);
                    if ($linkedMemberId) {
                        $sub->orWhere(function($sub2) use ($linkedMemberId) {
                            $sub2->where('member_id', $linkedMemberId)
                                 ->whereNull('waste_customer_id');
                        });
                    }
                });
            })
            ->where('type', 'credit')
            ->sum('amount');

        $totalTarik = SavingsLedger::whereBetween('created_at', [$startDate, $endDate])
            ->when($customerId, function($q) use ($customerId, $linkedMemberId) {
                $q->where(function($sub) use ($customerId, $linkedMemberId) {
                    $sub->where('waste_customer_id', $customerId);
                    if ($linkedMemberId) {
                        $sub->orWhere(function($sub2) use ($linkedMemberId) {
                            $sub2->where('member_id', $linkedMemberId)
                                 ->whereNull('waste_customer_id');
                        });
                    }
                });
            })
            ->where('type', 'debit')
            ->sum('amount');

        $totalSaldo = 0;
        $pageOpeningBalance = 0;
        if ($customerId) {
            $totalSaldo = SavingsLedger::where(function($sub) use ($customerId, $linkedMemberId) {
                    $sub->where('waste_customer_id', $customerId);
                    if ($linkedMemberId) {
                        $sub->orWhere(function($sub2) use ($linkedMemberId) {
                            $sub2->where('member_id', $linkedMemberId)
                                 ->whereNull('waste_customer_id');
                        });
                    }
                })
                ->sum(DB::raw("case when type = 'credit' then amount else -amount end"));

            // Calculate running balance for the paginated collection
            // 1. Get all chronological data for this customer to map accurate balances
            $allChronological = SavingsLedger::where(function($sub) use ($customerId, $linkedMemberId) {
                    $sub->where('waste_customer_id', $customerId);
                    if ($linkedMemberId) {
                        $sub->orWhere(function($sub2) use ($linkedMemberId) {
                            $sub2->where('member_id', $linkedMemberId)
                                 ->whereNull('waste_customer_id');
                        });
                    }
                })
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $bal = 0;
            $balanceMap = [];
            foreach ($allChronological as $item) {
                if ($item->type === 'credit') {
                    $bal += (float) $item->amount;
                } else {
                    $bal -= (float) $item->amount;
                }
                $balanceMap[$item->id] = $bal;
            }

            foreach ($ledgers as $ledger) {
                $ledger->running_balance = $balanceMap[$ledger->id] ?? 0;
            }

            if ($ledgers->isNotEmpty()) {
                $earliestLedger = $ledgers->last();
                $earliestBalance = $balanceMap[$earliestLedger->id] ?? 0;
                if ($earliestLedger->type === 'credit') {
                    $pageOpeningBalance = $earliestBalance - (float) $earliestLedger->amount;
                } else {
                    $pageOpeningBalance = $earliestBalance + (float) $earliestLedger->amount;
                }
            }
        }

        $customers = WasteCustomer::orderBy('name')->get();

        return view('bank-sampah.reports.savings-journal', compact(
            'ledgers', 'startDate', 'endDate', 'customerId', 'type',
            'totalSetor', 'totalTarik', 'totalSaldo', 'pageOpeningBalance', 'customers'
        ));
    }

    public function savingsJournalExcel(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        app(ActivityLogService::class)->logInfo('report.export', 'Export Excel Jurnal Tabungan', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ]);

        return Excel::download(new WasteSavingsJournalReportExport($request), 'jurnal-tabungan-nasabah.xlsx');
    }

    public function savingsJournalPrint(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        app(ActivityLogService::class)->logInfo('report.print', 'Print Jurnal Tabungan', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ]);

        $customerId = $request->input('waste_customer_id');
        $type = $request->input('type');

        $linkedMemberId = null;
        if ($customerId) {
            $customer = WasteCustomer::find($customerId);
            if ($customer) {
                $linkedMemberId = $customer->member_id;
            }
        }

        $query = SavingsLedger::with(['wasteCustomer', 'member'])
            ->when($customerId, function($q) use ($customerId, $linkedMemberId) {
                $q->where(function($sub) use ($customerId, $linkedMemberId) {
                    $sub->where('waste_customer_id', $customerId);
                    if ($linkedMemberId) {
                        $sub->orWhere(function($sub2) use ($linkedMemberId) {
                            $sub2->where('member_id', $linkedMemberId)
                                 ->whereNull('waste_customer_id');
                        });
                    }
                });
            })
            ->when($type, fn($q) => $q->where('type', $type))
            ->whereBetween('created_at', [$startDate, $endDate]);

        $ledgers = $query->orderBy('created_at', 'asc')->orderBy('id', 'asc')->get();

        // Calculate running balance per row in chronological order
        $bal = 0;
        if ($customerId) {
            $startingBalance = SavingsLedger::where(function($sub) use ($customerId, $linkedMemberId) {
                    $sub->where('waste_customer_id', $customerId);
                    if ($linkedMemberId) {
                        $sub->orWhere(function($sub2) use ($linkedMemberId) {
                            $sub2->where('member_id', $linkedMemberId)
                                 ->whereNull('waste_customer_id');
                        });
                    }
                })
                ->where('created_at', '<', $startDate)
                ->sum(DB::raw("case when type = 'credit' then amount else -amount end"));
            $bal = $startingBalance;
        }

        foreach ($ledgers as $ledger) {
            if ($ledger->type === 'credit') {
                $bal += (float) $ledger->amount;
            } else {
                $bal -= (float) $ledger->amount;
            }
            $ledger->running_balance = $bal;
        }

        // Return latest first for visual printing
        $ledgers = $ledgers->reverse();

        $totalSetor = $ledgers->where('type', 'credit')->sum('amount');
        $totalTarik = $ledgers->where('type', 'debit')->sum('amount');

        return view('bank-sampah.reports.savings-journal_print', compact(
            'ledgers', 'startDate', 'endDate', 'totalSetor', 'totalTarik'
        ));
    }

    /**
     * Scope 4: Laporan Arus Kas Bank Sampah (Cashflow)
     */
    public function cashflow(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        // Cashflow is strictly Penjualan (Pemasukan) vs Operational Expenses (Pengeluaran)
        // Penjualan ke Agregator
        $totalPemasukan = Sale::whereBetween('date', [$startDate, $endDate])->sum('total_amount');
        // Pengeluaran Operasional
        $totalPengeluaran = WasteBankExpense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');

        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        // Monthly breakdown calculated in PHP
        $months = [];
        for ($m = Carbon::instance($startDate)->copy(); $m->lte($endDate); $m->addMonth()) {
            $key = $m->format('Y-m');
            $months[$key] = [
                'name' => $m->translatedFormat('F Y'),
                'pemasukan' => 0,
                'pengeluaran' => 0,
                'net' => 0
            ];
        }

        $salesGrouped = Sale::whereBetween('date', [$startDate, $endDate])->get();
        foreach ($salesGrouped as $sale) {
            $key = Carbon::instance($sale->date)->format('Y-m');
            if (isset($months[$key])) {
                $months[$key]['pemasukan'] += (float) $sale->total_amount;
            }
        }

        $expensesGrouped = WasteBankExpense::whereBetween('expense_date', [$startDate, $endDate])->get();
        foreach ($expensesGrouped as $expense) {
            $key = Carbon::instance($expense->expense_date)->format('Y-m');
            if (isset($months[$key])) {
                $months[$key]['pengeluaran'] += (float) $expense->amount;
            }
        }

        foreach ($months as &$m) {
            $m['net'] = $m['pemasukan'] - $m['pengeluaran'];
        }

        // Chronological cash ledger
        $salesList = Sale::with('collector')->whereBetween('date', [$startDate, $endDate])->get()->map(function($item) {
            return [
                'date' => $item->date,
                'type' => 'Pemasukan',
                'code' => 'SAL-' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
                'description' => 'Penjualan ke agregator ' . ($item->collector->name ?? '-'),
                'amount' => (float) $item->total_amount,
                'is_in' => true
            ];
        });

        $expensesList = WasteBankExpense::whereBetween('expense_date', [$startDate, $endDate])->get()->map(function($item) {
            return [
                'date' => $item->expense_date->toDateString(),
                'type' => 'Pengeluaran',
                'code' => $item->expense_code ?? 'EXP-' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
                'description' => $item->description,
                'amount' => (float) $item->amount,
                'is_in' => false
            ];
        });

        $cashbook = $salesList->concat($expensesList)->sortByDesc('date')->values();

        return view('bank-sampah.reports.cashflow', compact(
            'startDate', 'endDate', 'totalPemasukan', 'totalPengeluaran', 'saldoAkhir', 'months', 'cashbook'
        ));
    }

    public function cashflowExcel(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        app(ActivityLogService::class)->logInfo('report.export', 'Export Excel Laporan Arus Kas', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ]);

        return Excel::download(new WasteCashflowReportExport($request), 'laporan-arus-kas-bank-sampah.xlsx');
    }

    public function cashflowPrint(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->parseDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['date_range' => $e->getMessage()]);
        }

        app(ActivityLogService::class)->logInfo('report.print', 'Print Laporan Arus Kas', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ]);

        $totalPemasukan = Sale::whereBetween('date', [$startDate, $endDate])->sum('total_amount');
        $totalPengeluaran = WasteBankExpense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        $salesList = Sale::with('collector')->whereBetween('date', [$startDate, $endDate])->get()->map(function($item) {
            return [
                'date' => $item->date,
                'type' => 'Pemasukan',
                'code' => 'SAL-' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
                'description' => 'Penjualan ke agregator ' . ($item->collector->name ?? '-'),
                'amount' => (float) $item->total_amount,
                'is_in' => true
            ];
        });

        $expensesList = WasteBankExpense::whereBetween('expense_date', [$startDate, $endDate])->get()->map(function($item) {
            return [
                'date' => $item->expense_date->toDateString(),
                'type' => 'Pengeluaran',
                'code' => $item->expense_code ?? 'EXP-' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
                'description' => $item->description,
                'amount' => (float) $item->amount,
                'is_in' => false
            ];
        });

        $cashbook = $salesList->concat($expensesList)->sortByDesc('date')->values();

        return view('bank-sampah.reports.cashflow_print', compact(
            'startDate', 'endDate', 'totalPemasukan', 'totalPengeluaran', 'saldoAkhir', 'cashbook'
        ));
    }
}
