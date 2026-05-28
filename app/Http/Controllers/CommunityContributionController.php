<?php

namespace App\Http\Controllers;

use App\Models\CommunityCashLedger;
use App\Models\CommunityContribution;
use App\Models\FundCategory;
use App\Models\Member;
use App\Services\CommunityCashService;
use App\Services\RtScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityContributionController extends Controller
{
    public function __construct(private RtScopeService $rtScope) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');

        $query = CommunityContribution::with(['fundCategory', 'recorder', 'rt'])
            ->when($search, fn($q) => $q->where('member_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('fundCategory', fn($q2) => $q2->where('name', 'like', "%{$search}%")));

        // RT Scoping: admin_rt hanya data rt_id = mereka (legacy NULL tidak tampil)
        $query = $this->rtScope->applyRtScope($query, $user);

        $contributions = $query->latest('date')->paginate(20)->withQueryString();

        return view('community-cash.contributions.index', compact('contributions', 'search'));
    }

    public function create()
    {
        $user = auth()->user();
        $rtId = $this->rtScope->getUserRtId($user);

        // Kategori yang visible: global + milik RT (untuk admin_rt), semua (untuk admin_rw)
        $categories = FundCategory::where('is_active', true)
            ->visibleToRt($rtId)
            ->get();

        $members = Member::orderBy('name')->get();

        return view('community-cash.contributions.create', compact('categories', 'members'));
    }

    public function store(Request $request, CommunityCashService $service)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'fund_category_id' => 'required|exists:fund_categories,id',
            'member_id' => 'nullable|exists:members,id',
            'member_name' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        if (empty($validated['member_id']) && empty($validated['member_name'])) {
            return back()->withErrors(['member_name' => 'Nama warga atau pilih member harus diisi.'])->withInput();
        }

        $validated['recorded_by'] = $user->id;

        if (!empty($validated['member_id']) && empty($validated['member_name'])) {
            $validated['member_name'] = Member::find($validated['member_id'])->name;
        }

        // Auto-fill rt_id: jika admin_rt, isi dari RT mereka
        $validated['rt_id'] = $this->rtScope->getUserRtId($user);

        // Validasi: pastikan fund_category visible ke RT ini (cegah URL tampering)
        $this->validateCategoryAccess($validated['fund_category_id'], $user);

        $service->recordContribution($validated);

        return redirect()->route('community-cash.contributions.index')
            ->with('success', 'Iuran berhasil dicatat.');
    }

    public function edit(CommunityContribution $contribution)
    {
        $user = auth()->user();
        $this->authorizeRtAccess($contribution, $user);

        $rtId = $this->rtScope->getUserRtId($user);
        $categories = FundCategory::where('is_active', true)->visibleToRt($rtId)->get();
        $members = Member::orderBy('name')->get();

        return view('community-cash.contributions.edit', compact('contribution', 'categories', 'members'));
    }

    public function update(Request $request, CommunityContribution $contribution, CommunityCashService $service)
    {
        $user = auth()->user();
        $this->authorizeRtAccess($contribution, $user);

        $validated = $request->validate([
            'fund_category_id' => 'required|exists:fund_categories,id',
            'member_id' => 'nullable|exists:members,id',
            'member_name' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        if (empty($validated['member_id']) && empty($validated['member_name'])) {
            return back()->withErrors(['member_name' => 'Nama warga atau pilih member harus diisi.'])->withInput();
        }

        if (!empty($validated['member_id']) && empty($validated['member_name'])) {
            $validated['member_name'] = Member::find($validated['member_id'])->name;
        }

        // Validasi category access (cegah tampering)
        $this->validateCategoryAccess($validated['fund_category_id'], $user);

        $oldCategoryId = $contribution->fund_category_id;
        $newCategoryId = (int) $validated['fund_category_id'];

        DB::transaction(function () use ($contribution, $validated, $oldCategoryId, $newCategoryId, $service) {
            $contribution->update($validated);

            CommunityCashLedger::where('reference_type', 'contribution')
                ->where('reference_id', $contribution->id)
                ->update([
                    'fund_category_id' => $newCategoryId,
                    'amount' => $validated['amount'],
                    'date' => $validated['date'],
                    'description' => $validated['description'] ?? 'Iuran: ' . $validated['member_name'],
                ]);

            $service->recalculateBalancesForCategory($newCategoryId);
            if ($oldCategoryId !== $newCategoryId) {
                $service->recalculateBalancesForCategory($oldCategoryId);
            }
        });

        return redirect()->route('community-cash.contributions.index')
            ->with('success', 'Iuran berhasil diperbarui.');
    }

    public function destroy(CommunityContribution $contribution, CommunityCashService $service)
    {
        $user = auth()->user();
        $this->authorizeRtAccess($contribution, $user);

        $categoryId = $contribution->fund_category_id;

        DB::transaction(function () use ($contribution, $categoryId, $service) {
            CommunityCashLedger::where('reference_type', 'contribution')
                ->where('reference_id', $contribution->id)
                ->delete();

            $contribution->delete();

            $service->recalculateBalancesForCategory($categoryId);
        });

        return redirect()->route('community-cash.contributions.index')
            ->with('success', 'Iuran berhasil dihapus.');
    }

    /**
     * Cegah admin_rt mengakses data contribution milik RT lain atau legacy (NULL).
     */
    private function authorizeRtAccess(CommunityContribution $contribution, $user): void
    {
        if ($this->rtScope->isGlobal($user)) {
            return;
        }

        if ($contribution->rt_id === null || $contribution->rt_id !== $user->rt_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses data ini.');
        }
    }

    /**
     * Validasi bahwa fund_category yang dipilih visible ke user ini (anti URL tampering).
     */
    private function validateCategoryAccess(int $categoryId, $user): void
    {
        if ($this->rtScope->isGlobal($user)) {
            return;
        }

        $rtId = $user->rt_id;
        $exists = FundCategory::where('id', $categoryId)
            ->visibleToRt($rtId)
            ->exists();

        if (!$exists) {
            abort(403, 'Kategori dana tidak dapat diakses oleh RT Anda.');
        }
    }
}
