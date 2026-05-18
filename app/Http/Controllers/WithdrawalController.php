<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Withdrawal;
use App\Services\BankSampahService;
use Illuminate\Http\Request;

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
        } catch (\App\Exceptions\InsufficientBalanceException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()->route('bank-sampah.withdrawals.index')
            ->with('success', 'Penarikan saldo berhasil dicatat.');
    }
}
