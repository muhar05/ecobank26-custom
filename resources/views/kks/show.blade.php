<x-layouts.dashboard title="Detail Kartu Keluarga">
<div class="space-y-6">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('kks.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar KK
        </a>
        <a href="{{ route('kks.edit', $kk) }}" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            Edit KK
        </a>
    </div>

    {{-- KK Overview Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Keluarga: {{ $kk->family_head }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">No KK: <span class="font-mono font-semibold">{{ $kk->kk_number ?? 'Belum Diisi' }}</span></p>
            </div>
            <div>
                @if($kk->status === \App\Models\Kk::STATUS_ACTIVE)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                    </span>
                @elseif($kk->status === \App\Models\Kk::STATUS_CONTRACT)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Kontrak
                    </span>
                @elseif($kk->status === \App\Models\Kk::STATUS_MOVED)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-800/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Pindah
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Kosong
                    </span>
                @endif
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-xs uppercase tracking-wider font-semibold text-slate-400 dark:text-slate-500">Wilayah Rukun Tetangga</p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">RT {{ $kk->rt->rt_number }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider font-semibold text-slate-400 dark:text-slate-500">No. Telepon / WA</p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">{{ $kk->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider font-semibold text-slate-400 dark:text-slate-500">Alamat</p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">{{ $kk->address ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Demographics Summary --}}
    @php
        $balitaCount = $kk->members->filter(fn($m) => $m->age_group === 'balita')->count();
        $anakCount = $kk->members->filter(fn($m) => $m->age_group === 'anak')->count();
        $remajaCount = $kk->members->filter(fn($m) => $m->age_group === 'remaja')->count();
        $dewasaCount = $kk->members->filter(fn($m) => $m->age_group === 'dewasa')->count();
        $lansiaCount = $kk->members->filter(fn($m) => $m->age_group === 'lansia')->count();
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 text-center">
            <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Balita (0-5)</span>
            <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 block mt-1">{{ $balitaCount }} Orang</span>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 text-center">
            <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Anak (6-12)</span>
            <span class="text-2xl font-black text-blue-600 dark:text-blue-400 block mt-1">{{ $anakCount }} Orang</span>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 text-center">
            <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Remaja (13-17)</span>
            <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 block mt-1">{{ $remajaCount }} Orang</span>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 text-center">
            <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Dewasa (18-59)</span>
            <span class="text-2xl font-black text-slate-700 dark:text-slate-300 block mt-1">{{ $dewasaCount }} Orang</span>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 text-center col-span-2 md:col-span-1">
            <span class="text-xs text-slate-400 block uppercase font-bold tracking-wider">Lansia (60+)</span>
            <span class="text-2xl font-black text-rose-600 dark:text-rose-400 block mt-1">{{ $lansiaCount }} Orang</span>
        </div>
    </div>

    {{-- Members List Section --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h4 class="text-base font-bold text-slate-900 dark:text-slate-100">Daftar Anggota Keluarga</h4>
            <span class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">{{ $kk->members->count() }} Terdaftar</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50/50 dark:bg-slate-800/30">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kode Warga</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Hubungan</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gender</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal Lahir</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Usia</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kelompok Usia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($kk->members as $member)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition duration-150">
                            <td class="px-6 py-4 text-sm font-mono font-bold text-slate-700 dark:text-slate-300">
                                {{ $member->member_code }}
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-slate-100">
                                {{ $member->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                {{ $member->relationship ?? 'Anggota Lainnya' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-slate-600 dark:text-slate-400">
                                {{ $member->gender ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ $member->birth_date ? $member->birth_date->format('d M Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center font-bold text-slate-800 dark:text-slate-200">
                                {{ $member->age !== null ? $member->age . ' Tahun' : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                @if($member->age_group === 'balita')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400">Balita</span>
                                @elseif($member->age_group === 'anak')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-400">Anak-anak</span>
                                @elseif($member->age_group === 'remaja')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-400">Remaja</span>
                                @elseif($member->age_group === 'dewasa')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Dewasa</span>
                                @elseif($member->age_group === 'lansia')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-400">Lansia</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                Belum ada anggota keluarga yang dihubungkan ke KK ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-layouts.dashboard>
