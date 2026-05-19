<?php

namespace App\Http\Controllers;

use App\Models\Collector;
use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\Member;
use App\Models\SavingsLedger;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use App\Services\BankSampahService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    public function index()
    {
        $deposits = Deposit::with(['member', 'details.wasteCategory'])
            ->latest('date')->paginate(20);

        return view('bank-sampah.deposits.index', compact('deposits'));
    }

    public function create()
    {
        $members = Member::orderBy('name')->get();
        $collectors = Collector::orderBy('name')->get();
        $categories = WasteCategory::orderBy('name')->get();
        $wastePrices = WastePrice::all()->groupBy('collector_id')
            ->map(fn ($items) => $items->keyBy('waste_category_id')->map(fn ($p) => [
                'member_price' => (float) $p->member_price,
                'collector_price' => (float) $p->collector_price,
            ]));

        return view('bank-sampah.deposits.create', compact('members', 'collectors', 'categories', 'wastePrices'));
    }

    public function store(Request $request, BankSampahService $service)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'collector_id' => 'required|exists:collectors,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $details = $this->parseDetails($request);

        if (empty($details)) {
            return back()->withErrors(['details' => 'Minimal satu item setoran harus diisi.'])->withInput();
        }

        $service->recordDeposit([
            'member_id' => $request->member_id,
            'collector_id' => $request->collector_id,
            'date' => $request->date,
            'notes' => $request->notes,
            'details' => $details,
        ]);

        return redirect()->route('bank-sampah.deposits.index')
            ->with('success', 'Setoran sampah berhasil dicatat.');
    }

    public function edit(Deposit $deposit)
    {
        $deposit->load('details');
        $members = Member::orderBy('name')->get();
        $collectors = Collector::orderBy('name')->get();
        $categories = WasteCategory::orderBy('name')->get();
        $wastePrices = WastePrice::all()->groupBy('collector_id')
            ->map(fn ($items) => $items->keyBy('waste_category_id')->map(fn ($p) => [
                'member_price' => (float) $p->member_price,
                'collector_price' => (float) $p->collector_price,
            ]));

        return view('bank-sampah.deposits.edit', compact('deposit', 'members', 'collectors', 'categories', 'wastePrices'));
    }

    public function update(Request $request, Deposit $deposit)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'collector_id' => 'required|exists:collectors,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $details = $this->parseDetails($request);

        if (empty($details)) {
            return back()->withErrors(['details' => 'Minimal satu item setoran harus diisi.'])->withInput();
        }

        $totalAmount = collect($details)->sum('subtotal');

        DB::transaction(function () use ($deposit, $request, $details, $totalAmount) {
            $deposit->update([
                'member_id' => $request->member_id,
                'collector_id' => $request->collector_id,
                'date' => $request->date,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
            ]);

            // Replace details
            $deposit->details()->delete();
            foreach ($details as $detail) {
                DepositDetail::create(array_merge($detail, ['deposit_id' => $deposit->id]));
            }

            // Update savings ledger
            SavingsLedger::where('reference_type', Deposit::class)
                ->where('reference_id', $deposit->id)
                ->update([
                    'member_id' => $request->member_id,
                    'amount' => $totalAmount,
                ]);
        });

        return redirect()->route('bank-sampah.deposits.index')
            ->with('success', 'Setoran sampah berhasil diperbarui.');
    }

    public function destroy(Deposit $deposit)
    {
        DB::transaction(function () use ($deposit) {
            SavingsLedger::where('reference_type', Deposit::class)
                ->where('reference_id', $deposit->id)
                ->delete();

            $deposit->details()->delete();
            $deposit->delete();
        });

        return redirect()->route('bank-sampah.deposits.index')
            ->with('success', 'Setoran sampah berhasil dihapus.');
    }

    private function parseDetails(Request $request): array
    {
        $details = [];
        foreach ($request->input('details', []) as $row) {
            if (!empty($row['waste_category_id']) && !empty($row['weight']) && $row['weight'] > 0) {
                $weight = (float) $row['weight'];
                $price = (float) ($row['price_per_unit'] ?? 0);
                $details[] = [
                    'waste_category_id' => $row['waste_category_id'],
                    'weight' => $weight,
                    'price_per_unit' => $price,
                    'subtotal' => $weight * $price,
                ];
            }
        }
        return $details;
    }
}
