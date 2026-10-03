<x-layouts.dashboard title="Harga Sampah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-text">Harga Sampah</h2>
                <p class="text-sm text-text-muted mt-1">Kelola harga beli dari nasabah dan harga jual ke agregator (margin keuntungan)</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('bank-sampah.waste-prices.history') }}" class="inline-flex items-center gap-2 bg-surface border border-border text-text px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-surface-muted shadow-sm transition">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Riwayat Harga
                </a>
                <a href="{{ route('bank-sampah.waste-prices.import') }}" class="inline-flex items-center gap-2 bg-surface border border-border text-text px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-surface-muted shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import Harga
                </a>
                <a href="{{ route('bank-sampah.waste-prices.create') }}" class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-primary-hover shadow-sm hover:shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Harga
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-primary-light border border-primary/30 text-primary rounded-radius-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-xs text-text-muted font-semibold uppercase tracking-wide">Total Harga Aktif</p>
                <p class="text-2xl font-bold text-text dark:text-white mt-1">{{ $totalActivePrices ?? 0 }}</p>
            </div>
            @foreach($groups as $g)
                <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex px-1.5 py-0.5 text-caption font-mono font-bold bg-surface-muted text-text-muted dark:text-text rounded border border-border">{{ $g->code }}</span>
                    </div>
                    <p class="text-xs font-semibold text-text mt-1 truncate" title="{{ $g->name }}">{{ $g->name }}</p>
                    <p class="text-xl font-bold text-text dark:text-white mt-1">
                        {{ $g->active_prices_count }} / {{ $g->waste_categories_count }}
                        <span class="block text-caption text-text-muted font-normal mt-0.5">Harga / Kategori</span>
                    </p>
                </div>
            @endforeach
        </div>

        <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden transition-colors duration-300">
            <div class="p-4 border-b border-border bg-surface-muted/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <form action="{{ route('bank-sampah.waste-prices.index') }}" method="GET" class="flex items-center gap-2" id="filter-form">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <select name="waste_category_group_id" class="rounded-radius-md border-border bg-surface text-sm shadow-sm focus:ring-primary focus:border-primary" onchange="document.getElementById('filter-form').submit()">
                            <option value="">Semua Grup</option>
                            <option value="uncategorized" {{ request('waste_category_group_id') === 'uncategorized' ? 'selected' : '' }}>Belum Dikategorikan</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" {{ request('waste_category_group_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="w-full sm:max-w-md">
                    <x-table-toolbar :search="$search ?? ''" placeholder="Cari harga berdasarkan kategori..." />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Kategori Sampah</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Grup</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Agregator</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">Harga Beli Nasabah</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">Harga Jual Agregator</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">
                                Margin
                                <span class="block text-caption text-text-muted font-normal mt-0.5 normal-case tracking-normal">Margin Bank Sampah</span>
                            </th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($categories as $category)
                            @if($category->wastePrices->isNotEmpty())
                                @foreach($category->wastePrices as $price)
                                    <tr class="hover:bg-surface-muted/50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-primary-light flex items-center justify-center text-primary">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-text">{{ $category->name ?? '-' }}</div>
                                                    <div class="text-xs font-mono text-text-muted mt-0.5">{{ $category->code ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($category->wasteCategoryGroup)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-xs font-medium bg-info-light text-info-text border border-info/30">
                                                    {{ $category->wasteCategoryGroup->name }}
                                                </span>
                                            @elseif($category->category_group)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-xs font-medium bg-warning-light text-warning-text border border-danger/30">
                                                    {{ $category->category_group }} (Legacy)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-caption font-medium bg-surface-muted text-text-muted border border-border">
                                                    Belum Dikategorikan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-text dark:text-text flex items-center gap-2">
                                                <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                {{ $price->collector->name ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="text-sm font-medium text-text">Rp {{ number_format($category->member_price ?? 0, 0, ',', '.') }} <span class="text-xs text-text-muted font-normal">/ {{ $category->unit ?? 'kg' }}</span></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="text-sm font-semibold text-primary">Rp {{ number_format($price->collector_price, 0, ',', '.') }} <span class="text-xs text-text-muted font-normal">/ {{ $category->unit ?? 'kg' }}</span></div>
                                            @php
                                                $latestHistory = $price->histories->first();
                                            @endphp
                                            @if($latestHistory)
                                                <div class="text-caption text-text-muted mt-0.5" title="Diubah oleh {{ $latestHistory->changedBy->name ?? 'Admin' }} ({{ $latestHistory->adminDevice->device_name ?? 'Device' }})">
                                                    {{ $latestHistory->changed_at ? $latestHistory->changed_at->format('d/m/y H:i') : '' }} &bull; {{ $latestHistory->source }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            @php
                                                $margin = $price->collector_price - ($category->member_price ?? 0);
                                            @endphp
                                            <div class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-xs font-medium {{ $margin > 0 ? 'bg-primary-light text-primary' : 'bg-surface-muted text-text' }}">
                                                Rp {{ number_format($margin, 0, ',', '.') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <a href="{{ route('bank-sampah.waste-prices.history', ['collector_id' => $price->collector_id, 'waste_category_id' => $price->waste_category_id]) }}" class="text-info-text hover:underline transition-subtle">Riwayat</a>
                                            <a href="{{ route('bank-sampah.waste-prices.edit', $price) }}" class="text-primary hover:underline transition-subtle">Edit</a>
                                            <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.waste-prices.destroy', $price) }}'})" class="text-danger hover:underline transition-subtle">Hapus</button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="hover:bg-surface-muted/50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-primary-light flex items-center justify-center text-primary">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-text">{{ $category->name ?? '-' }}</div>
                                                <div class="text-xs font-mono text-text-muted mt-0.5">{{ $category->code ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($category->wasteCategoryGroup)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-xs font-medium bg-info-light text-info-text border border-info/30">
                                                {{ $category->wasteCategoryGroup->name }}
                                            </span>
                                        @elseif($category->category_group)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-xs font-medium bg-warning-light text-warning-text border border-danger/30">
                                                {{ $category->category_group }} (Legacy)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-caption font-medium bg-surface-muted text-text-muted border border-border">
                                                Belum Dikategorikan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-text-muted">—</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm font-medium text-text">Rp {{ number_format($category->member_price ?? 0, 0, ',', '.') }} <span class="text-xs text-text-muted font-normal">/ {{ $category->unit ?? 'kg' }}</span></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm text-text-muted">—</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm text-text-muted">—</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('bank-sampah.waste-prices.create', ['waste_category_id' => $category->id]) }}" class="text-primary hover:underline transition-subtle">Tambah Harga</a>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-surface-muted rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <p class="text-text-muted text-sm">Belum ada data harga sampah yang ditambahkan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages())
                <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>

    <x-confirm-delete-modal />
</x-layouts.dashboard>
