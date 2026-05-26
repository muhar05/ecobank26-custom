<x-layouts.dashboard title="Data Rukun Tetangga (RT)">
<div x-data="{ loading: false }" class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Data Rukun Tetangga (RT)</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola data wilayah Rukun Tetangga (RT) pada sistem</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('rts.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition w-full sm:w-auto justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah RT
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
            <form method="GET" action="{{ route('rts.index') }}" @submit="loading = true" class="flex gap-3 w-full">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nomor RT atau deskripsi..." class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                </div>
                <button type="submit" :disabled="loading" class="h-10 bg-emerald-600 dark:bg-emerald-500 text-white px-5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm inline-flex justify-center items-center">
                    <span x-text="loading ? 'Mencari...' : 'Cari'"></span>
                </button>
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-white dark:bg-slate-900">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">No RT</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jumlah KK</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($rts as $rt)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition duration-150">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">
                                    RT {{ $rt->rt_number }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                {{ $rt->description ?? 'Tidak ada deskripsi' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center font-semibold text-slate-800 dark:text-slate-200">
                                {{ $rt->kks_count }} KK
                            </td>
                            <td class="px-6 py-4 text-right space-x-3 text-sm font-medium">
                                <a href="{{ route('rts.edit', $rt) }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-300 transition">Edit</a>
                                <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('rts.destroy', $rt) }}'})" class="text-rose-600 dark:text-rose-400 hover:text-rose-900 dark:hover:text-rose-300 transition">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada data RT.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($rts as $rt)
                <div class="p-4 bg-white dark:bg-slate-900 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">
                            RT {{ $rt->rt_number }}
                        </span>
                        <div class="flex items-center gap-3 text-sm font-medium">
                            <a href="{{ route('rts.edit', $rt) }}" class="text-emerald-600 dark:text-emerald-400">Edit</a>
                            <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('rts.destroy', $rt) }}'})" class="text-rose-600 dark:text-rose-400">Hapus</button>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $rt->description ?? 'Tidak ada deskripsi' }}</p>
                    <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-lg flex justify-between items-center">
                        <span>Total Terdaftar:</span>
                        <span class="text-emerald-600 dark:text-emerald-400">{{ $rt->kks_count }} KK</span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    Belum ada data RT.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($rts->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $rts->links() }}
            </div>
        @endif

    </div>
</div>

<x-confirm-delete-modal />
</x-layouts.dashboard>
