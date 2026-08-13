<?php

namespace App\Http\Controllers;

use App\Models\SavingsLedger;
use Illuminate\Http\Request;

class WargaSavingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Fetch active WasteCustomer profiles linked to this user
        $customers = \App\Models\WasteCustomer::where('user_id', $user->id)
            ->where('status', 'active')->get();

        $customerIds = $customers->pluck('id');

        if ($customerIds->isEmpty()) {
            return view('warga.savings.index', ['hasAccount' => false]);
        }

        $credit = SavingsLedger::whereIn('waste_customer_id', $customerIds)
            ->where('type', 'credit')->sum('amount');

        $debit = SavingsLedger::whereIn('waste_customer_id', $customerIds)
            ->where('type', 'debit')->sum('amount');

        $recent = SavingsLedger::whereIn('waste_customer_id', $customerIds)
            ->latest('id')->limit(5)->get();

        $totalTransactions = SavingsLedger::whereIn('waste_customer_id', $customerIds)->count();

        return view('warga.savings.index', [
            'hasAccount' => true,
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

        $customerIds = \App\Models\WasteCustomer::where('user_id', $user->id)
            ->pluck('id');

        if ($customerIds->isEmpty()) {
            return view('warga.savings.history', ['hasAccount' => false]);
        }

        $query = SavingsLedger::whereIn('waste_customer_id', $customerIds);

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

        return view('warga.savings.history', [
            'hasAccount' => true,
            'ledgers' => $ledgers,
        ]);
    }
}
