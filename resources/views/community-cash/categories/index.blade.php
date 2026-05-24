<x-layouts.dashboard title="Kategori Dana">
<div x-data="categoryPage()" class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Kategori Dana</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola kategori dana kas RT/RW</p>
        </div>
        <a href="{{ route('community-cash.categories.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-700 shadow-lg shadow-emerald-500/20 transition-all duration-200 hover:shadow-emerald-500/30 hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kategori
        </a>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-2xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-3 animate-fade-in">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Mini Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Kategori</p>
                    <p class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Target</p>
                    <p class="text-lg font-bold text-slate-800 dark:text-slate-100">Rp {{ number_format($stats['total_target'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Terkumpul</p>
                    <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($stats['total_collected'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Rata-rata Progress</p>
                    <p class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ round($stats['avg_progress']) }}%</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
        <form method="GET" action="{{ route('community-cash.categories.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            {{-- Search --}}
            <div class="relative lg:col-span-2">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kategori..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" />
            </div>
            {{-- Status Filter --}}
            <select name="status" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-sm text-slate-700 dark:text-slate-200 py-2.5 px-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                <option value="">Semua Status</option>
                <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            {{-- Progress Filter --}}
            <select name="progress" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-sm text-slate-700 dark:text-slate-200 py-2.5 px-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                <option value="">Semua Progress</option>
                <option value="low" {{ ($progress ?? '') === 'low' ? 'selected' : '' }}>&lt; 25%</option>
                <option value="mid" {{ ($progress ?? '') === 'mid' ? 'selected' : '' }}>25% - 75%</option>
                <option value="high" {{ ($progress ?? '') === 'high' ? 'selected' : '' }}>&gt; 75%</option>
            </select>
            {{-- Sort --}}
            <div class="flex gap-2">
                <select name="sort" class="flex-1 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-sm text-slate-700 dark:text-slate-200 py-2.5 px-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="latest" {{ ($sort ?? '') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="name" {{ ($sort ?? '') === 'name' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="target" {{ ($sort ?? '') === 'target' ? 'selected' : '' }}>Target Terbesar</option>
                    <option value="collected" {{ ($sort ?? '') === 'collected' ? 'selected' : '' }}>Dana Terbesar</option>
                </select>
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </button>
            </div>
        </form>
    </div>

    {{-- Desktop Table --}}
    <div class="hidden md:block bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50/80 dark:bg-slate-700/50 sticky top-0">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Target</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Terkumpul</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-48">Progress</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
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
                        <tr class="hover:bg-emerald-50/50 dark:hover:bg-slate-700/30 transition-colors duration-150 group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                                        <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $category->name }}</p>
                                        @if($category->description)
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">{{ $category->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-slate-600 dark:text-slate-300 font-medium">
                                @if($hasTarget)
                                    Rp {{ number_format($target, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($collected, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($hasTarget)
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 h-2.5 bg-slate-100 dark:bg-slate-600 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500 ease-out {{ $percentage >= 100 ? 'bg-emerald-500' : ($percentage >= 50 ? 'bg-emerald-400' : 'bg-amber-400') }}" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold {{ $percentage >= 75 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-300' }} w-10 text-right">{{ $percentage }}%</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Tanpa target</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($category->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('community-cash.categories.edit', $category) }}" class="p-2 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('community-cash.categories.destroy', $category) }}'})" class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada kategori dana</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Mulai dengan menambahkan kategori pertama</p>
                                    <a href="{{ route('community-cash.categories.create') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Tambah Kategori
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-3">
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
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $category->name }}</p>
                            @if($category->is_active)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('community-cash.categories.edit', $category) }}" class="p-2 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('community-cash.categories.destroy', $category) }}'})" class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                @if($category->description)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">{{ $category->description }}</p>
                @endif
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <p class="text-xs text-slate-400 dark:text-slate-500">Target</p>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $hasTarget ? 'Rp '.number_format($target, 0, ',', '.') : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 dark:text-slate-500">Terkumpul</p>
                        <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($collected, 0, ',', '.') }}</p>
                    </div>
                </div>
                @if($hasTarget)
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-2 bg-slate-100 dark:bg-slate-600 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $percentage >= 100 ? 'bg-emerald-500' : ($percentage >= 50 ? 'bg-emerald-400' : 'bg-amber-400') }}" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-xs font-bold {{ $percentage >= 75 ? 'text-emerald-600' : 'text-slate-500' }}">{{ $percentage }}%</span>
                    </div>
                @endif
            </div>
            @endif
        @empty
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-slate-100 dark:border-slate-700 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada kategori dana</p>
                <a href="{{ route('community-cash.categories.create') }}" class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kategori
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-2">
        {{ $categories->links() }}
    </div>
</div>

<x-confirm-delete-modal />

@push('scripts')
<script>
function categoryPage() {
    return { loading: false };
}
</script>
@endpush

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.3s ease-out; }
</style>
</x-layouts.dashboard>
