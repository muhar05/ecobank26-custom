<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\FundCategory;
use App\Models\Rt;
use App\Services\BillService;
use App\Services\RtScopeService;
use Illuminate\Http\Request;

class BillController extends Controller
{
    protected $billService;
    protected $rtScope;

    public function __construct(BillService $billService, RtScopeService $rtScope)
    {
        $this->billService = $billService;
        $this->rtScope = $rtScope;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $monthFilter = $request->input('month');
        $yearFilter = $request->input('year');
        $rtFilter = $request->input('rt_id');
        $statusFilter = $request->input('status');
        $categoryFilter = $request->input('fund_category_id');

        // admin_rt: paksa rt filter ke RT mereka (cegah URL tampering)
        if ($this->rtScope->isRtAdmin($user)) {
            $rtFilter = $user->rt_id;
        }

        $query = Bill::with(['kk.rt', 'fundCategory'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('kk', function ($qk) use ($search) {
                    $qk->where('family_head', 'like', "%{$search}%")
                       ->orWhere('kk_number', 'like', "%{$search}%")
                       ->orWhere('address', 'like', "%{$search}%");
                })->orWhere('bill_code', 'like', "%{$search}%");
            })
            ->when($monthFilter, function ($q) use ($monthFilter) {
                $q->where('month', $monthFilter);
            })
            ->when($yearFilter, function ($q) use ($yearFilter) {
                $q->where('year', $yearFilter);
            })
            ->when($rtFilter, function ($q) use ($rtFilter) {
                $q->whereHas('kk', fn($qk) => $qk->where('rt_id', $rtFilter));
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->when($categoryFilter, function ($q) use ($categoryFilter) {
                $q->where('fund_category_id', $categoryFilter);
            });

        $bills = $query->latest()->paginate(20)->withQueryString();

        // Load helpers/dropdown lists
        $rts = $this->rtScope->isGlobal($user) ? Rt::orderBy('rt_number')->get() : collect();
        $categories = FundCategory::where('is_mandatory', true)->get();

        // Months array for select options
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Stats — scoped ke RT user jika admin_rt
        $statsQuery = Bill::query();
        if ($this->rtScope->isRtAdmin($user)) {
            $statsQuery = $this->rtScope->applyKkRtScope($statsQuery, $user);
        }
        $allBills = $statsQuery->get();
        $stats = [
            'total_bills' => $allBills->sum('amount'),
            'total_paid' => $allBills->where('status', 'paid')->sum('amount'),
            'total_unpaid' => $allBills->whereIn('status', ['unpaid', 'partially_paid'])->sum('amount'),
            'count_unpaid' => $allBills->whereIn('status', ['unpaid', 'partially_paid'])->count(),
        ];

        return view('bills.index', compact(
            'bills', 'rts', 'categories', 'months', 'search',
            'monthFilter', 'yearFilter', 'rtFilter', 'statusFilter', 'categoryFilter', 'stats'
        ));
    }

    public function create()
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $currentYear = date('Y');
        $years = [$currentYear - 1, $currentYear, $currentYear + 1];

        return view('bills.generate', compact('months', 'years'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|between:2020,2050',
        ], [
            'month.required' => 'Bulan wajib dipilih.',
            'year.required' => 'Tahun wajib dipilih.',
        ]);

        $generated = $this->billService->generateMonthlyBills(
            $validated['month'],
            $validated['year']
        );

        if ($generated > 0) {
            return redirect()->route('iuran.bills.index')
                ->with('success', "Berhasil men-generate tagihan iuran sebanyak {$generated} data.");
        }

        return redirect()->route('iuran.bills.index')
            ->with('info', 'Tidak ada data tagihan baru yang di-generate. Seluruh tagihan KK sudah lengkap untuk periode ini.');
    }

    public function pay(Request $request, $billId)
    {
        $bill = Bill::findOrFail($billId);
        
        $validated = $request->validate([
            'amount_paid' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($bill) {
                    if ($value > $bill->outstanding_balance) {
                        $fail("Nominal pembayaran melebihi sisa tagihan (Sisa: Rp " . number_format($bill->outstanding_balance, 0, ',', '.') . ").");
                    }
                }
            ],
            'payment_method' => 'required|string|in:cash,transfer,qris',
            'paid_at' => 'nullable|date',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $this->billService->payBill($bill->id, $validated);
            
            return redirect()->route('iuran.bills.index')
                ->with('success', 'Pembayaran tagihan berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->route('iuran.bills.index')
                ->with('error', $e->getMessage());
        }
    }

    public function arrears(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $monthFilter = $request->input('month');
        $yearFilter = $request->input('year');
        $rtFilter = $request->input('rt_id');
        $statusFilter = $request->input('status');
        $categoryFilter = $request->input('fund_category_id');
        $overdueOnly = $request->boolean('overdue');

        // admin_rt: paksa rt filter ke RT mereka (cegah URL tampering)
        if ($this->rtScope->isRtAdmin($user)) {
            $rtFilter = $user->rt_id;
        }

        $query = Bill::with(['kk.rt', 'fundCategory', 'payments'])
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('kk', function ($qk) use ($search) {
                    $qk->where('family_head', 'like', "%{$search}%")
                       ->orWhere('kk_number', 'like', "%{$search}%");
                })->orWhere('bill_code', 'like', "%{$search}%");
            })
            ->when($monthFilter, function ($q) use ($monthFilter) {
                $q->where('month', $monthFilter);
            })
            ->when($yearFilter, function ($q) use ($yearFilter) {
                $q->where('year', $yearFilter);
            })
            ->when($rtFilter, function ($q) use ($rtFilter) {
                $q->whereHas('kk', fn($qk) => $qk->where('rt_id', $rtFilter));
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->when($categoryFilter, function ($q) use ($categoryFilter) {
                $q->where('fund_category_id', $categoryFilter);
            })
            ->when($overdueOnly, function ($q) {
                $q->where('due_date', '<', now()->toDateString());
            });

        $bills = $query->latest()->paginate(20)->withQueryString();

        // Dropdown data
        $rts = $this->rtScope->isGlobal($user) ? Rt::orderBy('rt_number')->get() : collect();
        $categories = FundCategory::where('is_mandatory', true)->get();
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Summary cards — scoped
        $statsQuery = Bill::whereIn('status', ['unpaid', 'partially_paid'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('kk', function ($qk) use ($search) {
                    $qk->where('family_head', 'like', "%{$search}%")
                       ->orWhere('kk_number', 'like', "%{$search}%");
                })->orWhere('bill_code', 'like', "%{$search}%");
            })
            ->when($monthFilter, fn($q) => $q->where('month', $monthFilter))
            ->when($yearFilter, fn($q) => $q->where('year', $yearFilter))
            ->when($rtFilter, fn($q) => $q->whereHas('kk', fn($qk) => $qk->where('rt_id', $rtFilter)))
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->when($categoryFilter, fn($q) => $q->where('fund_category_id', $categoryFilter))
            ->when($overdueOnly, fn($q) => $q->where('due_date', '<', now()->toDateString()));

        $allArrears = $statsQuery->with('payments')->get();

        $totalTunggakan = $allArrears->sum(fn($b) => $b->outstanding_balance);
        $jumlahKkMenunggak = $allArrears->pluck('kk_id')->unique()->count();
        $totalUnpaid = $allArrears->where('status', 'unpaid')->sum('amount');
        $totalPartiallyPaid = $allArrears->where('status', 'partially_paid')->sum('amount');

        $stats = [
            'total_tunggakan' => $totalTunggakan,
            'jumlah_kk_menunggak' => $jumlahKkMenunggak,
            'total_unpaid' => $totalUnpaid,
            'total_partially_paid' => $totalPartiallyPaid,
        ];

        return view('bills.arrears', compact(
            'bills', 'rts', 'categories', 'months', 'search',
            'monthFilter', 'yearFilter', 'rtFilter', 'statusFilter', 'categoryFilter', 'overdueOnly', 'stats'
        ));
    }

    public function annualReport(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));

        // 1. Core ledger stats
        $totalIncome = (float) \App\Models\CommunityCashLedger::whereYear('date', $year)->where('type', 'in')->sum('amount');
        $totalExpense = (float) \App\Models\CommunityCashLedger::whereYear('date', $year)->where('type', 'out')->sum('amount');
        $finalBalance = (float) \App\Models\CommunityCashLedger::where('date', '<=', "{$year}-12-31")->orderByDesc('id')->value('balance') ?? 0.00;

        // 2. Billing stats
        $totalBillsAmount = (float) Bill::where('year', $year)->sum('amount');
        $totalBillPayments = (float) BillPayment::whereHas('bill', fn($qb) => $qb->where('year', $year))->sum('amount_paid');
        $totalArrearsAmount = max(0.00, $totalBillsAmount - $totalBillPayments);

        // 3. Summary per category
        $categoriesSummary = FundCategory::withSum(['ledgers as income' => function($q) use ($year) {
            $q->whereYear('date', $year)->where('type', 'in');
        }], 'amount')
        ->withSum(['ledgers as expense' => function($q) use ($year) {
            $q->whereYear('date', $year)->where('type', 'out');
        }], 'amount')
        ->get()
        ->map(function($cat) use ($year) {
            $cat->final_balance = (float) \App\Models\CommunityCashLedger::where('fund_category_id', $cat->id)
                ->where('date', '<=', "{$year}-12-31")
                ->orderByDesc('id')
                ->value('balance') ?? 0.00;
            return $cat;
        });

        // 4. Summary per RT
        $rtsSummary = Rt::withCount('kks')
            ->orderBy('rt_number')
            ->get()
            ->map(function($rt) use ($year) {
                $rtBills = Bill::where('year', $year)->whereHas('kk', fn($qk) => $qk->where('rt_id', $rt->id));
                $rt->bills_amount = (float) $rtBills->sum('amount');
                
                $rtPayments = BillPayment::whereHas('bill', fn($qb) => $qb->where('year', $year)->whereHas('kk', fn($qk) => $qk->where('rt_id', $rt->id)));
                $rt->payments_amount = (float) $rtPayments->sum('amount_paid');
                $rt->arrears_amount = max(0.00, $rt->bills_amount - $rt->payments_amount);
                return $rt;
            });

        // 5. Arrears at the end of the year
        $unpaidBills = Bill::with(['kk.rt', 'fundCategory'])
            ->where('year', $year)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->orderBy('due_date')
            ->get();

        $stats = compact(
            'year', 'totalIncome', 'totalExpense', 'finalBalance',
            'totalBillsAmount', 'totalBillPayments', 'totalArrearsAmount',
            'categoriesSummary', 'rtsSummary', 'unpaidBills'
        );

        if ($request->input('export') === 'print') {
            return view('bills.annual_report_print', $stats);
        }

        return view('bills.annual_report', $stats);
    }
}
