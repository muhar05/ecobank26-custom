<?php

namespace App\Http\Controllers;

use App\Models\WasteBankCashLedger;
use Illuminate\Http\Request;

class WasteBankCashReportController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteBankCashLedger::query();

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $ledgers = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        $totalIn = $ledgers->where('type', 'in')->sum('amount');
        $totalOut = $ledgers->where('type', 'out')->sum('amount');
        $balance = WasteBankCashLedger::latest('id')->value('balance') ?? 0;

        return view('bank-sampah.cash-report.index', compact('ledgers', 'totalIn', 'totalOut', 'balance'));
    }

    public function export(Request $request)
    {
        $query = WasteBankCashLedger::query();

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $ledgers = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
        $filename = 'kas-bank-sampah-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($ledgers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Keterangan', 'Masuk', 'Keluar', 'Saldo Berjalan']);
            foreach ($ledgers as $l) {
                fputcsv($handle, [
                    $l->date->format('Y-m-d'),
                    $l->description,
                    $l->type === 'in' ? $l->amount : 0,
                    $l->type === 'out' ? $l->amount : 0,
                    $l->balance,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
