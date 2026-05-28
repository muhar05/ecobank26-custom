<?php

namespace App\Http\Controllers;

use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WasteCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $groupId = $request->input('waste_category_group_id');

        $query = WasteCategory::with('wasteCategoryGroup');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($groupId) {
            if ($groupId === 'uncategorized') {
                $query->whereNull('waste_category_group_id');
            } else {
                $query->where('waste_category_group_id', $groupId);
            }
        }

        $categories = $query->latest()->paginate(20)->withQueryString();

        // Dynamic group summaries
        $groups = WasteCategoryGroup::withCount('wasteCategories')->orderBy('name')->get();
        $totalCategories = WasteCategory::count();
        $uncategorizedCount = WasteCategory::whereNull('waste_category_group_id')->count();

        return view('bank-sampah.waste-categories.index', compact(
            'categories', 'search', 'groupId', 'groups', 'totalCategories', 'uncategorizedCount'
        ));
    }

    public function create()
    {
        $groups = WasteCategoryGroup::active()->orderBy('name')->get();
        return view('bank-sampah.waste-categories.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'unit' => 'required|string|max:20',
            'waste_category_group_id' => 'nullable|exists:waste_category_groups,id',
            'code' => 'nullable|string|max:20|unique:waste_categories,code',
        ]);

        $groupId = $validated['waste_category_group_id'] ?? null;
        $categoryGroupLegacy = $request->input('category_group');

        $group = null;
        if (!empty($groupId)) {
            $group = WasteCategoryGroup::find($groupId);
        } elseif (!empty($categoryGroupLegacy)) {
            $group = WasteCategoryGroup::where('name', $categoryGroupLegacy)
                ->orWhere('code', $categoryGroupLegacy)
                ->first();
        }

        if ($group) {
            $validated['waste_category_group_id'] = $group->id;
            $validated['category_group'] = $group->name;
        } else {
            $validated['waste_category_group_id'] = null;
            $validated['category_group'] = $categoryGroupLegacy ?: null;
        }

        DB::transaction(function() use (&$validated, $group) {
            if (empty($validated['code'])) {
                $attempts = 0;
                do {
                    $generatedCode = WasteCategory::generateCode($group ?: $validated['category_group']);
                    $exists = WasteCategory::where('code', $generatedCode)->exists();
                    $attempts++;
                    if ($attempts > 10) {
                        throw new \Exception("Gagal melakukan auto-generate kode kategori karena duplikasi.");
                    }
                } while ($exists);
                
                $validated['code'] = $generatedCode;
            } else {
                $validated['code'] = strtoupper(trim($validated['code']));
            }

            WasteCategory::create($validated);
        });

        return redirect()->route('bank-sampah.waste-categories.index')
            ->with('success', 'Kategori sampah berhasil ditambahkan.');
    }

    public function edit(WasteCategory $waste_category)
    {
        $groups = WasteCategoryGroup::active()->orderBy('name')->get();
        return view('bank-sampah.waste-categories.edit', ['category' => $waste_category, 'groups' => $groups]);
    }

    public function update(Request $request, WasteCategory $waste_category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'unit' => 'required|string|max:20',
            'waste_category_group_id' => 'nullable|exists:waste_category_groups,id',
            'code' => 'nullable|string|max:20|unique:waste_categories,code,' . $waste_category->id,
        ]);

        $groupId = $validated['waste_category_group_id'] ?? null;
        $categoryGroupLegacy = $request->input('category_group');

        $group = null;
        if (!empty($groupId)) {
            $group = WasteCategoryGroup::find($groupId);
        } elseif (!empty($categoryGroupLegacy)) {
            $group = WasteCategoryGroup::where('name', $categoryGroupLegacy)
                ->orWhere('code', $categoryGroupLegacy)
                ->first();
        }

        if ($group) {
            $validated['waste_category_group_id'] = $group->id;
            $validated['category_group'] = $group->name;
        } else {
            $validated['waste_category_group_id'] = null;
            $validated['category_group'] = $categoryGroupLegacy ?: null;
        }

        DB::transaction(function() use (&$validated, $waste_category, $group) {
            if (empty($validated['code'])) {
                if (!$waste_category->code || $waste_category->waste_category_group_id != $validated['waste_category_group_id']) {
                    $attempts = 0;
                    do {
                        $generatedCode = WasteCategory::generateCode($group ?: $validated['category_group']);
                        $exists = WasteCategory::where('code', $generatedCode)->where('id', '!=', $waste_category->id)->exists();
                        $attempts++;
                        if ($attempts > 10) {
                            throw new \Exception("Gagal melakukan auto-generate kode kategori karena duplikasi.");
                        }
                    } while ($exists);
                    $validated['code'] = $generatedCode;
                } else {
                    $validated['code'] = $waste_category->code;
                }
            } else {
                $validated['code'] = strtoupper(trim($validated['code']));
            }

            $waste_category->update($validated);
        });

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
