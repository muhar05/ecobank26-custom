<?php

namespace App\Http\Controllers;

use App\Models\WasteCustomer;
use Illuminate\Http\Request;

class BalanceCheckController extends Controller
{
    public function index()
    {
        return view('cek-saldo.index');
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            'customer_code' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        // Hanya nasabah yang kode dan nomor HP-nya cocok yang ditampilkan.
        // Tidak membocorkan apakah kode atau nomor HP yang salah.
        $customer = WasteCustomer::where('customer_code', $validated['customer_code'])
            ->where('phone', $validated['phone'])
            ->first();

        if (!$customer) {
            return back()->withErrors(['not_found' => 'Data nasabah tidak ditemukan.'])->withInput();
        }

        $credit = $customer->savingsLedgers()->where('type', 'credit')->sum('amount');
        $debit = $customer->savingsLedgers()->where('type', 'debit')->sum('amount');
        $balance = (float) ($credit - $debit);

        $totalSetoran = $customer->deposits()->sum('total_amount');
        $totalPenarikan = $customer->withdrawals()->sum('amount');
        $totalDeposits = $customer->deposits()->count();
        $totalWithdrawals = $customer->withdrawals()->count();
        $recentLedgers = $customer->savingsLedgers()->latest('id')->limit(10)->get();

        return view('cek-saldo.index', compact(
            'customer',
            'credit',
            'debit',
            'balance',
            'totalSetoran',
            'totalPenarikan',
            'totalDeposits',
            'totalWithdrawals',
            'recentLedgers'
        ));
    }
}
