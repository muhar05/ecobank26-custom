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

        $totalIn = (clone $query)->where('type', 'in')->sum('amount');
        $totalOut = (clone $query)->where('type', 'out')->sum('amount');
        $balance = WasteBankCashLedger::latest('id')->value('balance') ?? 0;

        $ledgers = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString()->fragment('table-section');

        return view('bank-sampah.cash-report.index', compact('ledgers', 'totalIn', 'totalOut', 'balance'));
    }

    public function pdf(Request $request)
    {
        $query = WasteBankCashLedger::query();

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $ledgers = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
        $totalIn = (clone $query)->where('type', 'in')->sum('amount');
        $totalOut = (clone $query)->where('type', 'out')->sum('amount');
        
        $dateLabel = '';
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $dateLabel = $request->date_from . ' sd ' . $request->date_to;
        } elseif ($request->filled('date_from')) {
            $dateLabel = 'Mulai ' . $request->date_from;
        } elseif ($request->filled('date_to')) {
            $dateLabel = 'Sampai ' . $request->date_to;
        } else {
            $dateLabel = 'Seluruh Waktu';
        }

        return view('bank-sampah.cash-report.pdf', compact('ledgers', 'totalIn', 'totalOut', 'dateLabel'));
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
            
            // PENTING: Tambahkan BOM UTF-8 agar Microsoft Excel langsung mendeteksi pemisah kolom & teks dengan benar
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header CSV
            fputcsv($handle, ['Tanggal', 'Keterangan', 'Masuk', 'Keluar', 'Saldo Berjalan']);
            
            foreach ($ledgers as $l) {
                // Atur format nominal rupiah untuk masing-masing kolom angka
                $masuk = $l->type === 'in' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '-';
                $keluar = $l->type === 'out' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '-';
                $saldo = 'Rp ' . number_format($l->balance, 0, ',', '.');

                fputcsv($handle, [
                    $l->date->format('Y-m-d'),
                    $l->description,
                    $masuk,
                    $keluar,
                    $saldo,
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
