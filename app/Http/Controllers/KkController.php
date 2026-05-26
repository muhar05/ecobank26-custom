<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKkRequest;
use App\Http\Requests\UpdateKkRequest;
use App\Models\Kk;
use App\Models\Rt;
use Illuminate\Http\Request;

class KkController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $rtFilter = $request->input('rt_id');
        $statusFilter = $request->input('status');

        $query = Kk::with('rt')
            ->withCount('members')
            ->when($search, function ($q) use ($search) {
                $q->where('kk_number', 'like', "%{$search}%")
                  ->orWhere('family_head', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            })
            ->when($rtFilter, function ($q) use ($rtFilter) {
                $q->where('rt_id', $rtFilter);
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            });

        $kks = $query->latest()->paginate(15)->withQueryString();
        $rts = Rt::orderBy('rt_number')->get();
        $statuses = Kk::getStatuses();

        return view('kks.index', compact('kks', 'rts', 'statuses', 'search', 'rtFilter', 'statusFilter'));
    }

    public function create()
    {
        $rts = Rt::orderBy('rt_number')->get();
        $statuses = Kk::getStatuses();

        return view('kks.create', compact('rts', 'statuses'));
    }

    public function store(StoreKkRequest $request)
    {
        Kk::create($request->validated());

        return redirect()->route('kks.index')
            ->with('success', 'Data Kartu Keluarga berhasil ditambahkan.');
    }

    public function show(Kk $kk)
    {
        $kk->load(['rt', 'members']);
        return view('kks.show', compact('kk'));
    }

    public function edit(Kk $kk)
    {
        $rts = Rt::orderBy('rt_number')->get();
        $statuses = Kk::getStatuses();

        return view('kks.edit', compact('kk', 'rts', 'statuses'));
    }

    public function update(UpdateKkRequest $request, Kk $kk)
    {
        $kk->update($request->validated());

        return redirect()->route('kks.index')
            ->with('success', 'Data Kartu Keluarga berhasil diperbarui.');
    }

    public function destroy(Kk $kk)
    {
        if ($kk->members()->exists()) {
            return redirect()->route('kks.index')
                ->with('error', 'Data Kartu Keluarga tidak dapat dihapus karena masih memiliki anggota keluarga (Warga).');
        }

        if ($kk->bills()->exists()) {
            return redirect()->route('kks.index')
                ->with('error', 'Data Kartu Keluarga tidak dapat dihapus karena masih memiliki tagihan iuran kas.');
        }

        $kk->delete();

        return redirect()->route('kks.index')
            ->with('success', 'Data Kartu Keluarga berhasil dihapus.');
    }
}
