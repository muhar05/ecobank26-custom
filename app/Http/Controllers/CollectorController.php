<?php

namespace App\Http\Controllers;

use App\Models\Collector;
use Illuminate\Http\Request;

class CollectorController extends Controller
{
    public function index()
    {
        $collectors = Collector::latest()->get();
        return view('bank-sampah.collectors.index', compact('collectors'));
    }

    public function create()
    {
        return view('bank-sampah.collectors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        Collector::create($validated);

        return redirect()->route('bank-sampah.collectors.index')
            ->with('success', 'Data pengepul berhasil ditambahkan.');
    }

    public function edit(Collector $collector)
    {
        return view('bank-sampah.collectors.edit', compact('collector'));
    }

    public function update(Request $request, Collector $collector)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $collector->update($validated);

        return redirect()->route('bank-sampah.collectors.index')
            ->with('success', 'Data pengepul berhasil diperbarui.');
    }

    public function destroy(Collector $collector)
    {
        $collector->delete();
        return redirect()->route('bank-sampah.collectors.index')
            ->with('success', 'Data pengepul berhasil dihapus.');
    }
}
