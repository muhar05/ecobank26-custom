<x-layouts.dashboard title="Harga Agregator">
    @php
        $selectedCollector = $collectors->firstWhere('id', $selectedCollectorId) ?? $collectors->first();
        $compareBCollector = $collectors->firstWhere('id', $compareBId) ?? ($collectors->count() > 1 ? $collectors[1] : null);

        $categoriesData = $categories->map(function ($cat) {
            $pricesMap = [];
            foreach ($cat->wastePrices as $wp) {
                $pricesMap[$wp->collector_id] = [
                    'id' => $wp->id,
                    'collector_id' => $wp->collector_id,
                    'collector_name' => $wp->collector->name ?? '-',
                    'price' => (float) $wp->collector_price,
                    'formatted_price' => 'Rp ' . number_format($wp->collector_price, 0, ',', '.'),
                    'updated_at' => $wp->updated_at ? $wp->updated_at->format('d M Y H:i') : '-',
                    'updated_at_diff' => $wp->updated_at ? $wp->updated_at->diffForHumans() : '',
                    'edit_url' => route('bank-sampah.waste-prices.edit', $wp),
                    'history_url' => route('bank-sampah.waste-prices.history', ['collector_id' => $wp->collector_id, 'waste_category_id' => $cat->id]),
                    'delete_url' => route('bank-sampah.waste-prices.destroy', $wp),
                ];
            }

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'code' => $cat->code ?? '-',
                'unit' => $cat->unit ?? 'kg',
                'member_price' => (float) ($cat->member_price ?? 0),
                'formatted_member_price' => 'Rp ' . number_format($cat->member_price ?? 0, 0, ',', '.'),
                'group_name' => $cat->wasteCategoryGroup->name ?? ($cat->category_group ?? ''),
                'group_id' => $cat->waste_category_group_id,
                'prices' => $pricesMap,
                'create_price_url' => route('bank-sampah.waste-prices.create', ['waste_category_id' => $cat->id]),
            ];
        })->values()->all();

        $collectorsData = $collectors->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
        ])->values()->all();
    @endphp

    <div x-data="wastePricesManager(@js($categoriesData), @js($collectorsData), '{{ $selectedCollector?->id ?? '' }}', '{{ $compareBCollector?->id ?? '' }}', '{{ $search ?? '' }}')"
         class="space-y-6 max-w-7xl mx-auto pb-12">

        {{-- Page Header --}}
        <x-ui.page-header title="Harga Agregator" description="Bandingkan dan kelola harga jual material ke setiap agregator.">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Harga Agregator</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <a href="{{ route('bank-sampah.waste-prices.history') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-radius-md border border-border bg-surface text-text hover:bg-surface-muted text-body-sm font-semibold transition-subtle shadow-subtle min-h-[44px]">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Riwayat Harga
                </a>
                <a href="{{ route('bank-sampah.waste-prices.import') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-radius-md border border-border bg-surface text-text hover:bg-surface-muted text-body-sm font-semibold transition-subtle shadow-subtle min-h-[44px]">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Harga
                </a>
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.waste-prices.create') }}'">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    + Tambah Harga
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Success Flash Alert --}}
        @if(session('success'))
            <div class="p-4 rounded-radius-md bg-success-light border border-success/30 flex items-center gap-3 text-success-text shadow-subtle">
                <svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-body-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Mode Switcher Tabs (Katalog Agregator vs Bandingkan Agregator) --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border pb-1">
            <div class="inline-flex items-center gap-1.5 p-1 bg-surface-muted rounded-radius-md border border-border">
                <button type="button"
                        @click="setMode('catalog')"
                        :class="mode === 'catalog' ? 'bg-surface text-text font-bold shadow-subtle' : 'text-text-muted hover:text-text font-semibold'"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-radius-sm text-body-sm transition-subtle cursor-pointer min-h-[44px]">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Katalog per Agregator</span>
                </button>

                <button type="button"
                        @click="setMode('compare')"
                        :class="mode === 'compare' ? 'bg-surface text-text font-bold shadow-subtle' : 'text-text-muted hover:text-text font-semibold'"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-radius-sm text-body-sm transition-subtle cursor-pointer min-h-[44px]">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Bandingkan Agregator</span>
                    <span class="px-1.5 py-0.2 rounded text-caption bg-primary-light text-primary font-bold">Compare</span>
                </button>
            </div>

            <div class="text-caption text-text-muted">
                Total Master Material: <span class="font-mono font-bold text-text">{{ $totalCategories }}</span> &bull; Total Harga Terdaftar: <span class="font-mono font-bold text-primary">{{ $totalActivePrices }}</span>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODE 1: KATALOG PER AGREGATOR                                             --}}
        {{-- ========================================================================= --}}
        <div x-show="mode === 'catalog'" class="space-y-6">
            @if($collectors->isEmpty())
                <div class="p-6 rounded-radius-lg border border-warning/30 bg-warning-light/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <p class="font-bold text-body text-warning-text">Belum ada mitra agregator terdaftar</p>
                        <p class="text-body-sm text-text-muted">Daftarkan mitra agregator terlebih dahulu agar dapat mengatur dan membandingkan harga lapak.</p>
                    </div>
                    <a href="{{ route('bank-sampah.collectors.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-radius-md bg-primary text-white text-body-sm font-semibold hover:bg-primary-hover shadow-subtle transition-subtle shrink-0">
                        + Tambah Agregator Baru
                    </a>
                </div>
            @else
                {{-- Selector Agregator & KPI Summary Card --}}
                <div class="p-4 sm:p-5 rounded-radius-lg bg-surface border border-border shadow-subtle flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                    {{-- Agregator Selector --}}
                    <div class="flex items-center gap-3.5 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-heading-sm shrink-0 shadow-subtle">
                            <span x-text="activeCollectorName().substr(0, 1).toUpperCase()">{{ strtoupper(substr($selectedCollector?->name ?? 'A', 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">
                                Agregator Aktif
                            </span>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <select x-model.number="activeCollectorId"
                                        @change="onCollectorChange()"
                                        aria-label="Pilih Agregator Aktif"
                                        class="font-bold text-body sm:text-heading-sm text-text bg-surface-muted/60 border border-border rounded-radius-md py-1.5 pl-3 pr-8 focus:border-primary focus:ring-1 focus:ring-primary transition-subtle cursor-pointer min-h-[44px]">
                                    <template x-for="col in collectors" :key="col.id">
                                        <option :value="col.id" x-text="col.name" :selected="col.id == activeCollectorId"></option>
                                    </template>
                                </select>
                                <a :href="'{{ url('bank-sampah/collectors') }}/' + activeCollectorId"
                                   class="text-caption font-semibold text-primary hover:underline px-2 py-1 inline-flex items-center">
                                    Lihat Profil Agregator &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Summary KPIs for Selected Agregator --}}
                    <div class="grid grid-cols-3 gap-2 sm:gap-3 shrink-0">
                        <div class="px-3.5 py-2.5 rounded-radius-md bg-surface-muted/60 border border-border flex flex-col justify-center">
                            <span class="text-caption text-text-muted font-bold uppercase">Sudah Ada Harga</span>
                            <span class="font-mono text-heading-sm sm:text-heading-md font-extrabold text-success mt-0.5" x-text="activeCollectorPricedCount()">
                                {{ $categories->filter(fn($c) => $c->wastePrices->contains('collector_id', $selectedCollector?->id))->count() }}
                            </span>
                        </div>
                        <div class="px-3.5 py-2.5 rounded-radius-md bg-surface-muted/60 border border-border flex flex-col justify-center">
                            <span class="text-caption text-text-muted font-bold uppercase">Belum Ada Harga</span>
                            <span class="font-mono text-heading-sm sm:text-heading-md font-extrabold text-warning mt-0.5" x-text="activeCollectorUnpricedCount()">
                                {{ $categories->filter(fn($c) => !$c->wastePrices->contains('collector_id', $selectedCollector?->id))->count() }}
                            </span>
                        </div>
                        <div class="px-3.5 py-2.5 rounded-radius-md bg-surface-muted/60 border border-border flex flex-col justify-center">
                            <span class="text-caption text-text-muted font-bold uppercase">Total Material</span>
                            <span class="font-mono text-heading-sm sm:text-heading-md font-extrabold text-text mt-0.5" x-text="materials.length">
                                {{ $categories->count() }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Filter & Search Toolbar --}}
            <x-ui.card variant="bordered" class="p-4 sm:p-5">
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                    {{-- Status Chips Filter --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-caption font-bold text-text-muted uppercase tracking-wider mr-1">Status:</span>

                        <button type="button"
                                @click="statusFilter = 'ALL'"
                                :class="statusFilter === 'ALL' ? 'bg-primary text-white font-bold shadow-subtle' : 'bg-surface-muted text-text hover:bg-surface border border-border font-semibold'"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-radius-md text-body-sm transition-subtle cursor-pointer min-h-[44px]">
                            <span>Semua Material</span>
                            <span class="font-mono text-caption px-1.5 py-0.2 rounded"
                                  :class="statusFilter === 'ALL' ? 'bg-white/20 text-white' : 'bg-surface text-text-muted'"
                                  x-text="materials.length">{{ $categories->count() }}</span>
                        </button>

                        <button type="button"
                                @click="statusFilter = 'PRICED'"
                                :class="statusFilter === 'PRICED' ? 'bg-success text-white font-bold shadow-subtle' : 'bg-surface-muted text-text hover:bg-surface border border-border font-semibold'"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-radius-md text-body-sm transition-subtle cursor-pointer min-h-[44px]">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Sudah Ada Harga</span>
                            <span class="font-mono text-caption px-1.5 py-0.2 rounded"
                                  :class="statusFilter === 'PRICED' ? 'bg-white/20 text-white' : 'bg-surface text-text-muted'"
                                  x-text="activeCollectorPricedCount()">
                                {{ $categories->filter(fn($c) => $c->wastePrices->contains('collector_id', $selectedCollector?->id))->count() }}
                            </span>
                        </button>

                        <button type="button"
                                @click="statusFilter = 'UNPRICED'"
                                :class="statusFilter === 'UNPRICED' ? 'bg-warning text-white font-bold shadow-subtle' : 'bg-surface-muted text-text hover:bg-surface border border-border font-semibold'"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-radius-md text-body-sm transition-subtle cursor-pointer min-h-[44px]">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Belum Ada Harga</span>
                            <span class="font-mono text-caption px-1.5 py-0.2 rounded"
                                  :class="statusFilter === 'UNPRICED' ? 'bg-white/20 text-white' : 'bg-surface text-text-muted'"
                                  x-text="activeCollectorUnpricedCount()">
                                {{ $categories->filter(fn($c) => !$c->wastePrices->contains('collector_id', $selectedCollector?->id))->count() }}
                            </span>
                        </button>
                    </div>

                    {{-- Search Input (Reactive + Sync with Backend Query) --}}
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1 min-w-[240px]">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </span>
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="Cari material..."
                                   aria-label="Cari nama material atau kode"
                                   class="w-full text-body rounded-radius-md border border-border bg-surface text-text pl-10 pr-4 py-2 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                        </div>

                        <template x-if="searchQuery || statusFilter !== 'ALL'">
                            <button type="button"
                                    @click="resetFilters()"
                                    class="text-body-sm font-semibold text-text-muted hover:text-danger px-2 py-2 transition-subtle inline-flex items-center min-h-[44px]">
                                Reset
                            </button>
                        </template>
                    </div>
                </div>
            </x-ui.card>

            {{-- Main Table Card --}}
            <x-ui.card id="table-section" variant="bordered" class="p-0 overflow-hidden space-y-0 scroll-mt-20">
                {{-- Desktop Table (md+) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted/40 text-caption font-bold text-text-muted uppercase tracking-wider">
                                <th scope="col" class="py-3 px-4 w-12 text-center">No</th>
                                <th scope="col" class="py-3 px-4 min-w-[220px]">Material / Jenis Sampah</th>
                                <th scope="col" class="py-3 px-4 min-w-[190px] text-right">Harga Lapak</th>
                                <th scope="col" class="py-3 px-4 w-24 text-center">Satuan</th>
                                <th scope="col" class="py-3 px-4 w-40 text-center">Status</th>
                                <th scope="col" class="py-3 px-4 w-36">Modified At</th>
                                <th scope="col" class="py-3 px-4 w-44 text-right">Aksi</th>
                            </tr>
                        </thead>

                        {{-- SSR Fallback Table Body (Visible before Alpine hydrates or for non-JS/PHPUnit) --}}
                        <tbody x-show="!alpineReady" class="divide-y divide-border-subtle">
                            @forelse($categories as $categoryIndex => $cat)
                                @php
                                    $activePrice = $selectedCollector ? $cat->wastePrices->firstWhere('collector_id', $selectedCollector->id) : $cat->wastePrices->first();
                                    $margin = $activePrice ? ((float) $activePrice->collector_price - (float) ($cat->member_price ?? 0)) : null;
                                @endphp
                                <tr class="hover:bg-surface-muted/50 transition-subtle">
                                    <td class="py-3.5 px-4 text-center font-mono text-body-sm text-text-muted">
                                        {{ $categoryIndex + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-body-sm shrink-0 shadow-subtle">
                                                {{ strtoupper(substr($cat->name ?? 'M', 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <span class="text-body font-bold text-text block truncate">{{ $cat->name ?? '-' }}</span>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="font-mono text-body-sm text-text-muted font-medium">{{ $cat->code ?? '-' }}</span>
                                                    @if($cat->wasteCategoryGroup)
                                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-caption font-medium bg-info-light text-info-text border border-info/30">
                                                            {{ $cat->wasteCategoryGroup->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-caption text-text-muted font-mono mt-0.5">
                                                    Acuan Beli Master: Rp {{ number_format($cat->member_price ?? 0, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        @if($activePrice)
                                            <div class="font-mono text-body font-extrabold text-primary">
                                                Rp {{ number_format($activePrice->collector_price, 0, ',', '.') }}
                                            </div>
                                            <div class="font-mono text-caption text-text-muted mt-0.5">
                                                Margin <span class="font-semibold {{ $margin > 0 ? 'text-primary' : 'text-text-muted' }}">Rp {{ number_format($margin, 0, ',', '.') }}</span>
                                            </div>
                                        @else
                                            <div class="text-body font-bold text-text-muted">&mdash;</div>
                                            <div class="text-caption text-text-muted font-mono mt-0.5">Belum ada harga</div>
                                        @endif

                                        {{-- Other Aggregators Prices for Testing & Operational Discovery --}}
                                        @foreach($cat->wastePrices as $otherP)
                                            @if(!$activePrice || $otherP->id !== $activePrice->id)
                                                <div class="text-caption text-text-muted mt-1">
                                                    {{ $otherP->collector->name ?? '-' }}: <span class="font-mono font-semibold text-text">Rp {{ number_format($otherP->collector_price, 0, ',', '.') }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="font-mono text-caption uppercase px-2 py-0.5 rounded bg-surface-muted border border-border text-text font-semibold">
                                            {{ $cat->unit ?? 'kg' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        @if($activePrice)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-success-light text-success-text border border-success/30 text-caption font-semibold">
                                                <svg class="w-3.5 h-3.5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Sudah Ada Harga</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-warning-light text-warning-text border border-warning/30 text-caption font-semibold">
                                                <svg class="w-3.5 h-3.5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>Belum Ada Harga</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="text-body-sm text-text font-medium">
                                            {{ $activePrice?->updated_at ? $activePrice->updated_at->format('d M Y H:i') : '-' }}
                                        </div>
                                        @if($activePrice?->updated_at)
                                            <div class="text-caption text-text-muted">
                                                {{ $activePrice->updated_at->diffForHumans() }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        @if($activePrice)
                                            <div class="inline-flex items-center justify-end gap-3 text-body-sm font-semibold">
                                                <a href="{{ route('bank-sampah.waste-prices.history', ['collector_id' => $activePrice->collector_id, 'waste_category_id' => $cat->id]) }}" class="text-text-muted hover:text-text py-1">Riwayat</a>
                                                <a href="{{ route('bank-sampah.waste-prices.edit', $activePrice) }}" class="text-primary hover:underline py-1">Edit</a>
                                                <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.waste-prices.destroy', $activePrice) }}'})" class="text-danger hover:underline cursor-pointer py-1">Hapus</button>
                                            </div>
                                        @else
                                            <a href="{{ route('bank-sampah.waste-prices.create', ['waste_category_id' => $cat->id, 'collector_id' => $selectedCollector?->id]) }}" class="inline-flex items-center gap-1 text-primary hover:underline font-semibold text-body-sm min-h-[44px]">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                <span>Tambah Harga</span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-text-muted">
                                        Belum ada data harga sampah yang ditambahkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        {{-- Alpine Reactive Table Body (Active when Alpine hydrates) --}}
                        <tbody x-show="alpineReady" class="divide-y divide-border-subtle">
                            <template x-for="(mat, idx) in filteredCatalogMaterials()" :key="mat.id">
                                <tr class="hover:bg-surface-muted/50 transition-subtle">
                                    {{-- No --}}
                                    <td class="py-3.5 px-4 text-center font-mono text-body-sm text-text-muted" x-text="idx + 1"></td>

                                    {{-- Material --}}
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-body-sm shrink-0 shadow-subtle"
                                                 x-text="mat.name.substr(0, 1).toUpperCase()">
                                            </div>
                                            <div class="min-w-0">
                                                <span class="text-body font-bold text-text block truncate" x-text="mat.name"></span>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="font-mono text-body-sm text-text-muted font-medium" x-text="mat.code"></span>
                                                    <template x-if="mat.group_name">
                                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-caption font-medium bg-info-light text-info-text border border-info/30" x-text="mat.group_name"></span>
                                                    </template>
                                                </div>
                                                <div class="text-caption text-text-muted font-mono mt-0.5" x-text="'Acuan Beli Master: ' + mat.formatted_member_price"></div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Harga Lapak --}}
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <template x-if="hasPrice(mat)">
                                            <div>
                                                <div class="font-mono text-body font-extrabold text-primary" x-text="getPrice(mat).formatted_price"></div>
                                                <div class="font-mono text-caption text-text-muted mt-0.5">
                                                    Margin <span class="font-semibold" :class="getMargin(mat) > 0 ? 'text-primary' : 'text-text-muted'" x-text="formatRp(getMargin(mat))"></span>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="!hasPrice(mat)">
                                            <div>
                                                <div class="text-body font-bold text-text-muted">&mdash;</div>
                                                <template x-if="otherPrices(mat).length > 0">
                                                    <div class="text-caption font-semibold text-primary mt-0.5" x-text="'Tersedia di ' + otherPrices(mat)[0].collector_name + ' (' + otherPrices(mat)[0].formatted_price + ')'"></div>
                                                </template>
                                                <template x-if="otherPrices(mat).length === 0">
                                                    <div class="text-caption text-text-muted mt-0.5">Belum ada harga di agregator manapun</div>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- If Priced but other collectors also offer --}}
                                        <template x-if="hasPrice(mat) && otherPrices(mat).length > 0">
                                            <div class="text-caption text-text-muted mt-1" x-text="'Juga ada di ' + otherPrices(mat)[0].collector_name + ' (' + otherPrices(mat)[0].formatted_price + ')'"></div>
                                        </template>
                                    </td>

                                    {{-- Satuan --}}
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="font-mono text-caption uppercase px-2 py-0.5 rounded bg-surface-muted border border-border text-text font-semibold" x-text="mat.unit"></span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <template x-if="hasPrice(mat)">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-success-light text-success-text border border-success/30 text-caption font-semibold">
                                                <svg class="w-3.5 h-3.5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Sudah Ada Harga</span>
                                            </span>
                                        </template>
                                        <template x-if="!hasPrice(mat)">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-warning-light text-warning-text border border-warning/30 text-caption font-semibold">
                                                <svg class="w-3.5 h-3.5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>Belum Ada Harga</span>
                                            </span>
                                        </template>
                                    </td>

                                    {{-- Modified At --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <template x-if="hasPrice(mat)">
                                            <div>
                                                <div class="text-body-sm text-text font-medium" x-text="getPrice(mat).updated_at"></div>
                                                <div class="text-caption text-text-muted" x-text="getPrice(mat).updated_at_diff"></div>
                                            </div>
                                        </template>
                                        <template x-if="!hasPrice(mat)">
                                            <span class="text-text-muted text-body-sm">&mdash;</span>
                                        </template>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <template x-if="hasPrice(mat)">
                                            <div class="inline-flex items-center justify-end gap-3 text-body-sm font-semibold">
                                                <a :href="getPrice(mat).history_url" class="text-text-muted hover:text-text py-1">Riwayat</a>
                                                <a :href="getPrice(mat).edit_url" class="text-primary hover:underline py-1">Edit</a>
                                                <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: getPrice(mat).delete_url})" class="text-danger hover:underline cursor-pointer py-1">Hapus</button>
                                            </div>
                                        </template>
                                        <template x-if="!hasPrice(mat)">
                                            <a :href="mat.create_price_url + (activeCollectorId ? '&collector_id=' + activeCollectorId : '')" class="inline-flex items-center gap-1 text-primary hover:underline font-semibold text-body-sm min-h-[44px]">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                <span>+ Tambah Harga</span>
                                            </a>
                                        </template>
                                    </td>
                                </tr>
                            </template>

                            {{-- Empty Search or Filter Result --}}
                            <template x-if="filteredCatalogMaterials().length === 0">
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <div class="w-12 h-12 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-heading-sm font-bold text-text">Material Tidak Ditemukan</h3>
                                                <p class="text-body-sm text-text-muted mt-1 max-w-md mx-auto">
                                                    Tidak ada material yang cocok dengan filter status atau kata kunci yang dimasukkan.
                                                </p>
                                            </div>
                                            <div class="pt-2">
                                                <button type="button" @click="resetFilters()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-radius-md bg-surface border border-border text-body font-semibold text-text hover:bg-surface-muted transition-subtle cursor-pointer">
                                                    Reset Filter &amp; Pencarian
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Adaptive Cards (< md) --}}
                <div class="block md:hidden divide-y divide-border-subtle p-3 space-y-3">
                    <template x-for="(mat, idx) in filteredCatalogMaterials()" :key="mat.id">
                        <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                            {{-- Material Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-body shrink-0 shadow-subtle"
                                         x-text="mat.name.substr(0, 1).toUpperCase()">
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-body font-bold text-text truncate" x-text="mat.name"></h4>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="font-mono text-body-sm text-text-muted font-medium" x-text="mat.code"></span>
                                            <template x-if="mat.group_name">
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-caption font-medium bg-info-light text-info-text border border-info/30" x-text="mat.group_name"></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <span class="font-mono text-body-sm text-text-muted font-semibold shrink-0" x-text="'#' + (idx + 1)"></span>
                            </div>

                            {{-- Price & Status Block --}}
                            <div class="p-3 bg-surface-muted rounded-radius-md border border-border-subtle space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-caption font-bold uppercase text-text-muted">Status Agregator</span>
                                    <template x-if="hasPrice(mat)">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-radius-sm bg-success-light text-success-text border border-success/30 text-caption font-semibold">
                                            <svg class="w-3.5 h-3.5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Sudah Ada Harga</span>
                                        </span>
                                    </template>
                                    <template x-if="!hasPrice(mat)">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-radius-sm bg-warning-light text-warning-text border border-warning/30 text-caption font-semibold">
                                            <svg class="w-3.5 h-3.5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>Belum Ada Harga</span>
                                        </span>
                                    </template>
                                </div>

                                <div class="flex items-baseline justify-between gap-2 border-t border-border-subtle pt-2">
                                    <span class="text-caption font-bold uppercase text-primary">Harga Lapak</span>
                                    <div class="text-right">
                                        <template x-if="hasPrice(mat)">
                                            <div>
                                                <span class="font-mono text-heading-sm font-extrabold text-primary" x-text="getPrice(mat).formatted_price"></span>
                                                <span class="font-mono text-caption text-text-muted" x-text="'/ ' + mat.unit"></span>
                                            </div>
                                        </template>
                                        <template x-if="!hasPrice(mat)">
                                            <span class="text-body font-bold text-text-muted">&mdash; Belum ada</span>
                                        </template>
                                    </div>
                                </div>

                                <template x-if="hasPrice(mat)">
                                    <div class="flex items-center justify-between gap-2 text-caption text-text-muted font-mono pt-1">
                                        <span x-text="'Acuan ' + mat.formatted_member_price"></span>
                                        <span>Margin: <strong :class="getMargin(mat) > 0 ? 'text-primary' : 'text-text-muted'" x-text="formatRp(getMargin(mat))"></strong></span>
                                    </div>
                                </template>

                                <template x-if="!hasPrice(mat) && otherPrices(mat).length > 0">
                                    <div class="text-caption text-primary font-semibold pt-1" x-text="'Tersedia di ' + otherPrices(mat)[0].collector_name + ' (' + otherPrices(mat)[0].formatted_price + ')'"></div>
                                </template>
                            </div>

                            {{-- Mobile Actions Footer --}}
                            <div class="flex items-center justify-between pt-2 border-t border-border-subtle text-caption text-text-muted">
                                <span x-text="hasPrice(mat) ? getPrice(mat).updated_at : '-'"></span>
                                <div class="flex items-center gap-3 text-body-sm font-semibold">
                                    <template x-if="hasPrice(mat)">
                                        <div class="flex items-center gap-3">
                                            <a :href="getPrice(mat).history_url" class="text-text-muted hover:text-text py-2">Riwayat</a>
                                            <a :href="getPrice(mat).edit_url" class="text-primary hover:underline py-2">Edit</a>
                                            <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: getPrice(mat).delete_url})" class="text-danger hover:underline cursor-pointer py-2">Hapus</button>
                                        </div>
                                    </template>
                                    <template x-if="!hasPrice(mat)">
                                        <a :href="mat.create_price_url + (activeCollectorId ? '&collector_id=' + activeCollectorId : '')" class="text-primary hover:underline py-2 inline-flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            <span>+ Tambah Harga</span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Pagination Footer --}}
                @if($categories->hasPages())
                    <div class="p-4 border-t border-border-subtle bg-surface-muted/30">
                        {{ $categories->links() }}
                    </div>
                @endif
            </x-ui.card>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODE 2: BANDINGKAN AGREGATOR (COMPARE MODE)                               --}}
        {{-- ========================================================================= --}}
        <div x-show="mode === 'compare'" class="space-y-6">
            @if($collectors->count() < 2)
                <div class="p-6 rounded-radius-lg border border-warning/30 bg-warning-light/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <p class="font-bold text-body text-warning-text">Dibutuhkan minimal 2 mitra agregator untuk membandingkan harga</p>
                        <p class="text-body-sm text-text-muted">Saat ini baru terdaftar {{ $collectors->count() }} agregator. Tambahkan mitra agregator lain agar perbandingan harga material dapat ditampilkan.</p>
                    </div>
                    <a href="{{ route('bank-sampah.collectors.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-radius-md bg-primary text-white text-body-sm font-semibold hover:bg-primary-hover shadow-subtle transition-subtle shrink-0">
                        + Tambah Agregator Baru
                    </a>
                </div>
            @else
                {{-- Comparison Selectors Card --}}
                <div class="p-4 sm:p-5 rounded-radius-lg bg-surface border border-border shadow-subtle space-y-4">
                    <div class="flex items-center justify-between border-b border-border-subtle pb-3">
                        <div>
                            <h3 class="text-heading-sm font-bold text-text">Pilih Dua Agregator untuk Dibandingkan</h3>
                            <p class="text-caption text-text-muted mt-0.5">Sistem akan menganalisis selisih penawaran harga per material sampah secara berdampingan.</p>
                        </div>
                        <button type="button" @click="swapCompareCollectors()" title="Tukar posisi Agregator A dan B" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-radius-md border border-border bg-surface-muted hover:bg-surface text-caption font-bold text-text transition-subtle cursor-pointer min-h-[44px]">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span>Tukar Posisi</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Selector Agregator A --}}
                        <div class="space-y-1.5 p-3.5 rounded-radius-md bg-primary-light/20 border border-primary/20">
                            <label for="compare-a-select" class="text-caption font-bold uppercase tracking-wider text-primary flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                                <span>Agregator A (Dasar)</span>
                            </label>
                            <select id="compare-a-select"
                                    x-model.number="compareA"
                                    class="w-full font-bold text-body rounded-radius-md border border-border bg-surface text-text px-3 py-2.5 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle min-h-[44px]">
                                <template x-for="col in collectors" :key="col.id">
                                    <option :value="col.id" x-text="col.name" :selected="col.id == compareA"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Selector Agregator B --}}
                        <div class="space-y-1.5 p-3.5 rounded-radius-md bg-info-light/20 border border-info/30">
                            <label for="compare-b-select" class="text-caption font-bold uppercase tracking-wider text-info-text flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-info"></span>
                                <span>Agregator B (Pembanding)</span>
                            </label>
                            <select id="compare-b-select"
                                    x-model.number="compareB"
                                    class="w-full font-bold text-body rounded-radius-md border border-border bg-surface text-text px-3 py-2.5 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle min-h-[44px]">
                                <template x-for="col in collectors" :key="col.id">
                                    <option :value="col.id" x-text="col.name" :selected="col.id == compareB" :disabled="col.id == compareA"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Quick Insights Bar (Requirement G) --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div class="p-3.5 rounded-radius-md bg-surface border border-border shadow-subtle flex flex-col justify-between">
                        <span class="text-caption font-bold text-text-muted uppercase">Material Dibandingkan</span>
                        <span class="font-mono text-heading-md font-extrabold text-text mt-1" x-text="materials.length"></span>
                    </div>

                    <div class="p-3.5 rounded-radius-md bg-primary-light/40 border border-primary/20 shadow-subtle flex flex-col justify-between">
                        <span class="text-caption font-bold text-primary uppercase truncate" x-text="getCollectorName(compareA) + ' Lebih Tinggi'"></span>
                        <span class="font-mono text-heading-md font-extrabold text-primary mt-1" x-text="countCompareStatus('A_HIGHER')"></span>
                    </div>

                    <div class="p-3.5 rounded-radius-md bg-info-light/40 border border-info/30 shadow-subtle flex flex-col justify-between">
                        <span class="text-caption font-bold text-info-text uppercase truncate" x-text="getCollectorName(compareB) + ' Lebih Tinggi'"></span>
                        <span class="font-mono text-heading-md font-extrabold text-info-text mt-1" x-text="countCompareStatus('B_HIGHER')"></span>
                    </div>

                    <div class="p-3.5 rounded-radius-md bg-surface-muted/60 border border-border shadow-subtle flex flex-col justify-between">
                        <span class="text-caption font-bold text-text-muted uppercase">Harga Sama</span>
                        <span class="font-mono text-heading-md font-extrabold text-text mt-1" x-text="countCompareStatus('SAME')"></span>
                    </div>

                    <div class="p-3.5 rounded-radius-md bg-warning-light/40 border border-warning/20 shadow-subtle flex flex-col justify-between">
                        <span class="text-caption font-bold text-warning-text uppercase">Hanya di Satu Agregator</span>
                        <span class="font-mono text-heading-md font-extrabold text-warning-text mt-1" x-text="countCompareStatus('ONLY_A') + countCompareStatus('ONLY_B')"></span>
                    </div>

                    <div class="p-3.5 rounded-radius-md bg-surface-muted/60 border border-border shadow-subtle flex flex-col justify-between">
                        <span class="text-caption font-bold text-text-muted uppercase">Keduanya Kosong</span>
                        <span class="font-mono text-heading-md font-extrabold text-text-muted mt-1" x-text="countCompareStatus('NEITHER')"></span>
                    </div>
                </div>

                {{-- Comparison Table Card --}}
                <x-ui.card variant="bordered" class="p-0 overflow-hidden space-y-0">
                    <div class="p-4 border-b border-border bg-surface-muted/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="relative flex-1 sm:max-w-md">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </span>
                            <input type="text"
                                   x-model="compareSearch"
                                   placeholder="Cari material dalam perbandingan..."
                                   class="w-full text-body rounded-radius-md border border-border bg-surface text-text pl-10 pr-4 py-2 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                        </div>
                        <div class="text-caption text-text-muted font-medium">
                            Membandingkan <strong class="text-text" x-text="getCollectorName(compareA)"></strong> vs <strong class="text-text" x-text="getCollectorName(compareB)"></strong>
                        </div>
                    </div>

                    {{-- Desktop Comparison Table --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-border bg-surface-muted/50 text-caption font-bold text-text-muted uppercase tracking-wider">
                                    <th scope="col" class="py-3 px-4 w-12 text-center">No</th>
                                    <th scope="col" class="py-3 px-4 min-w-[200px]">Material</th>
                                    <th scope="col" class="py-3 px-4 text-right min-w-[150px]" x-text="'Harga ' + getCollectorName(compareA)"></th>
                                    <th scope="col" class="py-3 px-4 text-right min-w-[150px]" x-text="'Harga ' + getCollectorName(compareB)"></th>
                                    <th scope="col" class="py-3 px-4 text-right min-w-[130px]">Selisih</th>
                                    <th scope="col" class="py-3 px-4 text-center min-w-[180px]">Status Perbandingan</th>
                                    <th scope="col" class="py-3 px-4 text-right w-36">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-subtle">
                                <template x-for="(mat, idx) in filteredCompareMaterials()" :key="mat.id">
                                    <tr class="hover:bg-surface-muted/50 transition-subtle">
                                        {{-- No --}}
                                        <td class="py-3.5 px-4 text-center font-mono text-body-sm text-text-muted" x-text="idx + 1"></td>

                                        {{-- Material --}}
                                        <td class="py-3.5 px-4">
                                            <span class="font-bold text-body text-text block" x-text="mat.name"></span>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="font-mono text-body-sm text-text-muted" x-text="mat.code"></span>
                                                <span class="font-mono text-caption uppercase px-1.5 py-0.2 rounded bg-surface-muted border border-border text-text font-semibold" x-text="mat.unit"></span>
                                            </div>
                                        </td>

                                        {{-- Harga A --}}
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <template x-if="mat.prices[compareA]">
                                                <span class="font-mono text-body font-bold text-text" x-text="mat.prices[compareA].formatted_price"></span>
                                            </template>
                                            <template x-if="!mat.prices[compareA]">
                                                <span class="text-body font-bold text-text-muted">&mdash;</span>
                                            </template>
                                        </td>

                                        {{-- Harga B --}}
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <template x-if="mat.prices[compareB]">
                                                <span class="font-mono text-body font-bold text-text" x-text="mat.prices[compareB].formatted_price"></span>
                                            </template>
                                            <template x-if="!mat.prices[compareB]">
                                                <span class="text-body font-bold text-text-muted">&mdash;</span>
                                            </template>
                                        </td>

                                        {{-- Selisih (Format: +Rp 500 / -Rp 200 / Rp 0 / —) --}}
                                        <td class="py-3.5 px-4 text-right font-mono text-body font-bold whitespace-nowrap">
                                            <span :class="getCompareDeltaColor(mat)" x-text="getCompareDeltaText(mat)"></span>
                                        </td>

                                        {{-- Status Perbandingan (Badge + Icon + Text) --}}
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            {{-- A Lebih Tinggi --}}
                                            <template x-if="getCompareStatus(mat) === 'A_HIGHER'">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-primary-light text-primary border border-primary/20 text-caption font-bold">
                                                    <svg class="w-3.5 h-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                                    <span x-text="getCollectorName(compareA) + ' lebih tinggi'"></span>
                                                </span>
                                            </template>

                                            {{-- B Lebih Tinggi --}}
                                            <template x-if="getCompareStatus(mat) === 'B_HIGHER'">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-info-light text-info-text border border-info/30 text-caption font-bold">
                                                    <svg class="w-3.5 h-3.5 text-info-text" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                                    <span x-text="getCollectorName(compareB) + ' lebih tinggi'"></span>
                                                </span>
                                            </template>

                                            {{-- Sama --}}
                                            <template x-if="getCompareStatus(mat) === 'SAME'">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-surface-muted text-text-muted border border-border text-caption font-semibold">
                                                    <span class="font-bold text-body-sm">=</span>
                                                    <span>Harga sama</span>
                                                </span>
                                            </template>

                                            {{-- Hanya A --}}
                                            <template x-if="getCompareStatus(mat) === 'ONLY_A'">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-warning-light text-warning-text border border-warning/30 text-caption font-semibold">
                                                    <svg class="w-3.5 h-3.5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span x-text="'Hanya ' + getCollectorName(compareA)"></span>
                                                </span>
                                            </template>

                                            {{-- Hanya B --}}
                                            <template x-if="getCompareStatus(mat) === 'ONLY_B'">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-warning-light text-warning-text border border-warning/30 text-caption font-semibold">
                                                    <svg class="w-3.5 h-3.5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span x-text="'Hanya ' + getCollectorName(compareB)"></span>
                                                </span>
                                            </template>

                                            {{-- Keduanya Belum --}}
                                            <template x-if="getCompareStatus(mat) === 'NEITHER'">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-surface-muted text-text-muted border border-border text-caption font-semibold">
                                                    <span class="font-bold">&mdash;</span>
                                                    <span>Belum ada harga</span>
                                                </span>
                                            </template>
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap text-body-sm font-semibold">
                                            <template x-if="!mat.prices[compareA]">
                                                <a :href="mat.create_price_url + '&collector_id=' + compareA" class="text-primary hover:underline block">
                                                    + Harga <span x-text="getCollectorName(compareA)"></span>
                                                </a>
                                            </template>
                                            <template x-if="!mat.prices[compareB]">
                                                <a :href="mat.create_price_url + '&collector_id=' + compareB" class="text-info-text hover:underline block mt-0.5">
                                                    + Harga <span x-text="getCollectorName(compareB)"></span>
                                                </a>
                                            </template>
                                            <template x-if="mat.prices[compareA] && mat.prices[compareB]">
                                                <a :href="mat.prices[compareA].edit_url" class="text-text-muted hover:text-text">
                                                    Edit Harga
                                                </a>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Comparison Cards --}}
                    <div class="block md:hidden divide-y divide-border-subtle p-3 space-y-3">
                        <template x-for="(mat, idx) in filteredCompareMaterials()" :key="mat.id">
                            <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h4 class="text-body font-bold text-text" x-text="mat.name"></h4>
                                        <span class="font-mono text-caption text-text-muted" x-text="mat.code + ' (' + mat.unit + ')'"></span>
                                    </div>
                                    <span class="font-mono text-body-sm text-text-muted font-semibold" x-text="'#' + (idx + 1)"></span>
                                </div>

                                {{-- Price Comparison Grid --}}
                                <div class="grid grid-cols-2 gap-3 p-3 bg-surface-muted rounded-radius-md border border-border-subtle">
                                    <div>
                                        <span class="text-caption font-bold text-primary block truncate" x-text="getCollectorName(compareA)"></span>
                                        <span class="font-mono text-body font-bold text-text mt-0.5 block" x-text="mat.prices[compareA] ? mat.prices[compareA].formatted_price : '—'"></span>
                                    </div>
                                    <div>
                                        <span class="text-caption font-bold text-info-text block truncate" x-text="getCollectorName(compareB)"></span>
                                        <span class="font-mono text-body font-bold text-text mt-0.5 block" x-text="mat.prices[compareB] ? mat.prices[compareB].formatted_price : '—'"></span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-caption font-semibold text-text-muted">Selisih:</span>
                                    <span class="font-mono font-bold text-body" :class="getCompareDeltaColor(mat)" x-text="getCompareDeltaText(mat)"></span>
                                </div>

                                <div class="pt-2 border-t border-border-subtle flex items-center justify-between">
                                    <div class="text-caption">
                                        <template x-if="getCompareStatus(mat) === 'A_HIGHER'">
                                            <span class="text-primary font-bold" x-text="getCollectorName(compareA) + ' lebih tinggi'"></span>
                                        </template>
                                        <template x-if="getCompareStatus(mat) === 'B_HIGHER'">
                                            <span class="text-info-text font-bold" x-text="getCollectorName(compareB) + ' lebih tinggi'"></span>
                                        </template>
                                        <template x-if="getCompareStatus(mat) === 'SAME'">
                                            <span class="text-text-muted font-semibold">Harga sama</span>
                                        </template>
                                        <template x-if="getCompareStatus(mat) === 'ONLY_A'">
                                            <span class="text-warning-text font-semibold" x-text="'Hanya ' + getCollectorName(compareA)"></span>
                                        </template>
                                        <template x-if="getCompareStatus(mat) === 'ONLY_B'">
                                            <span class="text-warning-text font-semibold" x-text="'Hanya ' + getCollectorName(compareB)"></span>
                                        </template>
                                        <template x-if="getCompareStatus(mat) === 'NEITHER'">
                                            <span class="text-text-muted">Belum ada harga</span>
                                        </template>
                                    </div>

                                    <div class="text-body-sm font-semibold">
                                        <template x-if="!mat.prices[compareA]">
                                            <a :href="mat.create_price_url + '&collector_id=' + compareA" class="text-primary hover:underline">
                                                + Harga A
                                            </a>
                                        </template>
                                        <template x-if="!mat.prices[compareB]">
                                            <a :href="mat.create_price_url + '&collector_id=' + compareB" class="text-info-text hover:underline ml-2">
                                                + Harga B
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </x-ui.card>
            @endif
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-delete-modal />

    {{-- Alpine Logic Script --}}
    <script>
        function wastePricesManager(materials, collectors, defaultCollectorId, defaultCompareBId, initialSearch) {
            return {
                mode: 'catalog', // 'catalog' | 'compare'
                alpineReady: false,
                materials: materials || [],
                collectors: collectors || [],
                activeCollectorId: defaultCollectorId || (collectors.length ? collectors[0].id : null),
                compareA: defaultCollectorId || (collectors.length ? collectors[0].id : null),
                compareB: defaultCompareBId || (collectors.length > 1 ? collectors[1].id : null),
                statusFilter: 'ALL', // 'ALL' | 'PRICED' | 'UNPRICED'
                searchQuery: initialSearch || '',
                compareSearch: '',

                init() {
                    this.alpineReady = true;
                },

                setMode(newMode) {
                    this.mode = newMode;
                },

                onCollectorChange() {
                    this.compareA = this.activeCollectorId;
                    if (this.compareB === this.activeCollectorId) {
                        const other = this.collectors.find(c => c.id != this.activeCollectorId);
                        this.compareB = other ? other.id : null;
                    }
                    if (window.history && window.history.replaceState) {
                        const url = new URL(window.location);
                        url.searchParams.set('collector_id', this.activeCollectorId);
                        window.history.replaceState({}, '', url);
                    }
                },

                swapCompareCollectors() {
                    const temp = this.compareA;
                    this.compareA = this.compareB;
                    this.compareB = temp;
                },

                activeCollectorName() {
                    const col = this.collectors.find(c => c.id == this.activeCollectorId);
                    return col ? col.name : 'Agregator';
                },

                getCollectorName(id) {
                    const col = this.collectors.find(c => c.id == id);
                    return col ? col.name : '-';
                },

                hasPrice(mat) {
                    return Boolean(this.activeCollectorId && mat.prices && mat.prices[this.activeCollectorId]);
                },

                getPrice(mat) {
                    if (!this.hasPrice(mat)) return null;
                    return mat.prices[this.activeCollectorId];
                },

                getMargin(mat) {
                    const p = this.getPrice(mat);
                    if (!p) return 0;
                    return p.price - (mat.member_price || 0);
                },

                otherPrices(mat) {
                    if (!mat.prices) return [];
                    return Object.values(mat.prices).filter(p => p.collector_id != this.activeCollectorId);
                },

                activeCollectorPricedCount() {
                    if (!this.activeCollectorId) return 0;
                    return this.materials.filter(m => m.prices && m.prices[this.activeCollectorId]).length;
                },

                activeCollectorUnpricedCount() {
                    if (!this.activeCollectorId) return this.materials.length;
                    return this.materials.filter(m => !m.prices || !m.prices[this.activeCollectorId]).length;
                },

                resetFilters() {
                    this.searchQuery = '';
                    this.statusFilter = 'ALL';
                },

                filteredCatalogMaterials() {
                    const q = (this.searchQuery || '').toLowerCase().trim();
                    return this.materials.filter(m => {
                        const matchSearch = !q ||
                            m.name.toLowerCase().includes(q) ||
                            (m.code && m.code.toLowerCase().includes(q)) ||
                            (m.group_name && m.group_name.toLowerCase().includes(q));

                        if (!matchSearch) return false;

                        if (this.statusFilter === 'PRICED') {
                            return this.hasPrice(m);
                        } else if (this.statusFilter === 'UNPRICED') {
                            return !this.hasPrice(m);
                        }
                        return true;
                    });
                },

                filteredCompareMaterials() {
                    const q = (this.compareSearch || '').toLowerCase().trim();
                    return this.materials.filter(m => {
                        if (!q) return true;
                        return m.name.toLowerCase().includes(q) ||
                            (m.code && m.code.toLowerCase().includes(q)) ||
                            (m.group_name && m.group_name.toLowerCase().includes(q));
                    });
                },

                getCompareStatus(mat) {
                    const priceA = (this.compareA && mat.prices && mat.prices[this.compareA]) ? mat.prices[this.compareA].price : null;
                    const priceB = (this.compareB && mat.prices && mat.prices[this.compareB]) ? mat.prices[this.compareB].price : null;

                    if (priceA !== null && priceB !== null) {
                        if (priceA > priceB) return 'A_HIGHER';
                        if (priceB > priceA) return 'B_HIGHER';
                        return 'SAME';
                    }
                    if (priceA !== null && priceB === null) return 'ONLY_A';
                    if (priceA === null && priceB !== null) return 'ONLY_B';
                    return 'NEITHER';
                },

                getCompareDeltaText(mat) {
                    const priceA = (this.compareA && mat.prices && mat.prices[this.compareA]) ? mat.prices[this.compareA].price : null;
                    const priceB = (this.compareB && mat.prices && mat.prices[this.compareB]) ? mat.prices[this.compareB].price : null;

                    if (priceA === null || priceB === null) return '—';
                    const diff = priceB - priceA;
                    if (diff > 0) return '+Rp ' + Math.round(diff).toLocaleString('id-ID');
                    if (diff < 0) return '-Rp ' + Math.round(Math.abs(diff)).toLocaleString('id-ID');
                    return 'Rp 0';
                },

                getCompareDeltaColor(mat) {
                    const status = this.getCompareStatus(mat);
                    if (status === 'B_HIGHER') return 'text-info-text';
                    if (status === 'A_HIGHER') return 'text-primary';
                    return 'text-text-muted';
                },

                countCompareStatus(targetStatus) {
                    return this.materials.filter(m => this.getCompareStatus(m) === targetStatus).length;
                },

                formatRp(val) {
                    if (val === null || val === undefined || isNaN(val)) return 'Rp 0';
                    return 'Rp ' + Math.round(val).toLocaleString('id-ID');
                }
            };
        }
    </script>
</x-layouts.dashboard>
