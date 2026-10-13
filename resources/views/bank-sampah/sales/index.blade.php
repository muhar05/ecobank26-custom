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
                    <a href="{{ route('bank-sampah.collectors.index') }}" class="text-body-sm font-semibold text-text-muted hover:text-primary transition-subtle px-1 py-1">
                        Agregator &rarr;
                    </a>
                    <a href="{{ route('bank-sampah.waste-prices.index') }}" class="text-body-sm font-semibold text-text-muted hover:text-primary transition-subtle px-1 py-1">
                        Harga Sampah &rarr;
                    </a>
                @endcan
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.sales.create', ['date' => $selectedDate]) }}'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Catat Penjualan
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Success Flash Alert with Print Action --}}
        @if(session('print_sale_id'))
            <div class="p-4 rounded-radius-md bg-success-light border border-success/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-success-text shadow-subtle">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-success text-white flex items-center justify-center shrink-0 font-bold text-caption">
                        ✓
                    </div>
                    <div>
                        <p class="text-body-sm font-bold">Penjualan berhasil disimpan!</p>
                        <p class="text-caption text-text-muted mt-0.5">Laporan Timbangan Kami siap dicetak untuk diserahkan ke mitra agregator.</p>
                    </div>
                </div>
                <a href="{{ route('bank-sampah.sales.show', session('print_sale_id')) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-radius-md bg-primary hover:bg-primary-hover text-white text-body-sm font-bold shadow-subtle transition-subtle shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Report Timbangan &rarr;
                </a>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- SECTION 1: SISA SETORAN SIAP DIJUAL (COMPACT OPERATIONAL)  --}}
        {{-- ========================================================= --}}
        <x-ui.card variant="bordered" class="p-0 overflow-hidden" x-data="{ matSearch: '' }">
            <div class="p-4 sm:px-5 sm:py-4 border-b border-border-subtle bg-surface flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                        <h2 class="text-heading-sm font-bold text-text">Sisa Setoran Siap Dijual</h2>
                        @if($readyToSell->isNotEmpty())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm bg-primary-light text-primary text-caption font-bold border border-primary/20">
                                {{ $readyToSell->count() }} Material &bull; {{ number_format($readyToSell->sum('remaining_weight'), 2, ',', '.') }} kg siap dijual
                            </span>
                        @endif
                    </div>
                    <p class="text-caption text-text-muted mt-0.5">
                        Timbangan sampah nasabah yang siap dimuat ke truk agregator
                    </p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    @if($readyToSell->isNotEmpty())
                        {{-- Search Material Input --}}
                        <div class="relative w-full sm:w-52">
                            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-text-muted">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </span>
                            <input type="text"
                                   x-model="matSearch"
                                   placeholder="Cari material..."
                                   class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text pl-8 pr-2.5 py-1.5 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                        </div>
                    @endif

                    {{-- Date Selector Form --}}
                    <form method="GET" action="{{ route('bank-sampah.sales.index') }}" class="flex items-center gap-1.5">
                        <span class="text-caption font-bold text-text-muted uppercase">Tanggal:</span>
                        <input type="date"
                               name="date"
                               value="{{ $selectedDate }}"
                               onchange="this.form.submit()"
                               class="text-body-sm rounded-radius-md border border-border bg-surface text-text py-1 px-2.5 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                    </form>
                </div>
            </div>

            @if($readyToSell->isNotEmpty())
                {{-- COMPACT OPERATIONAL TABLE (WITH INTERNAL VERTICAL SCROLL) --}}
                <div class="max-h-[360px] overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-10 bg-surface border-b border-border shadow-2xs">
                            <tr class="bg-surface-muted/60 text-body-sm font-bold text-text-muted uppercase tracking-wider">
                                <th class="py-2.5 px-4">Material</th>
                                <th class="py-2.5 px-4 text-right">Sisa</th>
                                <th class="py-2.5 px-4 text-right">Harga Beli</th>
                                <th class="py-2.5 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach($readyToSell as $item)
                                <tr class="hover:bg-surface-muted/40 transition-subtle"
                                    x-show="!matSearch || $el.dataset.search.includes(matSearch.toLowerCase().trim())"
                                    data-search="{{ strtolower($item['category']->name . ' ' . ($item['category']->code ?? '')) }}"
                                    title="Total Setoran: {{ number_format($item['total_deposited'], 2, ',', '.') }} {{ $item['category']->unit }} | Terjual: {{ number_format($item['total_sold'], 2, ',', '.') }} {{ $item['category']->unit }}">
                                    <td class="py-2.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-caption shrink-0">
                                                {{ strtoupper(substr($item['category']->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="text-body font-bold text-text block leading-tight">{{ $item['category']->name }}</span>
                                                @if($item['category']->code)
                                                    <span class="font-mono text-body-sm text-text-muted font-semibold block">{{ $item['category']->code }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-4 text-right whitespace-nowrap">
                                        <span class="font-mono font-bold text-body-sm text-primary">
                                            {{ number_format($item['remaining_weight'], 2, ',', '.') }}
                                        </span>
                                        <span class="text-caption text-text-muted font-normal">{{ $item['category']->unit }}</span>
                                    </td>
                                    <td class="py-2.5 px-4 text-right whitespace-nowrap">
                                        <span class="font-mono text-body-sm font-semibold text-text">
                                            Rp {{ number_format($item['member_price'], 0, ',', '.') }}
                                        </span>
                                        <span class="text-caption text-text-muted font-normal">/{{ $item['category']->unit }}</span>
                                    </td>
                                    <td class="py-2.5 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-radius-sm bg-success-light text-success-text text-caption font-bold border border-success/30">
                                            ✓ Siap Dijual
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Action Footer --}}
                <div class="p-3 sm:px-5 sm:py-3 border-t border-border-subtle bg-surface-muted/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-caption text-text-muted">
                        Total <strong class="text-text font-mono">{{ $readyToSell->count() }}</strong> material siap jual dengan akumulasi berat <strong class="text-primary font-mono">{{ number_format($readyToSell->sum('remaining_weight'), 2, ',', '.') }} kg</strong>.
                    </p>
                    <a href="{{ route('bank-sampah.sales.create', ['date' => $selectedDate]) }}"
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-radius-md bg-primary hover:bg-primary-hover text-white text-body-sm font-semibold shadow-subtle transition-subtle shrink-0">
                        Catat Penjualan ke Agregator &rarr;
                    </a>
                </div>
            @else
                {{-- Honest Empty State: Sisa Setoran --}}
                <div class="py-8 px-4 text-center border-2 border-dashed border-border rounded-radius-md m-4 flex flex-col items-center justify-center space-y-2">
                    <div class="w-10 h-10 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
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
                            <a href="{{ route('bank-sampah.deposits.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-radius-md bg-primary text-white text-body-sm font-semibold hover:bg-primary-hover shadow-subtle transition-subtle">
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
        <x-ui.card id="table-section" variant="bordered" class="p-0 overflow-hidden scroll-mt-20">
            <div class="p-4 sm:p-5 border-b border-border-subtle bg-surface flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-heading-sm font-bold text-text">Riwayat Transaksi Penjualan</h2>
                    <p class="text-caption text-text-muted mt-0.5">Catatan muatan sampah yang telah dijual ke mitra agregator</p>
                </div>

                {{-- Search Toolbar --}}
                <form method="GET" action="{{ route('bank-sampah.sales.index') }}" data-live-search class="flex items-center gap-2 w-full sm:w-auto">
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
                        <a href="{{ route('bank-sampah.sales.index') }}" class="text-body font-semibold text-text-muted hover:text-danger px-2 py-1.5 transition-subtle">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            @if($sales->isNotEmpty())
                @php
                    $salesByDate = $sales->getCollection()->groupBy(fn($s) => $s->date ? $s->date->format('Y-m-d') : '');
                @endphp

                <div class="p-4 sm:p-6 space-y-6">
                    @foreach($salesByDate as $dateKey => $salesOnDate)
                        @php
                            $carbonDate = $dateKey ? \Carbon\Carbon::parse($dateKey) : null;
                            $dayTotalWeight = $salesOnDate->sum(fn($s) => $s->details->sum('weight'));
                        @endphp
                        <div class="space-y-3">
                            {{-- Date Group Header --}}
                            <div class="flex items-center justify-between pb-2 border-b border-border-subtle">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-primary shrink-0"></div>
                                    <h3 class="text-heading-sm font-bold text-text">
                                        {{ $carbonDate ? $carbonDate->translatedFormat('d F Y') : 'Tanpa Tanggal' }}
                                    </h3>
                                    @if($carbonDate)
                                        <span class="text-caption text-text-muted font-medium">
                                            ({{ $carbonDate->translatedFormat('l') }})
                                        </span>
                                    @endif
                                </div>
                                <span class="text-caption font-mono text-text-muted">
                                    {{ $salesOnDate->count() }} transaksi &bull; {{ number_format($dayTotalWeight, 2, ',', '.') }} kg
                                </span>
                            </div>

                            {{-- Transaction Cards for this Date --}}
                            <div class="space-y-3 pl-0 sm:pl-3">
                                @foreach($salesOnDate as $sale)
                                    @php
                                        $materialCount = $sale->details->count();
                                        $totalWeight = $sale->details->sum('weight');
                                        $totalMargin = $sale->details->sum('margin');
                                    @endphp
                                    <div class="p-4 sm:p-5 rounded-radius-lg border border-border bg-surface hover:border-primary/40 hover:bg-surface-muted/30 transition-subtle shadow-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="space-y-1.5 flex-1 min-w-0">
                                            <div class="flex items-center gap-2.5 flex-wrap">
                                                <div class="w-8 h-8 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-body-sm shrink-0 shadow-subtle">
                                                    {{ strtoupper(substr($sale->collector->name ?? 'A', 0, 1)) }}
                                                </div>
                                                <h4 class="text-body font-bold text-text truncate">
                                                    {{ $sale->collector->name ?? 'Mitra Agregator' }}
                                                </h4>
                                                <span class="text-caption text-text-muted font-mono">SAL-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</span>
                                            </div>

                                            <div class="flex items-center gap-2 text-body-sm text-text-muted flex-wrap">
                                                <span class="font-semibold text-text">{{ $materialCount }} material</span>
                                                <span>&bull;</span>
                                                <span class="font-mono font-semibold text-text">{{ number_format($totalWeight, 2, ',', '.') }} kg</span>
                                                <span>&bull;</span>
                                                <span class="font-mono font-bold text-text">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                                                <span>&bull;</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm bg-success-light text-success-text border border-success/30 font-mono font-bold text-caption">
                                                    Margin +Rp {{ number_format($totalMargin, 0, ',', '.') }}
                                                </span>
                                            </div>

                                            @if($sale->notes)
                                                <p class="text-caption text-text-muted italic truncate max-w-xl">{{ $sale->notes }}</p>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                                            <a href="{{ route('bank-sampah.sales.show', $sale) }}"
                                               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-radius-md bg-primary hover:bg-primary-hover text-white text-body-sm font-semibold shadow-subtle transition-subtle">
                                                Detail &rarr;
                                            </a>
                                            <a href="{{ route('bank-sampah.sales.edit', $sale) }}"
                                               class="p-2 text-text-muted hover:text-text rounded-radius-md hover:bg-surface-muted transition-subtle" title="Edit Transaksi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <button type="button"
                                                    @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.sales.destroy', $sale) }}'})"
                                                    class="p-2 text-danger hover:text-danger-text rounded-radius-md hover:bg-danger-light transition-subtle cursor-pointer" title="Hapus Transaksi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
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
                    <div class="w-12 h-12 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <p class="text-heading-sm font-bold text-text">Belum ada riwayat transaksi penjualan ke agregator.</p>
                    <p class="text-caption text-text-muted">Gunakan tombol Catat Penjualan di atas untuk memuat timbangan ke truk agregator.</p>
                </div>
            @endif
        </x-ui.card>

        {{-- Confirmation Modal --}}
        <x-confirm-delete-modal />
    </div>
</x-layouts.dashboard>
