<?php

namespace App\Http\Controllers;

use App\Models\FundCategory;
use App\Services\RtScopeService;
use Illuminate\Http\Request;

class FundCategoryController extends Controller
{
    public function __construct(private RtScopeService $rtScope) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $status = $request->input('status');
        $progress = $request->input('progress');
        $sort = $request->input('sort', 'latest');

        $query = FundCategory::withSum('contributions', 'amount')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"))
            ->when($status === 'active', fn($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn($q) => $q->where('is_active', false));

        // RT Scoping: admin_rt hanya melihat global + milik RT-nya
        $query = $this->rtScope->applyFundCategoryScope($query, $user);

        $query = match ($sort) {
            'name' => $query->orderBy('name'),
            'target' => $query->orderByDesc('target_amount'),
            'collected' => $query->orderByDesc('contributions_sum_amount'),
            default => $query->latest(),
        };

        $categories = $query->paginate(20)->withQueryString()->fragment('table-section');

        // Stats — scope juga disesuaikan
        $statsQuery = FundCategory::withSum('contributions', 'amount');
        $statsQuery = $this->rtScope->applyFundCategoryScope($statsQuery, $user);
        $allCategories = $statsQuery->get();

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
        $user = auth()->user();
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

        // Auto-fill rt_id jika user adalah admin_rt
        if ($this->rtScope->isRtAdmin($user)) {
            $validated['rt_id'] = $user->rt_id;
        } else {
            // admin_rw / bendahara: kategori global (rt_id = null)
            $validated['rt_id'] = null;
        }

        FundCategory::create($validated);

        return redirect()->route('community-cash.categories.index')
            ->with('success', 'Kategori dana berhasil ditambahkan.');
    }

    public function edit(FundCategory $category)
    {
        // Pastikan admin_rt tidak bisa edit kategori RT lain
        $this->authorizeRtAccess($category);
        return view('community-cash.categories.edit', compact('category'));
    }

    public function update(Request $request, FundCategory $category)
    {
        $this->authorizeRtAccess($category);

        $user = auth()->user();
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
        $this->authorizeRtAccess($category);
        $category->delete();

        return redirect()->route('community-cash.categories.index')
            ->with('success', 'Kategori dana berhasil dihapus.');
    }

    /**
     * Cegah admin_rt mengakses/mengubah kategori milik RT lain atau kategori global RW.
     * Admin RW boleh edit semua.
     */
    private function authorizeRtAccess(FundCategory $category): void
    {
        $user = auth()->user();
        if (!$this->rtScope->isRtAdmin($user)) {
            return; // Global role → akses penuh
        }

        // Admin RT: hanya boleh edit kategori milik RT-nya sendiri (bukan NULL/global)
        if ($category->rt_id === null || $category->rt_id !== $user->rt_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah kategori ini.');
        }
    }
}
