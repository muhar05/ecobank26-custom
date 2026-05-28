<x-layouts.dashboard title="Data Kartu Keluarga (KK)">
<div x-data="{ loading: false }" class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Data Kartu Keluarga (KK)</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola data Kartu Keluarga (KK) dan status hunian warga</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('kks.import') }}" class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 shadow-sm transition w-full sm:w-auto justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import KK (Excel)
            </a>
            <a href="{{ route('kks.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition w-full sm:w-auto justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah KK
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-900/30 text-rose-800 dark:text-rose-300 rounded-xl border border-rose-200 dark:border-rose-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Main Content --}}
    <div id="table-section" 
         x-data="{ tableLoading: false }" 
         @click="if($event.target.closest('nav[role=\'navigation\'] a') || $event.target.closest('a.page-link')) tableLoading = true" 
         :class="{'opacity-70 cursor-wait pointer-events-none': tableLoading}"
         class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden relative transition-all duration-300">
         
        {{-- Loading Bar --}}
        <div x-show="tableLoading" style="display: none;" class="absolute top-0 inset-x-0 h-1 z-50 bg-emerald-100 dark:bg-emerald-900/30">
            <div class="h-full bg-emerald-500 w-full animate-pulse"></div>
        </div>

        {{-- Filters & Search --}}
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
            <form method="GET" action="{{ route('kks.index') }}" @submit="loading = true" class="flex flex-col lg:flex-row gap-3 w-full">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari No KK, Kepala Keluarga, Alamat..." class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                </div>
                <div class="w-full lg:w-48">
                    <select name="rt_id" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="">Semua RT</option>
                        @foreach($rts as $rt)
                            <option value="{{ $rt->id }}" {{ ($rtFilter ?? '') == $rt->id ? 'selected' : '' }}>RT {{ $rt->rt_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full lg:w-48">
                    <select name="status" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="">Semua Status Hunian</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ ($statusFilter ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" :disabled="loading" class="h-10 bg-emerald-600 dark:bg-emerald-500 text-white px-5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm inline-flex justify-center items-center w-full lg:w-auto">
                    <span x-text="loading ? 'Memfilter...' : 'Filter'"></span>
                </button>
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-white dark:bg-slate-900">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">No KK</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kepala Keluarga</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Wilayah</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Anggota</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status Hunian</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($kks as $kk)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition duration-150">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                {{ $kk->kk_number ?? 'Belum Diisi' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $kk->family_head }}</div>
                                @if($kk->phone)
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $kk->phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    RT {{ $kk->rt->rt_number }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center font-medium text-slate-800 dark:text-slate-200">
                                {{ $kk->members_count }} Orang
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($kk->status === \App\Models\Kk::STATUS_ACTIVE)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @elseif($kk->status === \App\Models\Kk::STATUS_CONTRACT)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Kontrak
                                    </span>
                                @elseif($kk->status === \App\Models\Kk::STATUS_MOVED)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-800/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Pindah
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Kosong
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-3 text-sm font-medium">
                                <a href="{{ route('kks.show', $kk) }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition">Detail</a>
                                <a href="{{ route('kks.edit', $kk) }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-300 transition">Edit</a>
                                <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('kks.destroy', $kk) }}'})" class="text-rose-600 dark:text-rose-400 hover:text-rose-900 dark:hover:text-rose-300 transition">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada data Kartu Keluarga.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($kks as $kk)
                <div class="p-4 bg-white dark:bg-slate-900 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $kk->family_head }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $kk->kk_number ?? 'No KK Belum Diisi' }}</p>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs font-semibold">
                            @if($kk->status === \App\Models\Kk::STATUS_ACTIVE)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">Aktif</span>
                            @elseif($kk->status === \App\Models\Kk::STATUS_CONTRACT)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30">Kontrak</span>
                            @elseif($kk->status === \App\Models\Kk::STATUS_MOVED)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-800/30">Pindah</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/30">Kosong</span>
                            @endif
                        </div>
                    </div>
                    @if($kk->address)
                        <p class="text-xs text-slate-600 dark:text-slate-400">{{ $kk->address }}</p>
                    @endif
                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl">
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 block">Wilayah:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">RT {{ $kk->rt->rt_number }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 block">Anggota:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $kk->members_count }} Orang</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-4 text-xs font-semibold border-t border-slate-100 dark:border-slate-800/50 pt-2.5">
                        <a href="{{ route('kks.show', $kk) }}" class="text-slate-600 dark:text-slate-400">Detail</a>
                        <a href="{{ route('kks.edit', $kk) }}" class="text-emerald-600 dark:text-emerald-400">Edit</a>
                        <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('kks.destroy', $kk) }}'})" class="text-rose-600 dark:text-rose-400">Hapus</button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    Belum ada data Kartu Keluarga.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($kks->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $kks->links() }}
            </div>
        @endif

    </div>
</div>

<x-confirm-delete-modal />
</x-layouts.dashboard>
