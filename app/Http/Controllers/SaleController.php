<?php

namespace App\Http\Controllers;

use App\Models\Collector;
use App\Models\Sale;
use App\Models\WasteCategory;
use App\Services\BankSampahService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private BankSampahService $service) {}

    public function index()
    {
        $sales = Sale::with('collector')->latest()->paginate(20);

        return view('bank-sampah.sales.index', compact('sales'));
    }

    public function create()
    {
        $collectors = Collector::orderBy('name')->get();
        $wasteCategories = WasteCategory::orderBy('name')->get();
        $wastePrices = \App\Models\WastePrice::all()->groupBy('collector_id')
            ->map(fn ($items) => $items->keyBy('waste_category_id')->map(fn ($p) => [
                'member_price' => (float) $p->member_price,
                'collector_price' => (float) $p->collector_price,
            ]));

        return view('bank-sampah.sales.create', compact('collectors', 'wasteCategories', 'wastePrices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'collector_id' => 'required|exists:collectors,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
            'details' => 'required|array',
            'details.*.waste_category_id' => 'nullable|exists:waste_categories,id',
            'details.*.weight' => 'nullable|numeric|min:0',
            'details.*.price_per_unit' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->service->recordSale($validated);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['details' => $e->getMessage()]);
        }

        return redirect()->route('bank-sampah.sales.index')
            ->with('success', 'Penjualan berhasil dicatat.');
    }
}
