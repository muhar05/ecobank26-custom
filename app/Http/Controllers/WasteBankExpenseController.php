<?php

namespace App\Http\Controllers;

use App\Models\WasteBankCashLedger;
use App\Models\WasteBankExpense;
use App\Services\BankSampahService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WasteBankExpenseController extends Controller
{
    public function index(Request $request, BankSampahService $service)
    {
        $query = WasteBankExpense::with('recordedBy')->latest('expense_date')->latest('id');

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        } else {
            // Default to current month
            $startOfMonth = now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = now()->endOfMonth()->format('Y-m-d');
            $query->whereBetween('expense_date', [$startOfMonth, $endOfMonth]);
        }

        $expenses = $query->paginate(20)->withQueryString();

        // Metrics
        $currentBalance = $service->getWasteBankBalance();
        
        $currentMonth = now()->format('Y-m');
        $totalIn = WasteBankCashLedger::where('type', 'in')
            ->where('date', 'like', "{$currentMonth}%")
            ->sum('amount');
            
        $totalOut = WasteBankCashLedger::where('type', 'out')
            ->where('date', 'like', "{$currentMonth}%")
            ->sum('amount');

        return view('bank-sampah.expenses.index', compact('expenses', 'currentBalance', 'totalIn', 'totalOut'));
    }

    public function create()
    {
        $nextCode = WasteBankExpense::generateExpenseCode();
        return view('bank-sampah.expenses.create', compact('nextCode'));
    }

    public function store(Request $request, BankSampahService $service)
    {
        $validated = $request->validate([
            'expense_code' => 'required|string|unique:waste_bank_expenses,expense_code',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date|before_or_equal:today',
            'description' => 'required|string|max:500',
            'proof' => 'required_without:proof_explanation|nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'proof_explanation' => 'required_without:proof|nullable|string|max:255',
        ], [
            'proof.required_without' => 'Bukti nota wajib diunggah. Jika tidak ada, wajib mengisi keterangan nota hilang.',
            'proof_explanation.required_without' => 'Keterangan nota hilang wajib diisi jika bukti nota tidak diunggah.',
        ]);

        try {
            if ($request->hasFile('proof')) {
                $path = $request->file('proof')->store('expenses', 'public');
                $validated['proof_path'] = $path;
            }

            if ($request->filled('proof_explanation')) {
                $validated['description'] .= "\n\n(Catatan Nota Hilang: " . $request->input('proof_explanation') . ")";
            }

            $service->recordExpense($validated);

            return redirect()->route('bank-sampah.expenses.index')
                ->with('success', 'Pengeluaran operasional berhasil dicatat.');
        } catch (\App\Exceptions\InsufficientBalanceException $e) {
            return back()->withInput()->with('error', 'Saldo kas Bank Sampah tidak mencukupi untuk pengeluaran ini.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(WasteBankExpense $expense)
    {
        $expense->load('recordedBy', 'ledger');
        return view('bank-sampah.expenses.show', compact('expense'));
    }
}
