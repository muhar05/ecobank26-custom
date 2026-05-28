<?php

namespace App\Http\Controllers;

use App\Models\WasteCategory;
use Illuminate\Http\Request;

class WasteCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $group = $request->input('category_group');

        $query = WasteCategory::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($group) {
            if ($group === 'uncategorized') {
                $query->whereNull('category_group');
            } else {
                $query->where('category_group', $group);
            }
        }

        $categories = $query->latest()->paginate(20)->withQueryString();

        // Summary cards
        $totalCategories = WasteCategory::count();
        $totalPlastik = WasteCategory::where('category_group', 'Plastik')->count();
        $totalKertas = WasteCategory::where('category_group', 'Kertas')->count();
        $totalLogam = WasteCategory::where('category_group', 'Logam')->count();
        $totalOther = $totalCategories - ($totalPlastik + $totalKertas + $totalLogam);

        return view('bank-sampah.waste-categories.index', compact(
            'categories', 'search', 'group', 'totalCategories', 'totalPlastik', 'totalKertas', 'totalLogam', 'totalOther'
        ));
    }

    public function create()
    {
        return view('bank-sampah.waste-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'unit' => 'required|string|max:20',
            'category_group' => 'nullable|string|in:' . implode(',', WasteCategory::GROUPS),
            'code' => 'nullable|string|max:20|unique:waste_categories,code',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = WasteCategory::generateCode($validated['category_group'] ?? null);
        } else {
            $validated['code'] = strtoupper(trim($validated['code']));
        }

        WasteCategory::create($validated);

        return redirect()->route('bank-sampah.waste-categories.index')
            ->with('success', 'Kategori sampah berhasil ditambahkan.');
    }

    public function edit(WasteCategory $waste_category)
    {
        return view('bank-sampah.waste-categories.edit', ['category' => $waste_category]);
    }

    public function update(Request $request, WasteCategory $waste_category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'unit' => 'required|string|max:20',
            'category_group' => 'nullable|string|in:' . implode(',', WasteCategory::GROUPS),
            'code' => 'nullable|string|max:20|unique:waste_categories,code,' . $waste_category->id,
        ]);

        if (empty($validated['code'])) {
            // Only generate new code if group changed or it was previously null, else keep old or generate
            if (!$waste_category->code || $waste_category->category_group !== $validated['category_group']) {
                $validated['code'] = WasteCategory::generateCode($validated['category_group'] ?? null);
            } else {
                $validated['code'] = $waste_category->code;
            }
        } else {
            $validated['code'] = strtoupper(trim($validated['code']));
        }

        $waste_category->update($validated);

        return redirect()->route('bank-sampah.waste-categories.index')
            ->with('success', 'Kategori sampah berhasil diperbarui.');
    }

    public function destroy(WasteCategory $waste_category)
    {
        $waste_category->delete();
        return redirect()->route('bank-sampah.waste-categories.index')
            ->with('success', 'Kategori sampah berhasil dihapus.');
    }
}
