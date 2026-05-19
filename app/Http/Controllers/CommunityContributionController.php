<?php

namespace App\Http\Controllers;

use App\Models\CommunityCashLedger;
use App\Models\CommunityContribution;
use App\Models\FundCategory;
use App\Models\Member;
use App\Services\CommunityCashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityContributionController extends Controller
{
    public function index()
    {
        $contributions = CommunityContribution::with(['fundCategory', 'recorder'])
            ->latest('date')->paginate(20);

        return view('community-cash.contributions.index', compact('contributions'));
    }

    public function create()
    {
        $categories = FundCategory::where('is_active', true)->get();
        $members = Member::orderBy('name')->get();

        return view('community-cash.contributions.create', compact('categories', 'members'));
    }

    public function store(Request $request, CommunityCashService $service)
    {
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

        $validated['recorded_by'] = auth()->id();

        if (!empty($validated['member_id']) && empty($validated['member_name'])) {
            $validated['member_name'] = Member::find($validated['member_id'])->name;
        }

        $service->recordContribution($validated);

        return redirect()->route('community-cash.contributions.index')
            ->with('success', 'Iuran berhasil dicatat.');
    }

    public function edit(CommunityContribution $contribution)
    {
        $categories = FundCategory::where('is_active', true)->get();
        $members = Member::orderBy('name')->get();

        return view('community-cash.contributions.edit', compact('contribution', 'categories', 'members'));
    }

    public function update(Request $request, CommunityContribution $contribution, CommunityCashService $service)
    {
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
}
