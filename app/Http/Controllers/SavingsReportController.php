<?php

namespace App\Http\Controllers;

use App\Models\SavingsLedger;
use App\Models\WasteCustomer;
use Illuminate\Http\Request;

class SavingsReportController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $rtFilter = $request->input('rt');

        $query = WasteCustomer::query()
            ->withSum(['savingsLedgers as total_credit' => fn($q) => $q->where('type', 'credit')], 'amount')
            ->withSum(['savingsLedgers as total_debit' => fn($q) => $q->where('type', 'debit')], 'amount')
            ->with(['latestSavingsLedger.reference']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($rtFilter) {
            $query->where(function ($q) use ($rtFilter) {
                $q->where('address', 'like', "%{$rtFilter}%")
                  ->orWhere('name', 'like', "%{$rtFilter}%");
            });
        }

        // Available RT options for filter dropdown
        $allCustomers = WasteCustomer::get(['name', 'address']);
        $rtOptions = $allCustomers->map(fn($c) => $c->rt_number)
            ->filter(fn($rt) => $rt !== '-' && !empty($rt))
            ->unique()
            ->sort()
            ->values();

        $customers = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Calculate summary metrics across all customers
        $totalCustomers = WasteCustomer::count();
        $totalCreditAll = (float) SavingsLedger::where('type', 'credit')->sum('amount');
        $totalDebitAll = (float) SavingsLedger::where('type', 'debit')->sum('amount');
        $totalBalanceAll = $totalCreditAll - $totalDebitAll;

        return view('bank-sampah.savings.index', compact(
            'customers',
            'search',
            'rtFilter',
            'rtOptions',
            'totalCustomers',
            'totalCreditAll',
            'totalDebitAll',
            'totalBalanceAll'
        ));
    }

    public function show(Request $request, WasteCustomer $customer)
    {
        $typeFilter = $request->input('type');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Fetch all ledgers chronologically to compute running balance accurately
        $allLedgers = $customer->savingsLedgers()
            ->with(['reference'])
            ->orderBy('id', 'asc')
            ->get();

        $running = 0.0;
        $processedHistory = $allLedgers->map(function ($ledger) use (&$running) {
            $amount = (float) $ledger->amount;
            if ($ledger->type === 'credit') {
                $running += $amount;
            } else {
                $running -= $amount;
            }
            $ledger->running_balance = $running;

            // Resolve actual transaction date & notes from reference if available
            $ledger->transaction_date = $ledger->reference?->date
                ? \Carbon\Carbon::parse($ledger->reference->date)
                : $ledger->created_at;

            $ledger->transaction_notes = $ledger->reference?->notes ?: $ledger->description;

            return $ledger;
        });

        // Summary metrics
        $totalCredit = (float) $allLedgers->where('type', 'credit')->sum('amount');
        $totalDebit = (float) $allLedgers->where('type', 'debit')->sum('amount');
        $currentBalance = $totalCredit - $totalDebit;
        $totalTransactions = $allLedgers->count();

        // Reverse for display (latest first)
        $history = $processedHistory->reverse();

        if ($typeFilter) {
            $history = $history->where('type', $typeFilter);
        }

        if ($dateFrom) {
            $history = $history->filter(fn($l) => $l->transaction_date->format('Y-m-d') >= $dateFrom);
        }

        if ($dateTo) {
            $history = $history->filter(fn($l) => $l->transaction_date->format('Y-m-d') <= $dateTo);
        }

        return view('bank-sampah.savings.show', compact(
            'customer',
            'history',
            'currentBalance',
            'totalCredit',
            'totalDebit',
            'totalTransactions',
            'typeFilter',
            'dateFrom',
            'dateTo'
        ));
    }

    public function export()
    {
        $customers = WasteCustomer::withSum(['savingsLedgers as total_credit' => fn($q) => $q->where('type', 'credit')], 'amount')
            ->withSum(['savingsLedgers as total_debit' => fn($q) => $q->where('type', 'debit')], 'amount')
            ->orderBy('name')
            ->get();

        $filename = 'saldo-nasabah-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($customers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode Nasabah', 'Nama Nasabah', 'No. RT / Alamat', 'Total Setoran', 'Total Penarikan', 'Saldo Akhir']);

            foreach ($customers as $c) {
                $code = $c->customer_code ?? '-';
                $name = $c->name ?? '-';
                $rt = $c->rt_number;
                $credit = (float) ($c->total_credit ?? 0);
                $debit = (float) ($c->total_debit ?? 0);
                $balance = $credit - $debit;

                fputcsv($handle, [
                    $code,
                    $name,
                    $rt,
                    $credit,
                    $debit,
                    $balance,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
