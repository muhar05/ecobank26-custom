<?php

namespace App\Http\Controllers;

use App\Models\SavingsLedger;
use Illuminate\Http\Request;

class WargaSavingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $member = $user->member;

        // Fetch all active WasteCustomer profiles linked to this user's member or user directly
        $customers = \App\Models\WasteCustomer::where(function($q) use ($user, $member) {
            $q->where('user_id', $user->id);
            if ($member) {
                $q->orWhere('member_id', $member->id);
            }
        })->where('status', 'active')->get();

        $customerIds = $customers->pluck('id');

        // Check if there are active customers or if the member has legacy data to prevent empty state
        $hasLegacyData = false;
        if ($member) {
            $hasLegacyData = SavingsLedger::where('member_id', $member->id)->exists();
        }

        if ($customerIds->isEmpty() && !$hasLegacyData) {
            return view('warga.savings.index', ['member' => null]);
        }

        // Consolidated sums with fallback to legacy member records
        $credit = SavingsLedger::where(function($q) use ($customerIds, $member) {
            $q->whereIn('waste_customer_id', $customerIds);
            if ($member) {
                $q->orWhere(fn($q2) => $q2->whereNull('waste_customer_id')->where('member_id', $member->id));
            }
        })->where('type', 'credit')->sum('amount');

        $debit = SavingsLedger::where(function($q) use ($customerIds, $member) {
            $q->whereIn('waste_customer_id', $customerIds);
            if ($member) {
                $q->orWhere(fn($q2) => $q2->whereNull('waste_customer_id')->where('member_id', $member->id));
            }
        })->where('type', 'debit')->sum('amount');

        $recent = SavingsLedger::where(function($q) use ($customerIds, $member) {
            $q->whereIn('waste_customer_id', $customerIds);
            if ($member) {
                $q->orWhere(fn($q2) => $q2->whereNull('waste_customer_id')->where('member_id', $member->id));
            }
        })->latest('id')->limit(5)->get();

        $totalTransactions = SavingsLedger::where(function($q) use ($customerIds, $member) {
            $q->whereIn('waste_customer_id', $customerIds);
            if ($member) {
                $q->orWhere(fn($q2) => $q2->whereNull('waste_customer_id')->where('member_id', $member->id));
            }
        })->count();

        // Pass member as a dummy object so index blade continues to work, or pass $member if available
        $memberForBlade = $member ?: new \App\Models\Member(['name' => $user->name]);

        return view('warga.savings.index', [
            'member' => $memberForBlade,
            'balance' => $credit - $debit,
            'totalCredit' => $credit,
            'totalDebit' => $debit,
            'recentLedgers' => $recent,
            'totalTransactions' => $totalTransactions,
        ]);
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        $member = $user->member;

        $customerIds = \App\Models\WasteCustomer::where(function($q) use ($user, $member) {
            $q->where('user_id', $user->id);
            if ($member) {
                $q->orWhere('member_id', $member->id);
            }
        })->pluck('id');

        $hasLegacyData = false;
        if ($member) {
            $hasLegacyData = SavingsLedger::where('member_id', $member->id)->exists();
        }

        if ($customerIds->isEmpty() && !$hasLegacyData) {
            return view('warga.savings.history', ['member' => null]);
        }

        $query = SavingsLedger::where(function($q) use ($customerIds, $member) {
            $q->whereIn('waste_customer_id', $customerIds);
            if ($member) {
                $q->orWhere(fn($q2) => $q2->whereNull('waste_customer_id')->where('member_id', $member->id));
            }
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_type', 'like', "%{$search}%");
                
                if (is_numeric($search)) {
                    $q->orWhere('amount', $search);
                }
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_preset')) {
            $preset = $request->date_preset;
            if ($preset === 'today') {
                $query->whereDate('created_at', today());
            } elseif ($preset === 'last_week') {
                $query->whereDate('created_at', '>=', now()->subWeek());
            } elseif ($preset === 'last_month') {
                $query->whereDate('created_at', '>=', now()->subMonth());
            } elseif ($preset === 'custom') {
                if ($request->filled('start_date')) {
                    $query->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $query->whereDate('created_at', '<=', $request->end_date);
                }
            }
        }

        if ($request->filled('sort')) {
            $sort = $request->sort;
            if ($sort === 'oldest') {
                $query->oldest('id');
            } elseif ($sort === 'amount_desc') {
                $query->orderBy('amount', 'desc');
            } elseif ($sort === 'amount_asc') {
                $query->orderBy('amount', 'asc');
            } else {
                $query->latest('id');
            }
        } else {
            $query->latest('id');
        }

        $ledgers = $query->paginate(20)->withQueryString()->fragment('table-section');
        $memberForBlade = $member ?: new \App\Models\Member(['name' => $user->name]);

        return view('warga.savings.history', [
            'member' => $memberForBlade,
            'ledgers' => $ledgers,
        ]);
    }
}
