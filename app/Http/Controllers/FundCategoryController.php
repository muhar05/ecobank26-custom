<?php

namespace App\Http\Controllers;

use App\Models\FundCategory;
use Illuminate\Http\Request;

class FundCategoryController extends Controller
{
    public function index()
    {
        $categories = FundCategory::withSum('contributions', 'amount')->latest()->paginate(20);
        return view('community-cash.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('community-cash.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'target_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        FundCategory::create($validated);

        return redirect()->route('community-cash.categories.index')
            ->with('success', 'Kategori dana berhasil ditambahkan.');
    }

    public function edit(FundCategory $category)
    {
        return view('community-cash.categories.edit', compact('category'));
    }

    public function update(Request $request, FundCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'target_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        return redirect()->route('community-cash.categories.index')
            ->with('success', 'Kategori dana berhasil diperbarui.');
    }

    public function destroy(FundCategory $category)
    {
        $category->delete();

        return redirect()->route('community-cash.categories.index')
            ->with('success', 'Kategori dana berhasil dihapus.');
    }
}
