<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Member;
use App\Models\SavingsLedger;
use App\Models\Withdrawal;
use App\Services\BankSampahService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::with('member')->latest('date')->paginate(20);
        return view('bank-sampah.withdrawals.index', compact('withdrawals'));
    }

    public function create()
    {
        $members = Member::orderBy('name')->get();
        return view('bank-sampah.withdrawals.create', compact('members'));
    }

    public function store(Request $request, BankSampahService $service)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $service->recordWithdrawal($validated);
        } catch (\App\Exceptions\MinimumDepositException $e) {
            return back()->withErrors(['member_id' => $e->getMessage()])->withInput();
        } catch (\App\Exceptions\InsufficientBalanceException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()->route('bank-sampah.withdrawals.index')
            ->with('success', 'Penarikan saldo berhasil dicatat.');
    }

    public function edit(Withdrawal $withdrawal)
    {
        $members = Member::orderBy('name')->get();
        return view('bank-sampah.withdrawals.edit', compact('withdrawal', 'members'));
    }

    public function update(Request $request, Withdrawal $withdrawal, BankSampahService $service)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        // Check minimum deposit rule for the target member
        $depositCount = Deposit::where('member_id', $validated['member_id'])->count();
        if ($depositCount < BankSampahService::MIN_DEPOSITS_BEFORE_WITHDRAWAL) {
            return back()->withErrors(['member_id' => "Nasabah belum memenuhi syarat penarikan. Minimal 2 kali setoran sebelum bisa menarik saldo. Saat ini: {$depositCount} setoran."])->withInput();
        }

        // Available balance = current balance + this withdrawal's current amount (add it back)
        $currentBalance = $service->getMemberBalance($validated['member_id']);
        $addBack = ($validated['member_id'] == $withdrawal->member_id) ? $withdrawal->amount : 0;
        $available = $currentBalance + $addBack;

        if ($validated['amount'] > $available) {
            return back()->withErrors(['amount' => "Saldo tidak cukup. Saldo tersedia: Rp " . number_format($available, 0, ',', '.')])->withInput();
        }

        DB::transaction(function () use ($withdrawal, $validated) {
            $withdrawal->update($validated);

            SavingsLedger::where('reference_type', Withdrawal::class)
                ->where('reference_id', $withdrawal->id)
                ->update([
                    'member_id' => $validated['member_id'],
                    'amount' => $validated['amount'],
                    'description' => $validated['notes'] ?? 'Penarikan saldo',
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
