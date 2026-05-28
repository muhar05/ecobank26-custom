<x-layouts.dashboard title="Kategori Sampah">
    <div class="space-y-6">
        <!-- Page Header & CTAs -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Kategori Sampah</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola daftar kategori, kode, dan grup sampah secara fleksibel</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('bank-sampah.waste-category-groups.index') }}" class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Kelola Grup
                </a>
                <a href="{{ route('bank-sampah.waste-categories.import.template') }}" class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Template
                </a>
                <a href="{{ route('bank-sampah.waste-categories.import') }}" class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import Kategori
                </a>
                <a href="{{ route('bank-sampah.waste-categories.create') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kategori
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Summary Cards Calculated in Controller --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wide">Total Kategori</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $totalCategories }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold uppercase tracking-wide">Grup Aktif</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $totalActiveGroups }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold uppercase tracking-wide">Belum Dikategorikan</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $uncategorizedCount }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-blue-600 dark:text-blue-400 font-semibold uppercase tracking-wide">Grup Terbanyak</p>
                <p class="text-base font-bold text-slate-900 dark:text-white mt-2 truncate">{{ $mostPopulousGroupName }}</p>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <form action="{{ route('bank-sampah.waste-categories.index') }}" method="GET" class="flex flex-wrap items-center gap-2" id="filter-form">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <select name="waste_category_group_id" class="rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500" onchange="document.getElementById('filter-form').submit()">
                            <option value="">Semua Grup Kategori</option>
                            <option value="uncategorized" {{ request('waste_category_group_id') === 'uncategorized' ? 'selected' : '' }}>Belum Dikategorikan</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" {{ request('waste_category_group_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="w-full sm:max-w-md">
                    <x-table-toolbar :search="$search ?? ''" placeholder="Cari nama atau kode kategori..." />
                </div>
            </div>

            <!-- DESKTOP VIEW (Table) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-white dark:bg-slate-900">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kode</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Kategori</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Grup Kategori</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Satuan</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($categories as $category)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $category->code ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 dark:text-slate-100">
                                    {{ $category->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($category->wasteCategoryGroup)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                            {{ $category->wasteCategoryGroup->name }}
                                        </span>
                                        @if(!$category->wasteCategoryGroup->is_active)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-medium bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800 ml-1">
                                                Grup Nonaktif
                                            </span>
                                        @endif
                                    @elseif($category->category_group)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                            Legacy: {{ $category->category_group }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                            Belum Dikategorikan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $category->unit }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('bank-sampah.waste-categories.edit', $category) }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-300 transition">Edit</a>
                                    <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.waste-categories.destroy', $category) }}'})" class="text-rose-600 dark:text-rose-400 hover:text-rose-900 dark:hover:text-rose-300 transition">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada kategori sampah yang ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MOBILE VIEW (Card List) -->
            <div class="block sm:hidden divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                @forelse($categories as $category)
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-mono text-xs font-semibold text-slate-400">{{ $category->code ?? '-' }}</p>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 mt-0.5">{{ $category->name }}</h4>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ $category->unit }}
                            </span>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-1.5">
                            @if($category->wasteCategoryGroup)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800">
                                    {{ $category->wasteCategoryGroup->name }}
                                </span>
                                @if(!$category->wasteCategoryGroup->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-medium bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-800">
                                        Grup Nonaktif
                                    </span>
                                @endif
                            @elseif($category->category_group)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                    Legacy: {{ $category->category_group }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    Belum Dikategorikan
                                </span>
                            @endif
                        </div>

                        <div class="flex justify-end gap-3 pt-2 text-xs font-semibold">
                            <a href="{{ route('bank-sampah.waste-categories.edit', $category) }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-300">Edit</a>
                            <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.waste-categories.destroy', $category) }}'})" class="text-rose-600 dark:text-rose-400 hover:text-rose-900 dark:hover:text-rose-300">Hapus</button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                        Belum ada kategori sampah yang ditemukan.
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($categories->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>

    <x-confirm-delete-modal />
</x-layouts.dashboard>
