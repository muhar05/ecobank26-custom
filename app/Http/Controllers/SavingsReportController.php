<?php

namespace App\Http\Controllers;

use App\Models\SavingsLedger;

class SavingsReportController extends Controller
{
    public function index()
    {
        $balances = SavingsLedger::select('member_id')
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_credit")
            ->selectRaw("SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_debit")
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) - SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as balance")
            ->groupBy('member_id')
            ->with('member')
            ->orderByDesc('balance')
            ->get();

        return view('bank-sampah.savings.index', compact('balances'));
    }

    public function export()
    {
        $balances = SavingsLedger::select('member_id')
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_credit")
            ->selectRaw("SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_debit")
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) - SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as balance")
            ->groupBy('member_id')
            ->with('member')
            ->orderByDesc('balance')
            ->get();

        $filename = 'saldo-nasabah-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($balances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode Nasabah', 'Nama Nasabah', 'Total Setoran', 'Total Penarikan', 'Saldo Akhir']);

            foreach ($balances as $b) {
                fputcsv($handle, [
                    $b->member->member_code ?? '',
                    $b->member->name ?? '',
                    $b->total_credit,
                    $b->total_debit,
                    $b->balance,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
