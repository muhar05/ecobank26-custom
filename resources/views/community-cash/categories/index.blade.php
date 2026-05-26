<x-layouts.dashboard title="Kategori Dana">
<div x-data="{ loading: false }" class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Kategori Dana</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola kategori dana kas RT/RW</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('community-cash.categories.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition w-full sm:w-auto justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Kategori
            </a>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Mini Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Total Kategori</p>
                <p class="text-xl font-bold text-slate-900 dark:text-slate-100 mt-0.5">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-500 dark:text-blue-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Total Target</p>
                <p class="text-lg font-bold text-slate-900 dark:text-slate-100 mt-0.5">Rp {{ number_format($stats['total_target'], 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-500 dark:text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Total Terkumpul</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">Rp {{ number_format($stats['total_collected'], 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-500 dark:text-amber-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Rata-rata Progress</p>
                <p class="text-xl font-bold text-slate-900 dark:text-slate-100 mt-0.5">{{ round($stats['avg_progress']) }}%</p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div id="table-section" 
         x-data="{ tableLoading: false }" 
         @click="if($event.target.closest('nav[role=\'navigation\'] a') || $event.target.closest('a.page-link')) tableLoading = true" 
         :class="{'opacity-70 cursor-wait pointer-events-none': tableLoading}"
         class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden relative transition-all duration-300">
         
        {{-- Top Loading Bar --}}
        <div x-show="tableLoading" style="display: none;" class="absolute top-0 inset-x-0 h-1 z-50 bg-emerald-100 dark:bg-emerald-900/30">
            <div class="h-full bg-emerald-500 w-full animate-pulse"></div>
        </div>

        {{-- Filters & Search --}}
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
            <form method="GET" action="{{ route('community-cash.categories.index') }}" @submit="loading = true" class="flex flex-col lg:flex-row gap-3 w-full">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kategori..." class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                </div>
                <div class="w-full lg:w-40">
                    <select name="status" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="">Semua Status</option>
                        <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="w-full lg:w-40">
                    <select name="progress" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="">Semua Progress</option>
                        <option value="low" {{ ($progress ?? '') === 'low' ? 'selected' : '' }}>&lt; 25%</option>
                        <option value="mid" {{ ($progress ?? '') === 'mid' ? 'selected' : '' }}>25% - 75%</option>
                        <option value="high" {{ ($progress ?? '') === 'high' ? 'selected' : '' }}>&gt; 75%</option>
                    </select>
                </div>
                <div class="w-full lg:w-40">
                    <select name="sort" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="latest" {{ ($sort ?? '') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="name" {{ ($sort ?? '') === 'name' ? 'selected' : '' }}>Nama A-Z</option>
                        <option value="target" {{ ($sort ?? '') === 'target' ? 'selected' : '' }}>Target Terbesar</option>
                        <option value="collected" {{ ($sort ?? '') === 'collected' ? 'selected' : '' }}>Dana Terbesar</option>
                    </select>
                </div>
                <button type="submit" :disabled="loading" :class="{'opacity-75 cursor-not-allowed': loading}" class="h-10 bg-emerald-600 dark:bg-emerald-500 text-white px-5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm inline-flex justify-center items-center w-full lg:w-auto">
                    <span x-text="loading ? 'Memfilter...' : 'Filter'"></span>
                </button>
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-white dark:bg-slate-900">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Target</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Terkumpul</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-64">Progress</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($categories as $category)
                        @php
                            $collected = $category->contributions_sum_amount ?? 0;
                            $target = $category->target_amount;
                            $hasTarget = $target && $target > 0;
                            $percentage = $hasTarget ? min(round(($collected / $target) * 100), 100) : 0;
                            $showRow = true;
                            if(($progress ?? '') === 'low') $showRow = $percentage < 25;
                            elseif(($progress ?? '') === 'mid') $showRow = $percentage >= 25 && $percentage <= 75;
                            elseif(($progress ?? '') === 'high') $showRow = $percentage > 75;
                        @endphp
                        @if($showRow)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold flex-shrink-0">
                                        {{ strtoupper(substr($category->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $category->name }}</p>
                                        @if($category->description)
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-[200px] truncate" title="{{ $category->description }}">{{ $category->description }}</p>
                                        @endif
                                        @if($category->is_mandatory)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/30 mt-1">
                                                Wajib: Rp {{ number_format($category->monthly_amount, 0, ',', '.') }}/bln
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-slate-600 dark:text-slate-300">
                                @if($hasTarget)
                                    Rp {{ number_format($target, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($collected, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($hasTarget)
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $percentage }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                            <div class="h-2 rounded-full transition-all duration-500 ease-out {{ $percentage >= 100 ? 'bg-emerald-500' : ($percentage >= 50 ? 'bg-blue-500' : 'bg-amber-500') }}" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Tanpa target</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($category->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-3 text-sm font-medium">
                                <a href="{{ route('community-cash.categories.edit', $category) }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-300 transition">Edit</a>
                                <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('community-cash.categories.destroy', $category) }}'})" class="text-rose-600 dark:text-rose-400 hover:text-rose-900 dark:hover:text-rose-300 transition">Hapus</button>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada data kategori dana.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($categories as $category)
                @php
                    $collected = $category->contributions_sum_amount ?? 0;
                    $target = $category->target_amount;
                    $hasTarget = $target && $target > 0;
                    $percentage = $hasTarget ? min(round(($collected / $target) * 100), 100) : 0;
                    $showRow = true;
                    if(($progress ?? '') === 'low') $showRow = $percentage < 25;
                    elseif(($progress ?? '') === 'mid') $showRow = $percentage >= 25 && $percentage <= 75;
                    elseif(($progress ?? '') === 'high') $showRow = $percentage > 75;
                @endphp
                @if($showRow)
                <div class="p-4 bg-white dark:bg-slate-900">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold flex-shrink-0">
                                {{ strtoupper(substr($category->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $category->name }}</p>
                                <div class="flex flex-wrap gap-1.5 mt-0.5">
                                    @if($category->is_active)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-emerald-600 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-500 dark:text-slate-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                        </span>
                                    @endif
                                    @if($category->is_mandatory)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800/30">
                                            Wajib: Rp {{ number_format($category->monthly_amount, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-sm font-medium">
                            <a href="{{ route('community-cash.categories.edit', $category) }}" class="text-emerald-600 dark:text-emerald-400">Edit</a>
                            <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('community-cash.categories.destroy', $category) }}'})" class="text-rose-600 dark:text-rose-400">Hapus</button>
                        </div>
                    </div>
                    @if($category->description)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">{{ $category->description }}</p>
                    @endif
                    <div class="grid grid-cols-2 gap-3 mb-3 bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl">
                        <div>
                            <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">Target</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $hasTarget ? 'Rp '.number_format($target, 0, ',', '.') : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">Terkumpul</p>
                            <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($collected, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @if($hasTarget)
                        <div class="flex flex-col gap-1.5 mt-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium text-slate-500 dark:text-slate-400">Progress</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $percentage }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-500 {{ $percentage >= 100 ? 'bg-emerald-500' : ($percentage >= 50 ? 'bg-blue-500' : 'bg-amber-500') }}" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
                @endif
            @empty
                <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    Belum ada data kategori.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if(method_exists($categories, 'hasPages') && $categories->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $categories->links() }}
            </div>
        @elseif(!method_exists($categories, 'hasPages') && $categories instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>

<x-confirm-delete-modal />
</x-layouts.dashboard>
