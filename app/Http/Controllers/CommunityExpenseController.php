<?php

namespace App\Http\Controllers;

use App\Models\CommunityExpense;
use App\Models\FundCategory;
use App\Services\CommunityCashService;
use Illuminate\Http\Request;

class CommunityExpenseController extends Controller
{
    public function index()
    {
        $expenses = CommunityExpense::with(['fundCategory', 'recorder'])
            ->latest('date')->get();

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
}
