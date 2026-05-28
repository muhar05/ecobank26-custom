<?php

namespace App\Http\Controllers;

use App\Models\SavingsLedger;

class SavingsReportController extends Controller
{
    public function index()
    {
        $balances = SavingsLedger::select('waste_customer_id', 'member_id')
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_credit")
            ->selectRaw("SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_debit")
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) - SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as balance")
            ->groupBy('waste_customer_id', 'member_id')
            ->with(['wasteCustomer', 'member'])
            ->orderByDesc('balance')
            ->paginate(15)->withQueryString()->fragment('table-section');

        return view('bank-sampah.savings.index', compact('balances'));
    }

    public function export()
    {
        $balances = SavingsLedger::select('waste_customer_id', 'member_id')
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_credit")
            ->selectRaw("SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_debit")
            ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) - SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as balance")
            ->groupBy('waste_customer_id', 'member_id')
            ->with(['wasteCustomer', 'member'])
            ->orderByDesc('balance')
            ->get();

        $filename = 'saldo-nasabah-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($balances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode Nasabah', 'Nama Nasabah', 'Tipe Nasabah', 'Total Setoran', 'Total Penarikan', 'Saldo Akhir']);

            foreach ($balances as $b) {
                $code = $b->wasteCustomer ? $b->wasteCustomer->customer_code : ($b->member->member_code ?? '');
                $name = $b->wasteCustomer ? $b->wasteCustomer->name : ($b->member->name ?? '');
                $type = $b->wasteCustomer ? 'Mandiri / Bank Sampah' : 'Warga RT';

                fputcsv($handle, [
                    $code,
                    $name,
                    $type,
                    $b->total_credit,
                    $b->total_debit,
                    $b->balance,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
