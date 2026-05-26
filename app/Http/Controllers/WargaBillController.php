<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\FundCategory;
use Illuminate\Http\Request;

class WargaBillController extends Controller
{
    public function index(Request $request)
    {
        $member = auth()->user()->member;
        
        if (!$member || !$member->kk_id) {
            return redirect()->route('warga.dashboard');
        }

        $kkId = $member->kk_id;
        
        $monthFilter = $request->input('month');
        $yearFilter = $request->input('year');
        $statusFilter = $request->input('status');

        $query = Bill::where('kk_id', $kkId)
            ->with(['fundCategory', 'payments'])
            ->when($monthFilter, function ($q) use ($monthFilter) {
                $q->where('month', $monthFilter);
            })
            ->when($yearFilter, function ($q) use ($yearFilter) {
                $q->where('year', $yearFilter);
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            });

        $bills = $query->latest()->paginate(15)->withQueryString();

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('warga.bills.index', compact('bills', 'months', 'monthFilter', 'yearFilter', 'statusFilter'));
    }
}
