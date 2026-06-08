<x-layouts.dashboard title="Data Warga">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Data Warga</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Kelola informasi warga dan nasabah RT/RW</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('members.export', request()->query()) }}" 
                    class="inline-flex items-center gap-2 bg-slate-100 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 transition">
                    Export Excel
                </a>
                <a href="{{ route('members.import-v2') }}" 
                    class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-100 transition">
                    Import Excel
                </a>
                <a href="{{ route('members.create') }}" 
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Tambah Warga
                </a>
            </div>
        </div>

        <!-- Search & Filter Toolbar -->
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <form method="GET" action="{{ route('members.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3">
                <div class="lg:col-span-2">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari warga..." class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm h-11">
                </div>
                <div>
                    <select name="rt_id" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm h-11">
                        <option value="">Semua RT</option>
                        @foreach($rts as $rt) <option value="{{ $rt->id }}" {{ ($rtFilter ?? '') == $rt->id ? 'selected' : '' }}>RT {{ $rt->rt_number }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <select name="gender" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm h-11">
                        <option value="">Semua Gender</option>
                        <option value="L" {{ ($genderFilter ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ ($genderFilter ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <select name="status" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm h-11">
                        <option value="">Semua Status</option>
                        <option value="Aktif" {{ ($statusFilter ?? '') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Kontrak" {{ ($statusFilter ?? '') == 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                        <option value="Pindah" {{ ($statusFilter ?? '') == 'Pindah' ? 'selected' : '' }}>Pindah</option>
                        <option value="Kosong" {{ ($statusFilter ?? '') == 'Kosong' ? 'selected' : '' }}>Kosong</option>
                    </select>
                </div>
                <div>
                    <select name="age_category" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm h-11">
                        <option value="">Umur</option>
                        <option value="anak" {{ ($ageCategory ?? '') == 'anak' ? 'selected' : '' }}>Anak (<17)</option>
                        <option value="dewasa" {{ ($ageCategory ?? '') == 'dewasa' ? 'selected' : '' }}>Dewasa (17-59)</option>
                        <option value="lansia" {{ ($ageCategory ?? '') == 'lansia' ? 'selected' : '' }}>Lansia (>=60)</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full bg-emerald-600 text-white px-4 rounded-lg text-sm font-medium h-11">Filter</button>
                    <a href="{{ route('members.index') }}" class="w-full bg-slate-100 text-slate-700 px-4 rounded-lg text-sm font-medium h-11 flex items-center justify-center">Reset</a>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Warga</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Gender</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Tgl Lahir / Umur</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">RT</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($members as $member)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium">{{ $member->name }}</td>
                                <td class="px-6 py-4 text-sm">{{ $member->gender }}</td>
                                <td class="px-6 py-4 text-sm">
                                    {{ $member->birth_date ? \Carbon\Carbon::parse($member->birth_date)->format('d/m/Y') : '-' }}
                                    <span class="text-slate-500">({{ $member->birth_date ? \Carbon\Carbon::parse($member->birth_date)->age : '-' }} th)</span>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $member->kk ? 'RT ' . $member->kk->rt->rt_number : '-' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-800">Aktif</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('members.show', $member) }}" class="text-emerald-600 text-sm font-bold">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">Data tidak ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            {{ $members->links() }}
        </div>
    </div>
</x-layouts.dashboard>
