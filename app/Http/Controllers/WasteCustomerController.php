<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWasteCustomerRequest;
use App\Http\Requests\UpdateWasteCustomerRequest;
use App\Models\Member;
use App\Models\WasteCustomer;
use Illuminate\Http\Request;

class WasteCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteCustomer::with(['member', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest('id')->paginate(15)->withQueryString();

        return view('bank-sampah.customers.index', compact('customers'));
    }

    public function create()
    {
        $members = Member::orderBy('name')->get();
        return view('bank-sampah.customers.create', compact('members'));
    }

    public function store(StoreWasteCustomerRequest $request)
    {
        $data = $request->validated();
        
        if ($data['mode'] === 'existing') {
            $member = Member::findOrFail($data['member_id']);
            
            $customer = WasteCustomer::create([
                'user_id' => $member->user_id,
                'member_id' => $member->id,
                'customer_code' => WasteCustomer::generateNextCustomerCode(),
                'name' => $member->name,
                'phone' => $member->phone ?? $request->phone,
                'address' => $member->address ?? $request->address,
                'status' => $data['status'],
                'joined_at' => now(),
            ]);
        } else {
            $customer = WasteCustomer::create([
                'user_id' => null,
                'member_id' => null,
                'customer_code' => WasteCustomer::generateNextCustomerCode(),
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'status' => $data['status'],
                'joined_at' => now(),
            ]);
        }

        app(\App\Services\ActivityLogService::class)->logInfo(
            'waste_customer.create',
            "Membuat profil nasabah baru {$customer->name} ({$customer->customer_code}).",
            ['after' => $customer->toArray()]
        );

        return redirect()->route('bank-sampah.customers.index')
            ->with('success', 'Nasabah Bank Sampah berhasil ditambahkan.');
    }

    public function show(WasteCustomer $customer)
    {
        $customer->load(['member.kk.rt', 'user']);
        
        // Calculate basic stats for the customer view
        $depositsCount = $customer->deposits()->count();
        $withdrawalsCount = $customer->withdrawals()->count();
        
        $credit = $customer->savingsLedgers()->where('type', 'credit')->sum('amount');
        $debit = $customer->savingsLedgers()->where('type', 'debit')->sum('amount');
        $balance = $credit - $debit;

        return view('bank-sampah.customers.show', compact('customer', 'depositsCount', 'withdrawalsCount', 'balance'));
    }

    public function edit(WasteCustomer $customer)
    {
        $members = Member::orderBy('name')->get();
        return view('bank-sampah.customers.edit', compact('customer', 'members'));
    }

    public function update(UpdateWasteCustomerRequest $request, WasteCustomer $customer)
    {
        $data = $request->validated();
        $before = $customer->toArray();

        if ($data['mode'] === 'existing') {
            $member = Member::findOrFail($data['member_id']);
            
            $customer->update([
                'user_id' => $member->user_id,
                'member_id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone ?? $request->phone,
                'address' => $member->address ?? $request->address,
                'status' => $data['status'],
            ]);
        } else {
            $customer->update([
                'user_id' => null,
                'member_id' => null,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'status' => $data['status'],
            ]);
        }

        app(\App\Services\ActivityLogService::class)->logInfo(
            'waste_customer.update',
            "Memperbarui profil nasabah {$customer->name} ({$customer->customer_code}).",
            [
                'before' => $before,
                'after' => $customer->fresh()->toArray()
            ]
        );

        return redirect()->route('bank-sampah.customers.index')
            ->with('success', 'Nasabah Bank Sampah berhasil diperbarui.');
    }

    public function destroy(WasteCustomer $customer)
    {
        $before = $customer->toArray();

        // Safe protection: prevent deleting customers who have financial records/transactions
        if ($customer->deposits()->exists() || $customer->withdrawals()->exists() || $customer->savingsLedgers()->exists()) {
            app(\App\Services\ActivityLogService::class)->logWarning(
                'waste_customer.delete_attempt',
                "Gagal menghapus nasabah {$customer->name} ({$customer->customer_code}) karena sudah memiliki riwayat keuangan.",
                [
                    'customer_id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'reason' => 'Has financial records/transactions'
                ]
            );

            return back()->with('error', 'Nasabah tidak dapat dihapus karena sudah memiliki riwayat transaksi/tabungan. Silakan ubah status menjadi Inactive.');
        }

        $customer->delete();

        app(\App\Services\ActivityLogService::class)->logInfo(
            'waste_customer.delete_attempt',
            "Berhasil menghapus nasabah {$before['name']} ({$before['customer_code']}).",
            ['before' => $before]
        );

        return redirect()->route('bank-sampah.customers.index')
            ->with('success', 'Nasabah Bank Sampah berhasil dihapus.');
    }
}
