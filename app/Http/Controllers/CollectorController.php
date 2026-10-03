<?php

namespace App\Http\Controllers;

use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use App\Models\WastePriceHistory;
use App\Services\DeviceTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $collectors = Collector::withCount('wastePrices')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%"))
            ->latest()->paginate(20)->withQueryString();
        return view('bank-sampah.collectors.index', compact('collectors', 'search'));
    }

    public function create()
    {
        return view('bank-sampah.collectors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $collector = Collector::create($validated);

        return redirect()->route('bank-sampah.collectors.show', $collector)
            ->with('success', 'Data agregator berhasil ditambahkan. Silakan atur harga lapak untuk agregator ini.');
    }

    public function show(Collector $collector)
    {
        $collector->load([
            'wastePrices' => function ($q) {
                $q->with([
                    'wasteCategory.wasteCategoryGroup',
                    'histories' => fn ($hq) => $hq->latest('changed_at')->limit(5),
                ])->latest('updated_at');
            },
        ]);

        $categories = WasteCategory::with('wasteCategoryGroup')->orderBy('name')->get();

        return view('bank-sampah.collectors.show', compact('collector', 'categories'));
    }

    public function edit(Collector $collector)
    {
        return view('bank-sampah.collectors.edit', compact('collector'));
    }

    public function update(Request $request, Collector $collector)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $collector->update($validated);

        return redirect()->route('bank-sampah.collectors.index')
            ->with('success', 'Data pengepul berhasil diperbarui.');
    }

    public function destroy(Collector $collector)
    {
        $collector->delete();
        return redirect()->route('bank-sampah.collectors.index')
            ->with('success', 'Data pengepul berhasil dihapus.');
    }

    public function storePrice(Request $request, Collector $collector)
    {
        $validated = $request->validate([
            'waste_category_id' => 'required|exists:waste_categories,id',
            'collector_price'   => 'required|numeric|min:0',
        ]);

        $exists = WastePrice::where('collector_id', $collector->id)
            ->where('waste_category_id', $validated['waste_category_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['waste_category_id' => 'Harga untuk material ini sudah terdaftar pada agregator ini.'])->withInput();
        }

        $device = $request->attributes->get('admin_device') 
            ?? app(DeviceTrackingService::class)->getDeviceFromRequest($request, $request->user());

        $category = WasteCategory::findOrFail($validated['waste_category_id']);

        DB::transaction(function () use ($collector, $validated, $category, $device) {
            $price = WastePrice::create([
                'collector_id'      => $collector->id,
                'waste_category_id' => $validated['waste_category_id'],
                'collector_price'   => $validated['collector_price'],
            ]);

            WastePriceHistory::create([
                'waste_price_id'    => $price->id,
                'collector_id'      => $collector->id,
                'waste_category_id' => $price->waste_category_id,
                'old_price'         => null,
                'new_price'         => (float) $validated['collector_price'],
                'unit'              => $category->unit ?? 'kg',
                'changed_at'        => now(),
                'changed_by_user_id'=> auth()->id(),
                'admin_device_id'   => $device?->id,
                'source'            => WastePriceHistory::SOURCE_MANUAL,
            ]);
        });

        return back()->with('success', 'Harga lapak berhasil ditambahkan.');
    }

    public function updatePrice(Request $request, Collector $collector, WastePrice $wastePrice)
    {
        if ($wastePrice->collector_id !== $collector->id) {
            abort(404);
        }

        $validated = $request->validate([
            'collector_price' => 'required|numeric|min:0',
        ]);

        $oldPrice = (float) $wastePrice->collector_price;
        $newPrice = (float) $validated['collector_price'];

        $device = $request->attributes->get('admin_device') 
            ?? app(DeviceTrackingService::class)->getDeviceFromRequest($request, $request->user());

        DB::transaction(function () use ($wastePrice, $collector, $oldPrice, $newPrice, $device) {
            $wastePrice->update([
                'collector_price' => $newPrice,
            ]);

            // Create history record ONLY when price actually changes!
            if (abs($oldPrice - $newPrice) > 0.001) {
                $category = $wastePrice->wasteCategory ?? WasteCategory::find($wastePrice->waste_category_id);

                WastePriceHistory::create([
                    'waste_price_id'    => $wastePrice->id,
                    'collector_id'      => $collector->id,
                    'waste_category_id' => $wastePrice->waste_category_id,
                    'old_price'         => $oldPrice,
                    'new_price'         => $newPrice,
                    'unit'              => $category?->unit ?? 'kg',
                    'changed_at'        => now(),
                    'changed_by_user_id'=> auth()->id(),
                    'admin_device_id'   => $device?->id,
                    'source'            => WastePriceHistory::SOURCE_MANUAL,
                ]);
            }
        });

        return back()->with('success', 'Harga lapak berhasil diperbarui.');
    }

    public function destroyPrice(Collector $collector, WastePrice $wastePrice)
    {
        if ($wastePrice->collector_id !== $collector->id) {
            abort(404);
        }

        $wastePrice->delete();

        return back()->with('success', 'Harga lapak berhasil dihapus.');
    }
}
