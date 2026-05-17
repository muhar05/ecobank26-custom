<?php

namespace App\Http\Controllers;

use App\Models\SavingsLedger;

class WargaSavingsController extends Controller
{
    public function index()
    {
        $member = auth()->user()->member;

        if (!$member) {
            return view('warga.savings.index', ['member' => null]);
        }

        $credit = SavingsLedger::where('member_id', $member->id)->where('type', 'credit')->sum('amount');
        $debit = SavingsLedger::where('member_id', $member->id)->where('type', 'debit')->sum('amount');
        $recent = SavingsLedger::where('member_id', $member->id)->latest('id')->limit(5)->get();

        return view('warga.savings.index', [
            'member' => $member,
            'balance' => $credit - $debit,
            'totalCredit' => $credit,
            'totalDebit' => $debit,
            'recentLedgers' => $recent,
        ]);
    }

    public function history()
    {
        $member = auth()->user()->member;

        if (!$member) {
            return view('warga.savings.history', ['member' => null]);
        }

        $ledgers = SavingsLedger::where('member_id', $member->id)
            ->latest('id')->paginate(20);

        return view('warga.savings.history', [
            'member' => $member,
            'ledgers' => $ledgers,
        ]);
    }
}
