<?php

namespace App\Http\Controllers;

use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use App\Models\WasteCategoryGroup;
use Illuminate\Http\Request;

class WastePriceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $groupId = $request->input('waste_category_group_id');

        $pricesQuery = WastePrice::with(['wasteCategory.wasteCategoryGroup', 'collector'])
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
            'collector_id' => 'required|exists:collectors,id',
            'member_price' => 'required|numeric|min:0',
            'collector_price' => 'required|numeric|min:0|gte:member_price',
        ]);

        $exists = WastePrice::where('waste_category_id', $validated['waste_category_id'])
            ->where('collector_id', $validated['collector_id'])->exists();

        if ($exists) {
            return back()->withErrors(['waste_category_id' => 'Harga untuk kombinasi kategori dan pengepul ini sudah ada.'])->withInput();
        }

        $validated['price_per_unit'] = $validated['member_price'];
        WastePrice::create($validated);

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
            'collector_id' => 'required|exists:collectors,id',
            'member_price' => 'required|numeric|min:0',
            'collector_price' => 'required|numeric|min:0|gte:member_price',
        ]);

        $exists = WastePrice::where('waste_category_id', $validated['waste_category_id'])
            ->where('collector_id', $validated['collector_id'])
            ->where('id', '!=', $wastePrice->id)->exists();

        if ($exists) {
            return back()->withErrors(['waste_category_id' => 'Harga untuk kombinasi kategori dan pengepul ini sudah ada.'])->withInput();
        }

        $validated['price_per_unit'] = $validated['member_price'];
        $wastePrice->update($validated);

        return redirect()->route('bank-sampah.waste-prices.index')
            ->with('success', 'Harga sampah berhasil diperbarui.');
    }

    public function destroy(WastePrice $wastePrice)
    {
        $wastePrice->delete();
        return redirect()->route('bank-sampah.waste-prices.index')
            ->with('success', 'Harga sampah berhasil dihapus.');
    }
}
