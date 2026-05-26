<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $rtFilter = $request->input('rt_id');
        $kkFilter = $request->input('kk_id');

        $members = Member::with(['user', 'kk.rt'])
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('member_code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%"))
            ->when($rtFilter, function ($q) use ($rtFilter) {
                $q->whereHas('kk', fn($qk) => $qk->where('rt_id', $rtFilter));
            })
            ->when($kkFilter, function ($q) use ($kkFilter) {
                $q->where('kk_id', $kkFilter);
            })
            ->latest()->paginate(20)->withQueryString();

        $rts = \App\Models\Rt::orderBy('rt_number')->get();
        $kks = \App\Models\Kk::orderBy('family_head')->get();

        return view('members.index', compact('members', 'search', 'rts', 'kks', 'rtFilter', 'kkFilter'));
    }

    public function create()
    {
        $nextCode = Member::generateNextCode();
        $kks = \App\Models\Kk::orderBy('family_head')->get();
        return view('members.create', compact('nextCode', 'kks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_code' => 'nullable|string|max:50|unique:members,member_code',
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'kk_id' => 'nullable|exists:kks,id',
            'relationship' => 'nullable|string|max:50',
        ]);

        if (empty($validated['member_code'])) {
            $validated['member_code'] = Member::generateNextCode();
        }

        Member::create($validated);

        return redirect()->route('members.index')->with('success', 'Data warga berhasil ditambahkan.');
    }

    public function show(Member $member)
    {
        $member->load('user.roles');
        $member->loadCount('contributions');
        
        $totalContribution = $member->contributions()->sum('amount');
        $latestActivity = $member->contributions()->latest('date')->first();

        $credit = \App\Models\SavingsLedger::where('member_id', $member->id)->where('type', 'credit')->sum('amount');
        $debit = \App\Models\SavingsLedger::where('member_id', $member->id)->where('type', 'debit')->sum('amount');
        $totalSavings = $credit - $debit;

        return view('members.show', compact('member', 'totalContribution', 'latestActivity', 'totalSavings'));
    }

    public function edit(Member $member)
    {
        $kks = \App\Models\Kk::orderBy('family_head')->get();
        return view('members.edit', compact('member', 'kks'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'member_code' => 'required|string|max:50|unique:members,member_code,' . $member->id,
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'kk_id' => 'nullable|exists:kks,id',
            'relationship' => 'nullable|string|max:50',
        ]);

        $member->update($validated);

        return redirect()->route('members.index')->with('success', 'Data warga berhasil diperbarui.');
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->route('members.index')->with('success', 'Data warga berhasil dihapus.');
    }

    public function resetPassword(Request $request, Member $member)
    {
        if (!auth()->user()->hasAnyRole(['admin_rt', 'admin_bank_sampah'])) {
            abort(403, 'Anda tidak memiliki akses untuk mereset password.');
        }

        if (!$member->user) {
            return back()->with('error', 'Warga ini belum memiliki akun yang terhubung.');
        }

        if (!$member->user->hasRole('warga') || $member->user->hasAnyRole(['admin_rt', 'admin_bank_sampah', 'bendahara'])) {
            abort(403, 'Hanya dapat mereset password untuk akun warga biasa.');
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $member->user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil direset. Berikan password sementara kepada warga.');
    }

    public function createLoginAccount(Request $request, Member $member)
    {
        if (!auth()->user()->hasAnyRole(['admin_rt', 'admin_bank_sampah'])) {
            abort(403, 'Anda tidak memiliki akses untuk membuat akun login.');
        }

        if ($member->user_id) {
            return back()->with('error', 'Warga ini sudah memiliki akun login yang terhubung.');
        }

        $validated = $request->validate([
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8',
        ]);

        // Normalisasi nomor telepon: hapus karakter non-angka
        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        
        // Pengecekan unik setelah normalisasi (manual safeguard)
        if (\App\Models\User::where('phone', $phone)->exists()) {
            return back()->withErrors(['phone' => 'Nomor telepon ini sudah digunakan oleh akun lain.']);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($member, $phone, $validated) {
                $user = \App\Models\User::create([
                    'name' => $member->name,
                    'phone' => $phone,
                    'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                ]);

                $user->assignRole('warga');

                $member->update(['user_id' => $user->id]);
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membuat akun: ' . $e->getMessage());
        }

        return back()->with('success', 'Akun login berhasil dibuat. Berikan password sementara kepada warga.');
    }
}
