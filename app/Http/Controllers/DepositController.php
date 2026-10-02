<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\DepositDetail;
use App\Models\SavingsLedger;
use App\Models\WasteCategory;
use App\Models\WasteCustomer;
use App\Services\BankSampahService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $deposits = Deposit::with(['wasteCustomer', 'details.wasteCategory'])
            ->when($search, fn($q) => $q->where('notes', 'like', "%{$search}%")
                ->orWhereHas('wasteCustomer', fn($q2) => $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")))
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('bank-sampah.deposits.index', compact('deposits', 'search', 'startDate', 'endDate'));
    }

    public function create(Request $request)
    {
        $customers = WasteCustomer::where('status', 'active')->orderBy('name')->get();
        $categories = WasteCategory::with('wasteCategoryGroup')->orderBy('name')->get();
        $groups = \App\Models\WasteCategoryGroup::active()->orderBy('name')->get();

        $selectedDate = $request->input('date', date('Y-m-d'));
        $todayDeposits = Deposit::with(['wasteCustomer', 'details.wasteCategory'])
            ->whereDate('date', $selectedDate)
            ->latest('id')
            ->get();

        $todayTotalWeight = (float) $todayDeposits->sum(fn($d) => $d->details->sum('weight'));
        $todayTotalAmount = (float) $todayDeposits->sum('total_amount');
        $todayCustomerCount = $todayDeposits->pluck('waste_customer_id')->unique()->count();

        return view('bank-sampah.deposits.create', compact(
            'customers',
            'categories',
            'groups',
            'todayDeposits',
            'todayTotalWeight',
            'todayTotalAmount',
            'todayCustomerCount',
            'selectedDate'
        ));
    }

    public function store(Request $request, BankSampahService $service)
    {
        $request->validate([
            'waste_customer_id' => 'required|exists:waste_customers,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $details = $this->parseDetails($request);

        if (empty($details)) {
            return back()->withErrors(['details' => 'Minimal satu item timbangan harus diisi dengan berat lebih dari 0.'])->withInput();
        }

        $deposit = $service->recordDeposit([
            'waste_customer_id' => $request->waste_customer_id,
            'date' => $request->date,
            'notes' => $request->notes,
            'details' => $details,
        ]);

        if ($request->input('action') === 'save_and_next') {
            $customerName = $deposit->wasteCustomer->name ?? 'Nasabah';
            return redirect()->route('bank-sampah.deposits.create', ['date' => $request->date])
                ->with('success', "Setoran atas nama {$customerName} berhasil dicatat. Silakan lanjutkan timbangan berikutnya.");
        }

        return redirect()->route('bank-sampah.deposits.index')
            ->with('success', 'Setoran sampah berhasil dicatat.');
    }

    public function show(Deposit $deposit)
    {
        $deposit->load(['wasteCustomer', 'details.wasteCategory']);

        return view('bank-sampah.deposits.show', compact('deposit'));
    }

    public function edit(Deposit $deposit)
    {
        $deposit->load(['details.wasteCategory', 'wasteCustomer']);
        $customers = WasteCustomer::where('status', 'active')->orderBy('name')->get();
        $categories = WasteCategory::with('wasteCategoryGroup')->orderBy('name')->get();
        $groups = \App\Models\WasteCategoryGroup::active()->orderBy('name')->get();

        return view('bank-sampah.deposits.edit', compact('deposit', 'customers', 'categories', 'groups'));
    }

    public function update(Request $request, Deposit $deposit)
    {
        $request->validate([
            'waste_customer_id' => 'required|exists:waste_customers,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $details = $this->parseDetails($request);

        if (empty($details)) {
            return back()->withErrors(['details' => 'Minimal satu item setoran harus diisi.'])->withInput();
        }

        $totalAmount = collect($details)->sum('subtotal');
        $customer = WasteCustomer::findOrFail($request->waste_customer_id);

        DB::transaction(function () use ($deposit, $request, $details, $totalAmount, $customer) {
            $deposit->update([
                'waste_customer_id' => $customer->id,
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
                    'waste_customer_id' => $customer->id,
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
                $category = WasteCategory::find($row['waste_category_id']);
                $price = (float) ($row['price_per_unit'] ?? ($category->member_price ?? 0));
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
