<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Member;
use App\Models\SavingsLedger;
use App\Models\WasteCustomer;
use App\Models\Withdrawal;
use App\Services\BankSampahService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $withdrawals = Withdrawal::with(['wasteCustomer', 'member'])
            ->when($search, fn($q) => $q->where('notes', 'like', "%{$search}%")
                ->orWhereHas('wasteCustomer', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                ->orWhereHas('member', fn($q2) => $q2->where('name', 'like', "%{$search}%")))
            ->latest('date')->paginate(20)->withQueryString();
        return view('bank-sampah.withdrawals.index', compact('withdrawals', 'search'));
    }

    public function create()
    {
        $customers = WasteCustomer::where('status', 'active')->orderBy('name')->get();
        return view('bank-sampah.withdrawals.create', compact('customers'));
    }

    public function store(Request $request, BankSampahService $service)
    {
        $request->validate([
            'waste_customer_id' => 'required|exists:waste_customers,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $service->recordWithdrawal([
                'waste_customer_id' => $request->waste_customer_id,
                'amount' => $request->amount,
                'date' => $request->date,
                'notes' => $request->notes,
            ]);
        } catch (\App\Exceptions\MinimumDepositException $e) {
            return back()->withErrors(['waste_customer_id' => $e->getMessage()])->withInput();
        } catch (\App\Exceptions\InsufficientBalanceException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()->route('bank-sampah.withdrawals.index')
            ->with('success', 'Penarikan saldo berhasil dicatat.');
    }

    public function edit(Withdrawal $withdrawal)
    {
        $customers = WasteCustomer::where('status', 'active')->orderBy('name')->get();
        return view('bank-sampah.withdrawals.edit', compact('withdrawal', 'customers'));
    }

    public function update(Request $request, Withdrawal $withdrawal, BankSampahService $service)
    {
        $request->validate([
            'waste_customer_id' => 'required|exists:waste_customers,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $customer = WasteCustomer::findOrFail($request->waste_customer_id);
        $memberId = $customer->member_id;

        // Check minimum deposit rule for the target customer
        $depositCount = Deposit::where(function($q) use ($customer) {
            $q->where('waste_customer_id', $customer->id);
            if ($customer->member_id) {
                $q->orWhere(fn($q2) => $q2->whereNull('waste_customer_id')->where('member_id', $customer->member_id));
            }
        })->count();

        if ($depositCount < BankSampahService::MIN_DEPOSITS_BEFORE_WITHDRAWAL) {
            return back()->withErrors(['waste_customer_id' => "Nasabah belum memenuhi syarat penarikan. Minimal 2 kali setoran sebelum bisa menarik saldo. Saat ini: {$depositCount} setoran."])->withInput();
        }

        // Available balance = current balance + this withdrawal's current amount (add it back)
        $currentBalance = $service->getCustomerBalance($customer->id);
        $addBack = ($request->waste_customer_id == $withdrawal->waste_customer_id) ? $withdrawal->amount : 0;
        $available = $currentBalance + $addBack;

        if ($request->amount > $available) {
            return back()->withErrors(['amount' => "Saldo tidak cukup. Saldo tersedia: Rp " . number_format($available, 0, ',', '.')])->withInput();
        }

        DB::transaction(function () use ($withdrawal, $request, $customer, $memberId) {
            $withdrawal->update([
                'member_id' => $memberId,
                'waste_customer_id' => $customer->id,
                'amount' => $request->amount,
                'date' => $request->date,
                'notes' => $request->notes,
            ]);

            SavingsLedger::where('reference_type', Withdrawal::class)
                ->where('reference_id', $withdrawal->id)
                ->update([
                    'member_id' => $memberId,
                    'waste_customer_id' => $customer->id,
                    'amount' => $request->amount,
                    'description' => $request->notes ?? 'Penarikan saldo',
                ]);
        });

        return redirect()->route('bank-sampah.withdrawals.index')
            ->with('success', 'Penarikan saldo berhasil diperbarui.');
    }

    public function destroy(Withdrawal $withdrawal)
    {
        DB::transaction(function () use ($withdrawal) {
            SavingsLedger::where('reference_type', Withdrawal::class)
                ->where('reference_id', $withdrawal->id)
                ->delete();

            $withdrawal->delete();
        });

        return redirect()->route('bank-sampah.withdrawals.index')
            ->with('success', 'Penarikan saldo berhasil dihapus.');
    }
}
