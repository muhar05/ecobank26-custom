<?php

namespace App\Http\Controllers;

use App\Models\CommunityCashLedger;
use App\Models\CommunityExpense;
use App\Models\FundCategory;
use App\Services\CommunityCashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityExpenseController extends Controller
{
    public function index()
    {
        $expenses = CommunityExpense::with(['fundCategory', 'recorder'])
            ->latest('date')->paginate(20);

        return view('community-cash.expenses.index', compact('expenses'));
    }

    public function create()
    {
        $categories = FundCategory::where('is_active', true)->get();
        return view('community-cash.expenses.create', compact('categories'));
    }

    public function store(Request $request, CommunityCashService $service)
    {
        $validated = $request->validate([
            'fund_category_id' => 'required|exists:fund_categories,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
        ]);

        $validated['recorded_by'] = auth()->id();

        try {
            $service->recordExpense($validated);
        } catch (\App\Exceptions\InsufficientBalanceException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()->route('community-cash.expenses.index')
            ->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function edit(CommunityExpense $expense)
    {
        $categories = FundCategory::where('is_active', true)->get();
        return view('community-cash.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, CommunityExpense $expense, CommunityCashService $service)
    {
        $validated = $request->validate([
            'fund_category_id' => 'required|exists:fund_categories,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
        ]);

        $oldCategoryId = $expense->fund_category_id;
        $newCategoryId = (int) $validated['fund_category_id'];

        DB::transaction(function () use ($expense, $validated, $oldCategoryId, $newCategoryId, $service) {
            $expense->update($validated);

            // Update related ledger entry
            CommunityCashLedger::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->update([
                    'fund_category_id' => $newCategoryId,
                    'amount' => $validated['amount'],
                    'date' => $validated['date'],
                    'description' => $validated['description'],
                ]);

            // Recalculate balances
            $service->recalculateBalancesForCategory($newCategoryId);
            if ($oldCategoryId !== $newCategoryId) {
                $service->recalculateBalancesForCategory($oldCategoryId);
            }
        });

        return redirect()->route('community-cash.expenses.index')
            ->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(CommunityExpense $expense, CommunityCashService $service)
    {
        $categoryId = $expense->fund_category_id;

        DB::transaction(function () use ($expense, $categoryId, $service) {
            CommunityCashLedger::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->delete();

            $expense->delete();

            $service->recalculateBalancesForCategory($categoryId);
        });

        return redirect()->route('community-cash.expenses.index')
            ->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
