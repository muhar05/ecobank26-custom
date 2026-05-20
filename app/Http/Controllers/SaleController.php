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
        $sales = Sale::with('collector')
            ->when($search, fn($q) => $q->where('notes', 'like', "%{$search}%")
                ->orWhereHas('collector', fn($q2) => $q2->where('name', 'like', "%{$search}%")))
            ->latest()->paginate(20)->withQueryString();
        return view('bank-sampah.sales.index', compact('sales', 'search'));
    }

    public function create()
    {
        return view('bank-sampah.sales.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->validateSale($request);

        try {
            $this->service->recordSale($request->only('collector_id', 'date', 'notes', 'details'));
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['details' => $e->getMessage()]);
        }

        return redirect()->route('bank-sampah.sales.index')
            ->with('success', 'Penjualan berhasil dicatat.');
    }

    public function edit(Sale $sale)
    {
        $sale->load('details');
        $data = $this->formData();
        $data['sale'] = $sale;
        return view('bank-sampah.sales.edit', $data);
    }

    public function update(Request $request, Sale $sale)
    {
        $this->validateSale($request);

        $details = collect($request->input('details', []))
            ->filter(fn ($d) => !empty($d['waste_category_id']) && !empty($d['weight']) && $d['weight'] > 0);

        if ($details->isEmpty()) {
            return back()->withInput()->withErrors(['details' => 'Minimal satu detail penjualan harus diisi.']);
        }

        $collectorId = $request->collector_id;
        $wastePrices = WastePrice::where('collector_id', $collectorId)
            ->pluck('member_price', 'waste_category_id');

        $totalMargin = 0;
        $details = $details->map(function ($d) use ($wastePrices, &$totalMargin) {
            $weight = (float) $d['weight'];
            $collectorPrice = (float) $d['price_per_unit'];
            $memberPrice = (float) ($wastePrices[$d['waste_category_id']] ?? 0);
            $subtotal = round($weight * $collectorPrice, 2);
            $margin = round(($collectorPrice - $memberPrice) * $weight, 2);
            $totalMargin += $margin;
            return [
                'waste_category_id' => $d['waste_category_id'],
                'weight' => $weight,
                'price_per_unit' => $collectorPrice,
                'subtotal' => $subtotal,
            ];
        });

        if ($totalMargin < 0) {
            return back()->withInput()->withErrors(['details' => 'Total margin negatif. Periksa harga pengepul dan harga nasabah.']);
        }

        $totalAmount = $details->sum('subtotal');

        DB::transaction(function () use ($sale, $request, $details, $totalAmount, $totalMargin, $collectorId) {
            $sale->update([
                'collector_id' => $collectorId,
                'date' => $request->date,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
            ]);

            $sale->details()->delete();
            foreach ($details as $detail) {
                SaleDetail::create(array_merge($detail, ['sale_id' => $sale->id]));
            }

            $collectorName = Collector::find($collectorId)->name;

            // Update or create/remove ledger entry
            $ledger = WasteBankCashLedger::where('reference_type', Sale::class)
                ->where('reference_id', $sale->id)->first();

            if ($totalMargin > 0) {
                if ($ledger) {
                    $ledger->update([
                        'amount' => $totalMargin,
                        'date' => $request->date,
                        'description' => 'Keuntungan penjualan sampah ke ' . $collectorName,
                    ]);
                } else {
                    WasteBankCashLedger::create([
                        'type' => 'in',
                        'amount' => $totalMargin,
                        'balance' => 0,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'date' => $request->date,
                        'description' => 'Keuntungan penjualan sampah ke ' . $collectorName,
                    ]);
                }
            } elseif ($ledger) {
                $ledger->delete();
            }

            $this->recalculateWasteBankBalances();
        });

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
            $this->recalculateWasteBankBalances();
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
            'details.*.waste_category_id' => 'nullable|exists:waste_categories,id',
            'details.*.weight' => 'nullable|numeric|min:0',
            'details.*.price_per_unit' => 'nullable|numeric|min:0',
        ]);
    }

    private function formData(): array
    {
        return [
            'collectors' => Collector::orderBy('name')->get(),
            'wasteCategories' => WasteCategory::orderBy('name')->get(),
            'wastePrices' => WastePrice::all()->groupBy('collector_id')
                ->map(fn ($items) => $items->keyBy('waste_category_id')->map(fn ($p) => [
                    'member_price' => (float) $p->member_price,
                    'collector_price' => (float) $p->collector_price,
                ])),
        ];
    }

    private function recalculateWasteBankBalances(): void
    {
        $ledgers = WasteBankCashLedger::orderBy('date')->orderBy('id')->get();
        $balance = 0;
        foreach ($ledgers as $ledger) {
            $balance = $ledger->type === 'in' ? $balance + $ledger->amount : $balance - $ledger->amount;
            $ledger->update(['balance' => $balance]);
        }
    }
}
