<?php

namespace App\Http\Controllers;

use App\Models\Collector;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\WasteBankCashLedger;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use App\Services\BankSampahService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function __construct(private BankSampahService $service) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $selectedDate = $request->input('date', date('Y-m-d'));

        // 1. Calculate remaining deposited waste on selected date ready to sell
        $deposited = \App\Models\DepositDetail::whereHas('deposit', fn ($q) => $q->whereDate('date', $selectedDate))
            ->select('waste_category_id', DB::raw('SUM(weight) as total_deposited'))
            ->groupBy('waste_category_id')
            ->pluck('total_deposited', 'waste_category_id');

        $sold = SaleDetail::whereHas('sale', fn ($q) => $q->whereDate('date', $selectedDate))
            ->select('waste_category_id', DB::raw('SUM(weight) as total_sold'))
            ->groupBy('waste_category_id')
            ->pluck('total_sold', 'waste_category_id');

        $readyToSell = collect();
        if ($deposited->isNotEmpty()) {
            $categories = WasteCategory::whereIn('id', $deposited->keys())->orderBy('name')->get();
            foreach ($categories as $cat) {
                $depWeight = (float) ($deposited[$cat->id] ?? 0);
                $soldWeight = (float) ($sold[$cat->id] ?? 0);
                $remaining = round($depWeight - $soldWeight, 2);

                if ($remaining > 0) {
                    $readyToSell->push([
                        'category' => $cat,
                        'total_deposited' => $depWeight,
                        'total_sold' => $soldWeight,
                        'remaining_weight' => $remaining,
                        'member_price' => (float) $cat->member_price,
                    ]);
                }
            }
        }

        // 2. Query sales history with collector and details
        $sales = Sale::with(['collector', 'details.wasteCategory'])
            ->when($search, fn($q) => $q->where('notes', 'like', "%{$search}%")
                ->orWhereHas('collector', fn($q2) => $q2->where('name', 'like', "%{$search}%")))
            ->latest()->paginate(15)->withQueryString();

        $collectors = Collector::orderBy('name')->get();
        $wastePrices = WastePrice::all()->groupBy('collector_id')
            ->map(fn ($items) => $items->keyBy('waste_category_id')->map(fn ($p) => [
                'collector_price' => (float) $p->collector_price,
            ]));

        return view('bank-sampah.sales.index', compact('sales', 'search', 'readyToSell', 'selectedDate', 'collectors', 'wastePrices'));
    }

    public function dailyRecap(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'collector_id' => 'required|exists:collectors,id',
            'exclude_sale_id' => 'nullable|integer',
        ]);

        $recap = $this->service->getDailyRemainingWaste(
            $request->input('date'),
            (int) $request->input('collector_id'),
            $request->filled('exclude_sale_id') ? (int) $request->input('exclude_sale_id') : null
        );

        return response()->json([
            'status' => 'success',
            'data' => $recap,
        ]);
    }

    public function create()
    {
        return view('bank-sampah.sales.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->validateSale($request);

        try {
            $sale = $this->service->recordSale($request->only('collector_id', 'date', 'notes', 'details'));
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['details' => $e->getMessage()]);
        }

        return redirect()->route('bank-sampah.sales.index')
            ->with('success', 'Penjualan berhasil dicatat.')
            ->with('print_sale_id', $sale->id);
    }

    public function show(Sale $sale)
    {
        $sale->load(['collector', 'details.wasteCategory']);

        return view('bank-sampah.sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $sale->load('details.wasteCategory');
        $data = $this->formData();
        $data['sale'] = $sale;
        return view('bank-sampah.sales.edit', $data);
    }

    public function update(Request $request, Sale $sale)
    {
        $this->validateSale($request);

        try {
            $this->service->recordSale($request->only('collector_id', 'date', 'notes', 'details'), $sale->id);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['details' => $e->getMessage()]);
        }

        return redirect()->route('bank-sampah.sales.index')
            ->with('success', 'Penjualan berhasil diperbarui.');
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            WasteBankCashLedger::where('reference_type', Sale::class)
                ->where('reference_id', $sale->id)->delete();
            $sale->details()->delete();
            $sale->delete();
            $this->service->recalculateWasteBankBalances();
        });

        return redirect()->route('bank-sampah.sales.index')
            ->with('success', 'Penjualan berhasil dihapus.');
    }

    private function validateSale(Request $request): void
    {
        $request->validate([
            'collector_id' => 'required|exists:collectors,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
            'details' => 'required|array',
            'details.*.waste_category_id' => 'required|exists:waste_categories,id',
            'details.*.weight' => 'required|numeric|min:0.01',
        ]);
    }

    private function formData(): array
    {
        return [
            'collectors' => Collector::orderBy('name')->get(),
            'wasteCategories' => WasteCategory::orderBy('name')->get(),
            'wastePrices' => WastePrice::all()->groupBy('collector_id')
                ->map(fn ($items) => $items->keyBy('waste_category_id')->map(fn ($p) => [
                    'collector_price' => (float) $p->collector_price,
                ])),
        ];
    }
}
