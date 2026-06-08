<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = $this->applyFilters($request);
        $members = $members->latest()->paginate(20)->withQueryString();

        $rts = \App\Models\Rt::orderBy('rt_number')->get();
        $kks = \App\Models\Kk::orderBy('family_head')->get();

        $search = $request->input('search');
        $rtFilter = $request->input('rt_id');
        $kkFilter = $request->input('kk_id');
        $genderFilter = $request->input('gender');
        $statusFilter = $request->input('status');
        $ageCategory = $request->input('age_category');

        return view('members.index', compact('members', 'search', 'rts', 'kks', 'rtFilter', 'kkFilter', 'genderFilter', 'statusFilter', 'ageCategory'));
    }

    public function export(Request $request)
    {
        $membersQuery = $this->applyFilters($request);
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MembersExport($membersQuery), 'data-warga-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function applyFilters(Request $request)
    {
        $search = $request->input('search');
        $rtFilter = $request->input('rt_id');
        $kkFilter = $request->input('kk_id');
        $genderFilter = $request->input('gender');
        $statusFilter = $request->input('status');
        $ageCategory = $request->input('age_category');

        return Member::with(['kk.rt'])
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
            ->when($genderFilter, function ($q) use ($genderFilter) {
                $q->whereIn('gender', $genderFilter === 'L' ? ['L', 'Laki-laki'] : ['P', 'Perempuan']);
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                // Assuming status is on KK model
                $q->whereHas('kk', fn($qk) => $qk->where('status', $statusFilter));
            })
            ->when($ageCategory, function ($q) use ($ageCategory) {
                $now = \Carbon\Carbon::now();
                if ($ageCategory === 'anak') {
                    $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, ?) < 17', [$now]);
                } elseif ($ageCategory === 'dewasa') {
                    $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, ?) BETWEEN 17 AND 59', [$now]);
                } elseif ($ageCategory === 'lansia') {
                    $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, ?) >= 60', [$now]);
                }
            });
    }

    public function create()
    {
        $nextCode = Member::generateNextCode();
        $kks = \App\Models\Kk::orderBy('family_head')->get();
        return view('members.create', compact('nextCode', 'kks'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isOperationalAdmin = $user && $user->hasAnyRole(['admin_rt', 'admin_rw', 'bendahara', 'bendahara_rw']);

        $validated = $request->validate([
            'member_code' => 'nullable|string|max:50|unique:members,member_code',
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'gender' => 'nullable|in:L,P,Laki-laki,Perempuan',
            'address' => 'nullable|string|max:255',
            'kk_id' => $isOperationalAdmin ? 'required|exists:kks,id' : 'nullable|exists:kks,id',
            'relationship' => 'nullable|string|max:50',
        ], [
            'kk_id.required' => 'Data warga operasional RT wajib terhubung dengan Kartu Keluarga.',
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
        $user = auth()->user();
        $isOperationalAdmin = $user && $user->hasAnyRole(['admin_rt', 'admin_rw', 'bendahara', 'bendahara_rw']);

        $validated = $request->validate([
            'member_code' => 'required|string|max:50|unique:members,member_code,' . $member->id,
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'gender' => 'nullable|in:L,P,Laki-laki,Perempuan',
            'address' => 'nullable|string|max:255',
            'kk_id' => $isOperationalAdmin ? 'required|exists:kks,id' : 'nullable|exists:kks,id',
            'relationship' => 'nullable|string|max:50',
        ], [
            'kk_id.required' => 'Data warga operasional RT wajib terhubung dengan Kartu Keluarga.',
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
