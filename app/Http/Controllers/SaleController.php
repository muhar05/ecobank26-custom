<?php

namespace App\Http\Controllers;

use App\Models\Collector;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\WasteBankCashLedger;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use App\Models\WastePriceHistory;
use App\Services\BankSampahService;
use App\Services\DeviceTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function __construct(private BankSampahService $service) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $selectedDate = $request->input('date', date('Y-m-d'));

        // 1. Calculate cumulative remaining stock (carry-over included) as of selected date
        $deposited = \App\Models\DepositDetail::whereHas('deposit', fn ($q) => $q->whereDate('date', '<=', $selectedDate))
            ->select('waste_category_id', DB::raw('SUM(weight) as total_deposited'))
            ->groupBy('waste_category_id')
            ->pluck('total_deposited', 'waste_category_id');

        $sold = SaleDetail::whereHas('sale', fn ($q) => $q->whereDate('date', '<=', $selectedDate))
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

        // 2. Query sales history with collector and lightweight details
        $sales = Sale::with(['collector', 'details:id,sale_id,weight,margin'])
            ->when($search, fn($q) => $q->where('notes', 'like', "%{$search}%")
                ->orWhereHas('collector', fn($q2) => $q2->where('name', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->fragment('table-section');

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
            $data = $request->only('collector_id', 'date', 'notes', 'details');
            $data['changed_by_user_id'] = auth()->id();
            $sale = $this->service->recordSale($data);
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
            $data = $request->only('collector_id', 'date', 'notes', 'details');
            $data['changed_by_user_id'] = auth()->id();
            $this->service->recordSale($data, $sale->id);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['details' => $e->getMessage()]);
        }

        return redirect()->route('bank-sampah.sales.index')
            ->with('success', 'Penjualan berhasil diperbarui.');
    }

    public function inlinePrice(Request $request)
    {
        $validated = $request->validate([
            'collector_id'      => 'required|exists:collectors,id',
            'waste_category_id' => 'required|exists:waste_categories,id',
            'collector_price'   => 'required|numeric|min:0',
        ]);

        $device = $request->attributes->get('admin_device')
            ?? app(DeviceTrackingService::class)->getDeviceFromRequest($request, $request->user());

        $category = WasteCategory::find($validated['waste_category_id']);
        $newPrice = (float) $validated['collector_price'];

        $price = DB::transaction(function () use ($validated, $newPrice, $device, $category) {
            $price = WastePrice::where('waste_category_id', $validated['waste_category_id'])
                ->where('collector_id', $validated['collector_id'])
                ->first();

            if ($price) {
                $oldPrice = (float) $price->collector_price;
                $price->update(['collector_price' => $newPrice]);

                if (abs($oldPrice - $newPrice) > 0.01) {
                    WastePriceHistory::create([
                        'waste_price_id'     => $price->id,
                        'collector_id'       => $validated['collector_id'],
                        'waste_category_id'  => $validated['waste_category_id'],
                        'old_price'          => $oldPrice,
                        'new_price'          => $newPrice,
                        'unit'               => $category?->unit ?? 'kg',
                        'changed_at'         => now(),
                        'changed_by_user_id' => auth()->id(),
                        'admin_device_id'    => $device?->id,
                        'source'             => WastePriceHistory::SOURCE_MANUAL,
                    ]);
                }
            } else {
                $price = WastePrice::create([
                    'waste_category_id' => $validated['waste_category_id'],
                    'collector_id'      => $validated['collector_id'],
                    'collector_price'   => $newPrice,
                ]);

                WastePriceHistory::create([
                    'waste_price_id'     => $price->id,
                    'collector_id'       => $validated['collector_id'],
                    'waste_category_id'  => $validated['waste_category_id'],
                    'old_price'          => null,
                    'new_price'          => $newPrice,
                    'unit'               => $category?->unit ?? 'kg',
                    'changed_at'         => now(),
                    'changed_by_user_id' => auth()->id(),
                    'admin_device_id'    => $device?->id,
                    'source'             => WastePriceHistory::SOURCE_MANUAL,
                ]);
            }

            return $price;
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'waste_category_id' => (int) $validated['waste_category_id'],
                'collector_price'   => (float) $price->collector_price,
            ],
        ]);
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
