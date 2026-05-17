<?php

namespace App\Http\Controllers;

use App\Models\WasteCategory;
use Illuminate\Http\Request;

class WasteCategoryController extends Controller
{
    public function index()
    {
        $categories = WasteCategory::latest()->get();
        return view('bank-sampah.waste-categories.index', compact('categories'));
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
        ]);

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
        ]);

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
