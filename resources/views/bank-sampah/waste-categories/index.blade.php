<x-layouts.dashboard title="Kategori Sampah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header --}}
        <x-ui.page-header title="Kategori Sampah" description="Kelola daftar kategori, kode, dan grup sampah secara fleksibel">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Kategori Sampah</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <a href="{{ route('bank-sampah.waste-category-groups.index') }}" class="inline-flex items-center gap-1.5 bg-surface border border-border text-text px-3.5 py-2 rounded-radius-md text-body-sm font-semibold hover:bg-surface-muted shadow-subtle transition-subtle min-h-[44px]">
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Kelola Grup
                </a>
                <a href="{{ route('bank-sampah.waste-categories.import.template') }}" class="inline-flex items-center gap-1.5 bg-surface border border-border text-text px-3.5 py-2 rounded-radius-md text-body-sm font-semibold hover:bg-surface-muted shadow-subtle transition-subtle min-h-[44px]">
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Template
                </a>
                <a href="{{ route('bank-sampah.waste-categories.import') }}" class="inline-flex items-center gap-1.5 bg-surface border border-border text-text px-3.5 py-2 rounded-radius-md text-body-sm font-semibold hover:bg-surface-muted shadow-subtle transition-subtle min-h-[44px]">
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import Kategori
                </a>
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.waste-categories.create') }}'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kategori
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @if(session('success'))
            <div class="p-4 bg-primary-light border border-primary/30 text-primary rounded-radius-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Summary Cards Calculated in Controller --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-subtle">
                <p class="text-body-sm text-text-muted font-bold uppercase tracking-wider">Total Kategori</p>
                <p class="font-mono text-heading-lg font-bold text-text mt-1">{{ $totalCategories }}</p>
            </div>
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-subtle">
                <p class="text-body-sm text-primary font-bold uppercase tracking-wider">Grup Aktif</p>
                <p class="font-mono text-heading-lg font-bold text-text mt-1">{{ $totalActiveGroups }}</p>
            </div>
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-subtle">
                <p class="text-body-sm text-warning-text font-bold uppercase tracking-wider">Belum Dikategorikan</p>
                <p class="font-mono text-heading-lg font-bold text-text mt-1">{{ $uncategorizedCount }}</p>
            </div>
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-subtle">
                <p class="text-body-sm text-info-text font-bold uppercase tracking-wider">Grup Terbanyak</p>
                <p class="text-body font-bold text-text mt-2 truncate">{{ $mostPopulousGroupName }}</p>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-surface rounded-radius-lg shadow-subtle border border-border overflow-hidden transition-colors duration-300">
            <div class="p-4 border-b border-border bg-surface-muted/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <form action="{{ route('bank-sampah.waste-categories.index') }}" method="GET" class="flex flex-wrap items-center gap-2" id="filter-form">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <select name="waste_category_group_id" class="rounded-radius-md border-border bg-surface text-body-sm shadow-subtle focus:ring-primary focus:border-primary" onchange="document.getElementById('filter-form').submit()">
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
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface">
                        <tr class="border-b border-border bg-surface-muted/40 text-body-sm font-bold text-text-muted uppercase tracking-wider">
                            <th scope="col" class="py-3 px-4 text-left">Kode</th>
                            <th scope="col" class="py-3 px-4 text-left">Nama Kategori</th>
                            <th scope="col" class="py-3 px-4 text-left">Grup Kategori</th>
                            <th scope="col" class="py-3 px-4 text-left">Satuan</th>
                            <th scope="col" class="py-3 px-4 text-right">Harga Nasabah</th>
                            <th scope="col" class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($categories as $category)
                            <tr class="hover:bg-surface-muted/50 transition duration-150">
                                <td class="px-4 py-3.5 whitespace-nowrap font-mono text-body font-bold text-text">
                                    {{ $category->code ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-body font-bold text-text">
                                    {{ $category->name }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($category->wasteCategoryGroup)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm text-body-sm font-medium bg-info-light text-info-text border border-info/30">
                                            {{ $category->wasteCategoryGroup->name }}
                                        </span>
                                        @if(!$category->wasteCategoryGroup->is_active)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-caption font-medium bg-danger-light text-danger-text border border-danger/30 ml-1">
                                                Grup Nonaktif
                                            </span>
                                        @endif
                                    @elseif($category->category_group)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-body-sm font-medium bg-warning-light text-warning-text border border-danger/30">
                                            Legacy: {{ $category->category_group }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-caption font-medium bg-surface-muted text-text-muted border border-border">
                                            Belum Dikategorikan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-body-sm font-medium bg-surface-muted text-text border border-border">
                                        {{ $category->unit }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono text-body font-extrabold text-primary">
                                    Rp {{ number_format($category->member_price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right text-body font-semibold space-x-3">
                                    <a href="{{ route('bank-sampah.waste-categories.edit', $category) }}" class="text-primary hover:underline transition-subtle">Edit</a>
                                    <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.waste-categories.destroy', $category) }}'})" class="text-danger hover:underline transition-subtle">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <p class="text-body-sm text-text-muted">Belum ada kategori sampah yang ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MOBILE VIEW (Card List) -->
            <div class="block sm:hidden divide-y divide-border-subtle bg-surface">
                @forelse($categories as $category)
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-mono text-body-sm font-semibold text-text-muted">{{ $category->code ?? '-' }}</p>
                                <h4 class="text-body font-bold text-text mt-0.5">{{ $category->name }}</h4>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-body-sm font-medium bg-surface-muted text-text border border-border">
                                    {{ $category->unit }}
                                </span>
                                <p class="font-mono text-body-sm font-bold text-primary mt-1">
                                    Rp {{ number_format($category->member_price, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-1.5">
                            @if($category->wasteCategoryGroup)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm text-caption font-medium bg-info-light text-info-text border border-info/30">
                                    {{ $category->wasteCategoryGroup->name }}
                                </span>
                                @if(!$category->wasteCategoryGroup->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm text-caption font-medium bg-danger-light text-danger-text border border-danger/30">
                                        Grup Nonaktif
                                    </span>
                                @endif
                            @elseif($category->category_group)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm text-caption font-medium bg-warning-light text-warning-text border border-danger/30">
                                    Legacy: {{ $category->category_group }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm text-caption font-medium bg-surface-muted text-text-muted border border-border">
                                    Belum Dikategorikan
                                </span>
                            @endif
                        </div>

                        <div class="flex justify-end gap-3 pt-2 text-body-sm font-semibold">
                            <a href="{{ route('bank-sampah.waste-categories.edit', $category) }}" class="text-primary hover:underline transition-subtle">Edit</a>
                            <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.waste-categories.destroy', $category) }}'})" class="text-danger hover:underline transition-subtle">Hapus</button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-text-muted text-body-sm">
                        Belum ada kategori sampah yang ditemukan.
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($categories->hasPages())
                <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>

    <x-confirm-delete-modal />
</x-layouts.dashboard>
