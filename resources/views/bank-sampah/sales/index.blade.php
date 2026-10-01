<x-layouts.dashboard title="Penjualan ke Agregator">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header --}}
        <x-ui.page-header title="Penjualan ke Agregator" description="Kelola penjualan sisa setoran ke mitra agregator dan pantauan margin kas bank sampah">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Penjualan</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                @can('manage_waste_prices')
                    <a href="{{ route('bank-sampah.collectors.index') }}" class="text-caption font-semibold text-text-muted hover:text-primary transition-subtle px-1 py-1">
                        Agregator &rarr;
                    </a>
                    <a href="{{ route('bank-sampah.waste-prices.index') }}" class="text-caption font-semibold text-text-muted hover:text-primary transition-subtle px-1 py-1">
                        Harga Sampah &rarr;
                    </a>
                @endcan
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.sales.create') }}'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Catat Penjualan
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- ========================================================= --}}
        {{-- SECTION 1: SISA SETORAN SIAP DIJUAL                       --}}
        {{-- ========================================================= --}}
        <x-ui.card variant="bordered" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border-subtle pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                        <h2 class="text-heading-sm font-bold text-text">Sisa Setoran Siap Dijual</h2>
                    </div>
                    <p class="text-caption text-text-muted mt-0.5">
                        Sampah terpilah hasil timbangan nasabah yang siap dimuat ke truk agregator
                    </p>
                </div>

                {{-- Date Selector Form --}}
                <form method="GET" action="{{ route('bank-sampah.sales.index') }}" class="flex items-center gap-2">
                    <span class="text-caption font-bold text-text-muted uppercase">Tanggal:</span>
                    <input type="date"
                           name="date"
                           value="{{ $selectedDate }}"
                           onchange="this.form.submit()"
                           class="text-body-sm rounded-radius-md border border-border bg-surface text-text py-1.5 px-3 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                </form>
            </div>

            @if($readyToSell->isNotEmpty())
                {{-- DESKTOP TABLE: Sisa Setoran --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted/40 text-caption font-bold text-text-muted uppercase tracking-wider">
                                <th class="py-3 px-4">Material / Kategori Sampah</th>
                                <th class="py-3 px-4 text-right">Total Setoran</th>
                                <th class="py-3 px-4 text-right">Sudah Terjual</th>
                                <th class="py-3 px-4 text-right">Sisa Siap Dijual</th>
                                <th class="py-3 px-4 text-right">Harga Beli Nasabah</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach($readyToSell as $item)
                                <tr class="hover:bg-surface-muted/50 transition-subtle">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-caption shrink-0">
                                                {{ strtoupper(substr($item['category']->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="text-body font-bold text-text block">{{ $item['category']->name }}</span>
                                                <span class="font-mono text-caption text-text-muted block">{{ $item['category']->code ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-body-sm text-text-muted">
                                        {{ number_format($item['total_deposited'], 2, ',', '.') }} {{ $item['category']->unit }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-body-sm text-text-muted">
                                        {{ number_format($item['total_sold'], 2, ',', '.') }} {{ $item['category']->unit }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-heading-sm font-bold text-primary">
                                        {{ number_format($item['remaining_weight'], 2, ',', '.') }} <span class="text-caption font-normal text-text-muted">{{ $item['category']->unit }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-body-sm font-semibold text-text">
                                        Rp {{ number_format($item['member_price'], 0, ',', '.') }} <span class="text-caption text-text-muted font-normal">/ {{ $item['category']->unit }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <x-ui.badge variant="success" size="sm">Siap Dijual</x-ui.badge>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('bank-sampah.sales.create', ['date' => $selectedDate, 'category_id' => $item['category']->id]) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-radius-md bg-primary hover:bg-primary-hover text-white text-caption font-semibold shadow-subtle transition-subtle">
                                            Jual ke Agregator &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE ADAPTIVE CARDS: Sisa Setoran (< md) --}}
                <div class="block md:hidden space-y-3">
                    @foreach($readyToSell as $item)
                        <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-body font-bold text-text block">{{ $item['category']->name }}</span>
                                    <span class="font-mono text-caption text-text-muted">{{ $item['category']->code ?? '-' }}</span>
                                </div>
                                <x-ui.badge variant="success" size="sm">Siap Dijual</x-ui.badge>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-caption pt-1 border-t border-border-subtle">
                                <div>
                                    <span class="text-text-muted uppercase font-bold block">Sisa Siap Dijual</span>
                                    <span class="font-mono font-bold text-heading-sm text-primary">
                                        {{ number_format($item['remaining_weight'], 2, ',', '.') }} {{ $item['category']->unit }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-text-muted uppercase font-bold block">Harga Beli Nasabah</span>
                                    <span class="font-mono font-bold text-body-sm text-text">
                                        Rp {{ number_format($item['member_price'], 0, ',', '.') }}/{{ $item['category']->unit }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-caption text-text-muted flex justify-between">
                                <span>Setoran: {{ number_format($item['total_deposited'], 2, ',', '.') }} {{ $item['category']->unit }}</span>
                                <span>Terjual: {{ number_format($item['total_sold'], 2, ',', '.') }} {{ $item['category']->unit }}</span>
                            </div>

                            <div class="pt-2 border-t border-border-subtle">
                                <a href="{{ route('bank-sampah.sales.create', ['date' => $selectedDate, 'category_id' => $item['category']->id]) }}"
                                   class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-radius-md bg-primary hover:bg-primary-hover text-white text-caption font-semibold shadow-subtle transition-subtle">
                                    Jual ke Agregator &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Honest Empty State: Sisa Setoran --}}
                <div class="py-10 px-4 text-center border-2 border-dashed border-border rounded-radius-md flex flex-col items-center justify-center space-y-2.5">
                    <div class="w-12 h-12 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-body-sm font-bold text-text">Saat ini belum ada setoran yang siap dijual pada tanggal ini.</p>
                        <p class="text-caption text-text-muted mt-0.5">
                            Setoran sampah nasabah pada {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }} belum tercatat atau seluruh timbangan sudah habis terjual.
                        </p>
                    </div>
                    @can('manage_deposits')
                        <div class="pt-1">
                            <a href="{{ route('bank-sampah.deposits.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-radius-md bg-primary text-white text-caption font-semibold hover:bg-primary-hover shadow-subtle transition-subtle">
                                + Catat Setoran Baru
                            </a>
                        </div>
                    @endcan
                </div>
            @endif
        </x-ui.card>

        {{-- ========================================================= --}}
        {{-- SECTION 2: RIWAYAT TRANSAKSI PENJUALAN                    --}}
        {{-- ========================================================= --}}
        <x-ui.card variant="bordered" class="p-0 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-border-subtle bg-surface flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-heading-sm font-bold text-text">Riwayat Transaksi Penjualan</h2>
                    <p class="text-caption text-text-muted mt-0.5">Catatan pengiriman muatan sampah ke mitra agregator</p>
                </div>

                {{-- Search Toolbar --}}
                <form method="GET" action="{{ route('bank-sampah.sales.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative flex-1 sm:w-72">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari agregator atau catatan..."
                               class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text pl-9 pr-3 py-1.5 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                    </div>
                    <x-ui.button type="submit" variant="secondary" size="sm">
                        Cari
                    </x-ui.button>
                    @if(request('search'))
                        <a href="{{ route('bank-sampah.sales.index') }}" class="text-caption font-semibold text-text-muted hover:text-danger px-2 py-1.5 transition-subtle">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            @if($sales->isNotEmpty())
                {{-- DESKTOP SALES TABLE WITH DRAWER --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted/40 text-caption font-bold text-text-muted uppercase tracking-wider">
                                <th class="py-3 px-5">Tanggal</th>
                                <th class="py-3 px-5">Agregator</th>
                                <th class="py-3 px-5 text-right">Nilai Penjualan</th>
                                <th class="py-3 px-5 text-right">Margin Bank Sampah</th>
                                <th class="py-3 px-5">Catatan</th>
                                <th class="py-3 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach($sales as $sale)
                                <tr x-data="{ expanded: false }" class="hover:bg-surface-muted/40 transition-subtle group">
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        <div class="text-body-sm font-semibold text-text">{{ $sale->date->format('d/m/Y') }}</div>
                                        <div class="text-caption text-text-muted mt-0.5">{{ $sale->date->diffForHumans() }}</div>
                                    </td>

                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-body-sm shrink-0 shadow-subtle">
                                                {{ strtoupper(substr($sale->collector->name ?? 'A', 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="text-body font-bold text-text block">{{ $sale->collector->name ?? '-' }}</span>
                                                <span class="text-caption text-text-muted">{{ $sale->details->count() }} jenis material</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <span class="font-mono text-body font-bold text-text block">
                                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-success-light text-success-text border border-success/30 font-mono font-bold text-body-sm">
                                            +Rp {{ number_format($sale->details->sum('margin'), 0, ',', '.') }}
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-5">
                                        <div class="text-body-sm text-text-muted max-w-xs truncate" title="{{ $sale->notes }}">
                                            {{ $sale->notes ?: '-' }}
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center justify-end gap-3 text-body-sm font-semibold">
                                            <button type="button"
                                                    @click="expanded = !expanded"
                                                    class="text-caption font-semibold text-primary hover:underline transition-subtle">
                                                <span x-text="expanded ? 'Tutup Rincian' : 'Lihat Rincian'"></span>
                                            </button>
                                            <a href="{{ route('bank-sampah.sales.edit', $sale) }}"
                                               class="text-text-muted hover:text-text transition-subtle">
                                                Edit
                                            </a>
                                            <button type="button"
                                                    @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.sales.destroy', $sale) }}'})"
                                                    class="text-danger hover:underline transition-subtle cursor-pointer">
                                                Hapus
                                            </button>
                                        </div>

                                        {{-- Expandable Accordion Drawer for Material Details --}}
                                        <div x-show="expanded" x-cloak class="mt-3 p-4 rounded-radius-md bg-surface-muted/60 border border-border text-left">
                                            <div class="text-caption font-bold uppercase tracking-wider text-text-muted mb-2">
                                                Rincian Muatan Penjualan:
                                            </div>
                                            <div class="divide-y divide-border-subtle">
                                                @foreach($sale->details as $d)
                                                    <div class="py-2 flex items-center justify-between text-body-sm">
                                                        <div>
                                                            <span class="font-bold text-text">{{ $d->wasteCategory->name ?? 'Material' }}</span>
                                                            <span class="font-mono text-caption text-text-muted ml-1">({{ number_format($d->weight, 2, ',', '.') }} {{ $d->wasteCategory->unit ?? 'kg' }})</span>
                                                            <span class="text-caption text-text-muted block mt-0.5">
                                                                Harga Beli Nasabah: Rp {{ number_format($d->member_price, 0, ',', '.') }} &bull; Harga Agregator: Rp {{ number_format($d->price_per_unit, 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="font-mono font-bold text-text block">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                                                            <span class="font-mono text-caption text-success font-semibold">Margin: +Rp {{ number_format($d->margin, 0, ',', '.') }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE ADAPTIVE CARDS (< md) --}}
                <div class="block md:hidden divide-y divide-border-subtle p-3 space-y-3">
                    @foreach($sales as $sale)
                        <div x-data="{ expanded: false }" class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-body font-bold text-text block">{{ $sale->collector->name ?? 'Agregator' }}</span>
                                    <span class="text-caption text-text-muted font-medium">{{ $sale->date->format('d/m/Y') }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-mono text-body font-bold text-text block">
                                        Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                    </span>
                                    <span class="font-mono text-caption text-success font-bold">
                                        +Rp {{ number_format($sale->details->sum('margin'), 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            @if($sale->notes)
                                <p class="text-caption text-text-muted italic">{{ $sale->notes }}</p>
                            @endif

                            {{-- Mobile Drawer --}}
                            <div x-show="expanded" x-cloak class="pt-2 border-t border-border-subtle space-y-2">
                                <span class="text-caption font-bold text-text-muted uppercase block">Rincian Material:</span>
                                @foreach($sale->details as $d)
                                    <div class="flex items-center justify-between text-caption py-1 border-b border-border-subtle last:border-b-0">
                                        <div>
                                            <span class="font-bold text-text">{{ $d->wasteCategory->name ?? 'Material' }}</span>
                                            <span class="font-mono text-text-muted block">{{ number_format($d->weight, 2, ',', '.') }} kg @ Rp {{ number_format($d->price_per_unit, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="text-right font-mono">
                                            <span class="font-bold text-text block">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                                            <span class="text-success font-semibold">+Rp {{ number_format($d->margin, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="pt-2 border-t border-border-subtle flex items-center justify-between text-body-sm">
                                <button type="button" @click="expanded = !expanded" class="text-caption font-semibold text-primary hover:underline">
                                    <span x-text="expanded ? 'Tutup Rincian' : 'Rincian Material (' + {{ $sale->details->count() }} + ')'"></span>
                                </button>
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('bank-sampah.sales.edit', $sale) }}" class="text-text-muted hover:text-text font-semibold text-caption">Edit</a>
                                    <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.sales.destroy', $sale) }}'})" class="text-danger font-semibold text-caption">Hapus</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination Footer --}}
                @if($sales->hasPages())
                    <div class="p-4 border-t border-border-subtle bg-surface-muted/30">
                        {{ $sales->links() }}
                    </div>
                @endif
            @else
                <div class="p-10 text-center flex flex-col items-center justify-center space-y-2">
                    <p class="text-body-sm font-semibold text-text">Belum ada riwayat transaksi penjualan ke agregator.</p>
                    <p class="text-caption text-text-muted">Gunakan tombol Catat Penjualan di atas untuk memuat timbangan ke truk agregator.</p>
                </div>
            @endif
        </x-ui.card>

        {{-- Confirmation Modal --}}
        <x-confirm-delete-modal />
    </div>
</x-layouts.dashboard>
