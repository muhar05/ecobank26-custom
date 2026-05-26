<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRtRequest;
use App\Http\Requests\UpdateRtRequest;
use App\Models\Rt;
use Illuminate\Http\Request;

class RtController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Rt::withCount('kks')
            ->when($search, function ($q) use ($search) {
                $q->where('rt_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });

        $rts = $query->latest()->paginate(15)->withQueryString();

        return view('rts.index', compact('rts', 'search'));
    }

    public function create()
    {
        return view('rts.create');
    }

    public function store(StoreRtRequest $request)
    {
        Rt::create($request->validated());

        return redirect()->route('rts.index')
            ->with('success', 'Data RT berhasil ditambahkan.');
    }

    public function edit(Rt $rt)
    {
        return view('rts.edit', compact('rt'));
    }

    public function update(UpdateRtRequest $request, Rt $rt)
    {
        $rt->update($request->validated());

        return redirect()->route('rts.index')
            ->with('success', 'Data RT berhasil diperbarui.');
    }

    public function destroy(Rt $rt)
    {
        if ($rt->kks()->exists()) {
            return redirect()->route('rts.index')
                ->with('error', 'Data RT tidak dapat dihapus karena masih memiliki data Kartu Keluarga.');
        }

        $rt->delete();

        return redirect()->route('rts.index')
            ->with('success', 'Data RT berhasil dihapus.');
    }
}
