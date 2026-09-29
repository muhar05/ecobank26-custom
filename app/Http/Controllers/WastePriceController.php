<?php

namespace App\Http\Controllers;

use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use App\Models\WastePriceHistory;
use App\Models\WasteCategoryGroup;
use App\Services\DeviceTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WastePriceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $groupId = $request->input('waste_category_group_id');

        $pricesQuery = WastePrice::with([
            'wasteCategory.wasteCategoryGroup', 
            'collector',
            'histories.adminDevice',
            'histories.changedBy',
        ])
        ->when($search, function($q) use ($search) {
            $q->where(function($q2) use ($search) {
                $q2->whereHas('wasteCategory', function($q3) use ($search) {
                    $q3->where('name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%");
                })->orWhereHas('collector', function($q3) use ($search) {
                    $q3->where('name', 'like', "%{$search}%");
                });
            });
        })
        ->when($groupId, function($q) use ($groupId) {
            if ($groupId === 'uncategorized') {
                $q->whereHas('wasteCategory', function($q2) {
                    $q2->whereNull('waste_category_group_id');
                });
            } else {
                $q->whereHas('wasteCategory', function($q2) use ($groupId) {
                    $q2->where('waste_category_group_id', $groupId);
                });
            }
        });

        $prices = $pricesQuery->latest()->paginate(20)->withQueryString();

        // Fetch dynamic groups with category counts and price counts
        $groups = WasteCategoryGroup::withCount(['wasteCategories', 'wasteCategories as active_prices_count' => function($q) {
            $q->whereHas('wastePrices');
        }])->orderBy('name')->get();

        $totalCategories = WasteCategory::count();
        $totalActivePrices = WastePrice::count();
        $uncategorizedCount = WasteCategory::whereNull('waste_category_group_id')->count();

        return view('bank-sampah.waste-prices.index', compact(
            'prices', 'search', 'groupId', 'groups', 'totalCategories', 'totalActivePrices', 'uncategorizedCount'
        ));
    }

    public function create()
    {
        $categories = WasteCategory::orderBy('name')->get();
        $collectors = Collector::orderBy('name')->get();
        return view('bank-sampah.waste-prices.create', compact('categories', 'collectors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'waste_category_id' => 'required|exists:waste_categories,id',
            'collector_id'      => 'required|exists:collectors,id',
            'collector_price'   => 'required|numeric|min:0',
        ]);

        $exists = WastePrice::where('waste_category_id', $validated['waste_category_id'])
            ->where('collector_id', $validated['collector_id'])->exists();

        if ($exists) {
            return back()->withErrors(['waste_category_id' => 'Harga untuk kombinasi kategori dan agregator ini sudah ada.'])->withInput();
        }

        $device = $request->attributes->get('admin_device') 
            ?? app(DeviceTrackingService::class)->getDeviceFromRequest($request, $request->user());

        DB::transaction(function () use ($validated, $device) {
            $price = WastePrice::create($validated);

            WastePriceHistory::create([
                'waste_price_id'    => $price->id,
                'collector_id'      => $validated['collector_id'],
                'waste_category_id' => $validated['waste_category_id'],
                'old_price'         => null,
                'new_price'         => (float) $validated['collector_price'],
                'changed_at'        => now(),
                'changed_by_user_id'=> auth()->id(),
                'admin_device_id'   => $device?->id,
                'source'            => WastePriceHistory::SOURCE_MANUAL,
            ]);
        });

        return redirect()->route('bank-sampah.waste-prices.index')
            ->with('success', 'Harga sampah berhasil ditambahkan.');
    }

    public function edit(WastePrice $wastePrice)
    {
        $categories = WasteCategory::orderBy('name')->get();
        $collectors = Collector::orderBy('name')->get();
        return view('bank-sampah.waste-prices.edit', compact('wastePrice', 'categories', 'collectors'));
    }

    public function update(Request $request, WastePrice $wastePrice)
    {
        $validated = $request->validate([
            'waste_category_id' => 'required|exists:waste_categories,id',
            'collector_id'      => 'required|exists:collectors,id',
            'collector_price'   => 'required|numeric|min:0',
        ]);

        $exists = WastePrice::where('waste_category_id', $validated['waste_category_id'])
            ->where('collector_id', $validated['collector_id'])
            ->where('id', '!=', $wastePrice->id)->exists();

        if ($exists) {
            return back()->withErrors(['waste_category_id' => 'Harga untuk kombinasi kategori dan agregator ini sudah ada.'])->withInput();
        }

        $device = $request->attributes->get('admin_device') 
            ?? app(DeviceTrackingService::class)->getDeviceFromRequest($request, $request->user());

        $oldPrice = (float) $wastePrice->collector_price;
        $newPrice = (float) $validated['collector_price'];

        DB::transaction(function () use ($wastePrice, $validated, $oldPrice, $newPrice, $device) {
            $wastePrice->update($validated);

            // Log history ONLY if price actually changed!
            if (abs($oldPrice - $newPrice) > 0.01) {
                WastePriceHistory::create([
                    'waste_price_id'    => $wastePrice->id,
                    'collector_id'      => $validated['collector_id'],
                    'waste_category_id' => $validated['waste_category_id'],
                    'old_price'         => $oldPrice,
                    'new_price'         => $newPrice,
                    'changed_at'        => now(),
                    'changed_by_user_id'=> auth()->id(),
                    'admin_device_id'   => $device?->id,
                    'source'            => WastePriceHistory::SOURCE_MANUAL,
                ]);
            }
        });

        return redirect()->route('bank-sampah.waste-prices.index')
            ->with('success', 'Harga sampah berhasil diperbarui.');
    }

    public function destroy(WastePrice $wastePrice)
    {
        $wastePrice->delete();
        return redirect()->route('bank-sampah.waste-prices.index')
            ->with('success', 'Harga sampah berhasil dihapus.');
    }

    public function history(Request $request, ?WastePrice $wastePrice = null)
    {
        $collectorId = $request->input('collector_id', $wastePrice?->collector_id);
        $wasteCategoryId = $request->input('waste_category_id', $wastePrice?->waste_category_id);
        $source = $request->input('source');

        $histories = WastePriceHistory::with([
            'collector',
            'wasteCategory.wasteCategoryGroup',
            'changedBy',
            'adminDevice',
        ])
        ->when($collectorId, fn($q) => $q->where('collector_id', $collectorId))
        ->when($wasteCategoryId, fn($q) => $q->where('waste_category_id', $wasteCategoryId))
        ->when($source, fn($q) => $q->where('source', $source))
        ->latest('changed_at')
        ->paginate(25)
        ->withQueryString();

        $collectors = Collector::orderBy('name')->get();
        $categories = WasteCategory::orderBy('name')->get();

        return view('bank-sampah.waste-prices.history', compact(
            'histories',
            'collectors',
            'categories',
            'collectorId',
            'wasteCategoryId',
            'source',
            'wastePrice'
        ));
    }
}
