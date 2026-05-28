<?php

namespace App\Http\Controllers;

use App\Models\WasteCategoryGroup;
use Illuminate\Http\Request;

class WasteCategoryGroupController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $groups = WasteCategoryGroup::withCount('wasteCategories')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('bank-sampah.waste-category-groups.index', compact('groups', 'search'));
    }

    public function create()
    {
        return view('bank-sampah.waste-category-groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:waste_category_groups,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        WasteCategoryGroup::create($validated);

        return redirect()->route('bank-sampah.waste-category-groups.index')
            ->with('success', 'Grup kategori sampah berhasil ditambahkan.');
    }

    public function edit(WasteCategoryGroup $waste_category_group)
    {
        return view('bank-sampah.waste-category-groups.edit', ['group' => $waste_category_group]);
    }

    public function update(Request $request, WasteCategoryGroup $waste_category_group)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:waste_category_groups,code,' . $waste_category_group->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        $waste_category_group->update($validated);

        return redirect()->route('bank-sampah.waste-category-groups.index')
            ->with('success', 'Grup kategori sampah berhasil diperbarui.');
    }

    public function toggle(WasteCategoryGroup $waste_category_group)
    {
        $waste_category_group->update([
            'is_active' => !$waste_category_group->is_active
        ]);

        $status = $waste_category_group->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('bank-sampah.waste-category-groups.index')
            ->with('success', "Grup kategori sampah '{$waste_category_group->name}' berhasil {$status}.");
    }
}
