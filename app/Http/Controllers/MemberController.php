<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $members = Member::with('user')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('member_code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%"))
            ->latest()->paginate(20)->withQueryString();
        return view('members.index', compact('members', 'search'));
    }

    public function create()
    {
        $nextCode = Member::generateNextCode();
        return view('members.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_code' => 'nullable|string|max:50|unique:members,member_code',
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        if (empty($validated['member_code'])) {
            $validated['member_code'] = Member::generateNextCode();
        }

        Member::create($validated);

        return redirect()->route('members.index')->with('success', 'Data warga berhasil ditambahkan.');
    }

    public function show(Member $member)
    {
        $member->loadCount('contributions');
        $totalContribution = $member->contributions()->sum('amount');
        $latestActivity = $member->contributions()->latest('date')->first();

        return view('members.show', compact('member', 'totalContribution', 'latestActivity'));
    }

    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'member_code' => 'required|string|max:50|unique:members,member_code,' . $member->id,
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $member->update($validated);

        return redirect()->route('members.index')->with('success', 'Data warga berhasil diperbarui.');
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->route('members.index')->with('success', 'Data warga berhasil dihapus.');
    }
}
