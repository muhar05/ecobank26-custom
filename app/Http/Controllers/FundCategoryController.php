<?php

namespace App\Http\Controllers;

use App\Models\FundCategory;
use Illuminate\Http\Request;

class FundCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $progress = $request->input('progress');
        $sort = $request->input('sort', 'latest');

        $query = FundCategory::withSum('contributions', 'amount')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"))
            ->when($status === 'active', fn($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn($q) => $q->where('is_active', false));

        $query = match ($sort) {
            'name' => $query->orderBy('name'),
            'target' => $query->orderByDesc('target_amount'),
            'collected' => $query->orderByDesc('contributions_sum_amount'),
            default => $query->latest(),
        };

        $categories = $query->paginate(20)->withQueryString()->fragment('table-section');

        // Stats
        $allCategories = FundCategory::withSum('contributions', 'amount')->get();
        $stats = [
            'total' => $allCategories->count(),
            'total_target' => $allCategories->sum('target_amount'),
            'total_collected' => $allCategories->sum('contributions_sum_amount'),
            'avg_progress' => $allCategories->where('target_amount', '>', 0)->avg(fn($c) => min(round(($c->contributions_sum_amount ?? 0) / $c->target_amount * 100), 100)) ?? 0,
        ];

        return view('community-cash.categories.index', compact('categories', 'search', 'status', 'progress', 'sort', 'stats'));
    }

    public function create()
    {
        return view('community-cash.categories.create');
    }

    public function store(Request $request)
    {
        $isMandatoryInput = $request->input('is_mandatory');
        $isMandatory = ($isMandatoryInput == 1 || $isMandatoryInput === '1' || $isMandatoryInput === true || $isMandatoryInput === 'true');
        
        $request->merge([
            'is_active' => $request->boolean('is_active'),
            'is_mandatory' => $isMandatory ? 1 : 0,
            'monthly_amount' => $isMandatory ? $request->input('monthly_amount') : 0.00,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'target_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_mandatory' => 'integer|in:0,1',
            'monthly_amount' => 'required_if:is_mandatory,1|nullable|numeric|min:0',
        ], [
            'monthly_amount.required_if' => 'Nominal bulanan wajib diisi jika iuran diatur sebagai wajib.',
        ]);

        if ($validated['is_mandatory'] == 1 && (!isset($validated['monthly_amount']) || $validated['monthly_amount'] <= 0)) {
            return back()->withErrors(['monthly_amount' => 'Nominal iuran wajib harus lebih dari Rp 0.'])->withInput();
        }

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
        $isMandatoryInput = $request->input('is_mandatory');
        $isMandatory = ($isMandatoryInput == 1 || $isMandatoryInput === '1' || $isMandatoryInput === true || $isMandatoryInput === 'true');
        
        $request->merge([
            'is_active' => $request->boolean('is_active'),
            'is_mandatory' => $isMandatory ? 1 : 0,
            'monthly_amount' => $isMandatory ? $request->input('monthly_amount') : 0.00,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'target_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_mandatory' => 'integer|in:0,1',
            'monthly_amount' => 'required_if:is_mandatory,1|nullable|numeric|min:0',
        ], [
            'monthly_amount.required_if' => 'Nominal bulanan wajib diisi jika iuran diatur sebagai wajib.',
        ]);

        if ($validated['is_mandatory'] == 1 && (!isset($validated['monthly_amount']) || $validated['monthly_amount'] <= 0)) {
            return back()->withErrors(['monthly_amount' => 'Nominal iuran wajib harus lebih dari Rp 0.'])->withInput();
        }

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
