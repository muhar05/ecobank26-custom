<x-layouts.dashboard title="Detail Agregator - {{ $collector->name }}">
    <div x-data="collectorPricesManager()" class="space-y-6 max-w-6xl mx-auto pb-12">
        {{-- Page Header --}}
        <x-ui.page-header title="Detail Agregator" description="Informasi mitra agregator dan pengelolaan harga lapak per material sampah">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.collectors.index') }}" class="hover:text-text transition-subtle">Agregator</a>
                <span>&rarr;</span>
                <span class="text-text">{{ $collector->name }}</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.collectors.index') }}'">
                    &larr; Kembali ke Daftar
                </x-ui.button>
                <x-ui.button variant="secondary" size="sm" onclick="window.location='{{ route('bank-sampah.collectors.edit', $collector) }}'">
                    Edit Profil Agregator
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Success / Error Alerts --}}
        @if(session('success'))
            <div class="p-4 rounded-radius-md bg-success-light border border-success/30 flex items-center gap-3 text-success-text shadow-subtle">
                <svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-body-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-radius-md bg-danger-light border border-danger/30 text-danger-text">
                <ul class="text-body-sm space-y-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- PROFIL MITRA AGREGATOR                                    --}}
        {{-- ========================================================= --}}
        <x-ui.card variant="bordered" class="space-y-4 scroll-mt-24" id="informasi">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-heading-sm shrink-0 shadow-subtle">
                        {{ strtoupper(substr($collector->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-heading-sm font-bold text-text">{{ $collector->name }}</h2>
                        <p class="text-caption text-text-muted mt-0.5">Mitra Pengepul / Agregator Terdaftar</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-caption font-semibold">
                    <div class="px-3 py-1.5 rounded-radius-md bg-surface-muted border border-border text-text">
                        <span class="text-text-muted uppercase text-caption block">Material Aktif:</span>
                        <span class="font-mono text-body-sm font-bold text-primary">{{ $collector->wastePrices->count() }} Jenis</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-border-subtle text-body-sm">
                <div>
                    <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">Kontak / Telepon</span>
                    @if($collector->phone)
                        @php
                            $waPhone = preg_replace('/[^0-9]/', '', $collector->phone);
                            if(substr($waPhone, 0, 1) === '0') {
                                $waPhone = '62' . substr($waPhone, 1);
                            }
                        @endphp
                        <div class="mt-1 flex items-center gap-2">
                            <span class="font-mono font-medium text-text">{{ $collector->phone }}</span>
                            <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 bg-success-light text-success-text border border-success/30 rounded text-caption font-semibold hover:bg-success/20 transition-subtle">
                                WhatsApp
                            </a>
                        </div>
                    @else
                        <span class="text-text-muted italic mt-1 block">Tidak ada nomor telepon</span>
                    @endif
                </div>

                <div>
                    <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">Alamat Gudang / Pengepul</span>
                    <span class="text-text mt-1 block">{{ $collector->address ?: 'Alamat belum dicantumkan' }}</span>
                </div>
            </div>
        </x-ui.card>

        {{-- ========================================================= --}}
        {{-- SECTION / TAB: HARGA LAPAK                                --}}
        {{-- ========================================================= --}}
        <x-ui.card variant="bordered" class="p-0 overflow-hidden space-y-0 scroll-mt-24" id="harga-lapak">
            {{-- Section Toolbar --}}
            <div class="p-4 sm:p-5 border-b border-border-subtle bg-surface flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                        <h3 class="text-heading-sm font-bold text-text">Harga Lapak</h3>
                    </div>
                    <p class="text-caption text-text-muted mt-0.5">
                        Daftar harga beli agregator terhadap material sampah terpilah (current price untuk penjualan)
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <a href="{{ route('bank-sampah.collectors.import', $collector) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-radius-md border border-border bg-surface text-text hover:bg-surface-muted text-caption font-semibold transition-subtle shadow-subtle cursor-pointer">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        + Import Harga
                    </a>
                    <x-ui.button type="button" variant="primary" size="sm" @click="openAddModal()">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        + Tambah Harga
                    </x-ui.button>
                </div>
            </div>

            @if($collector->wastePrices->isNotEmpty())
                {{-- DESKTOP TABLE: Harga Lapak --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted/40 text-caption font-bold text-text-muted uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Jenis Barang</th>
                                <th class="py-3 px-4 text-right">Harga Lapak</th>
                                <th class="py-3 px-4 text-center">Satuan</th>
                                <th class="py-3 px-4">Modified At</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach($collector->wastePrices as $index => $wp)
                                <tr class="hover:bg-surface-muted/40 transition-subtle">
                                    <td class="py-3.5 px-4 text-center font-mono text-body-sm text-text-muted">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-caption shrink-0">
                                                {{ strtoupper(substr($wp->wasteCategory->name ?? 'M', 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="text-body font-bold text-text block">{{ $wp->wasteCategory->name ?? '-' }}</span>
                                                <span class="font-mono text-caption text-text-muted block">{{ $wp->wasteCategory->code ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <span class="font-mono text-body font-bold text-text">
                                            Rp {{ number_format($wp->collector_price, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="font-mono text-caption uppercase px-2 py-0.5 rounded bg-surface-muted border border-border text-text font-semibold">
                                            {{ $wp->wasteCategory->unit ?? 'Kg' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="text-body-sm text-text font-medium">{{ $wp->updated_at->format('d M Y H:i') }}</div>
                                        <div class="text-caption text-text-muted">{{ $wp->updated_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center justify-end gap-2.5 text-body-sm font-semibold">
                                            <button type="button"
                                                    @click="openEditModal({{ $wp->id }}, '{{ addslashes($wp->wasteCategory->name ?? '-') }}', '{{ $wp->wasteCategory->unit ?? 'Kg' }}', {{ (float) $wp->collector_price }})"
                                                    class="text-caption font-semibold text-primary hover:underline transition-subtle cursor-pointer px-1 py-2">
                                                Edit Harga
                                            </button>
                                            <button type="button"
                                                    @click="$dispatch('open-delete-modal', {id: 'confirm-delete-price-modal', action: '{{ route('bank-sampah.collectors.prices.destroy', [$collector, $wp]) }}'})"
                                                    class="text-caption font-semibold text-danger hover:underline transition-subtle cursor-pointer px-1 py-2">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE ADAPTIVE CARDS (< md) --}}
                <div class="block md:hidden divide-y divide-border-subtle p-3 space-y-3">
                    @foreach($collector->wastePrices as $index => $wp)
                        <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-body font-bold text-text block">{{ $wp->wasteCategory->name ?? '-' }}</span>
                                    <span class="font-mono text-caption text-text-muted">{{ $wp->wasteCategory->code ?? '-' }}</span>
                                </div>
                                <span class="font-mono text-caption uppercase px-2 py-0.5 rounded bg-surface-muted border border-border text-text font-semibold">
                                    {{ $wp->wasteCategory->unit ?? 'Kg' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-caption pt-1 border-t border-border-subtle">
                                <div>
                                    <span class="text-text-muted uppercase font-bold block">Harga Lapak</span>
                                    <span class="font-mono font-bold text-heading-sm text-text">
                                        Rp {{ number_format($wp->collector_price, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-text-muted uppercase font-bold block">Modified At</span>
                                    <span class="text-body-sm font-medium text-text block">{{ $wp->updated_at->format('d M Y H:i') }}</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-border-subtle flex items-center justify-end gap-3 text-caption font-semibold">
                                <button type="button"
                                        @click="openEditModal({{ $wp->id }}, '{{ addslashes($wp->wasteCategory->name ?? '-') }}', '{{ $wp->wasteCategory->unit ?? 'Kg' }}', {{ (float) $wp->collector_price }})"
                                        class="text-primary hover:underline px-1 py-2">
                                    Edit Harga
                                </button>
                                <button type="button"
                                        @click="$dispatch('open-delete-modal', {id: 'confirm-delete-price-modal', action: '{{ route('bank-sampah.collectors.prices.destroy', [$collector, $wp]) }}'})"
                                        class="text-danger hover:underline px-1 py-2">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Clear Empty State --}}
                <div class="py-12 px-4 text-center border-2 border-dashed border-border rounded-radius-md m-4 flex flex-col items-center justify-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-body-sm font-bold text-text">Belum ada harga lapak yang diatur untuk agregator ini.</p>
                        <p class="text-caption text-text-muted mt-0.5 max-w-md mx-auto">
                            Tambahkan penawaran harga agregator per kategori material sampah agar transaksi penjualan dapat mendeteksi harga secara otomatis.
                        </p>
                    </div>
                    <div class="flex items-center gap-2.5 pt-1">
                        <button type="button"
                                @click="openAddModal()"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-radius-md bg-primary text-white text-caption font-semibold hover:bg-primary-hover shadow-subtle transition-subtle cursor-pointer">
                            + Tambah Harga
                        </button>
                        <a href="{{ route('bank-sampah.collectors.import', $collector) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-radius-md border border-border bg-surface text-text hover:bg-surface-muted text-caption font-semibold transition-subtle shadow-subtle cursor-pointer">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            + Import Harga
                        </a>
                    </div>
                </div>
            @endif
        </x-ui.card>

        {{-- ========================================================= --}}
        {{-- MODAL 1: TAMBAH HARGA LAPAK                               --}}
        {{-- ========================================================= --}}
        {{-- ========================================================= --}}
        {{-- SECTION: RIWAYAT HARGA AGREGATOR --}}
        {{-- ========================================================= --}}
        @php
            $recentHistories = $collector->wastePrices
                ->flatMap(fn ($wp) => $wp->histories->map(fn ($h) => ['history' => $h, 'price' => $wp]))
                ->sortByDesc(fn ($x) => $x['history']->changed_at)
                ->take(10)
                ->values();
        @endphp

        <x-ui.card variant="bordered" class="p-0 overflow-hidden space-y-0 scroll-mt-24" id="riwayat-harga">
            <div class="p-4 sm:p-5 border-b border-border-subtle bg-surface flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-info"></span>
                        <h3 class="text-heading-sm font-bold text-text">Riwayat Harga</h3>
                    </div>
                    <p class="text-caption text-text-muted mt-0.5">
                        Perubahan harga lapak agregator ini. Harga pertama tercatat dari kosong, perubahan berikutnya mencatat harga lama ke harga baru.
                    </p>
                </div>

                <a href="{{ route('bank-sampah.waste-prices.history', ['collector_id' => $collector->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-radius-md border border-border bg-surface text-text hover:bg-surface-muted text-caption font-semibold transition-subtle shadow-subtle cursor-pointer">
                    Lihat Riwayat Lengkap &rarr;
                </a>
            </div>

            @if($recentHistories->isNotEmpty())
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted/40 text-caption font-bold text-text-muted uppercase tracking-wider">
                                <th class="py-3 px-4">Material</th>
                                <th class="py-3 px-4 text-right">Harga Lama</th>
                                <th class="py-3 px-4 text-right">Harga Baru</th>
                                <th class="py-3 px-4 text-center">Satuan</th>
                                <th class="py-3 px-4">Changed At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach($recentHistories as $item)
                                @php
                                    $h = $item['history'];
                                    $wp = $item['price'];
                                    $unit = $h->unit ?? $wp->wasteCategory->unit ?? 'Kg';
                                @endphp
                                <tr class="hover:bg-surface-muted/40 transition-subtle">
                                    <td class="py-3.5 px-4">
                                        <span class="text-body-sm font-bold text-text block">{{ $wp->wasteCategory->name ?? '-' }}</span>
                                        <span class="font-mono text-caption text-text-muted block">{{ $wp->wasteCategory->code ?? '-' }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        @if($h->old_price !== null)
                                            <span class="font-mono text-body-sm text-text-muted line-through">
                                                Rp {{ number_format($h->old_price, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <x-ui.badge variant="info" size="sm">Harga Awal</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <span class="font-mono text-body-sm font-bold text-text">
                                            Rp {{ number_format($h->new_price, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="font-mono text-caption uppercase px-2 py-0.5 rounded bg-surface-muted border border-border text-text font-semibold">
                                            {{ $unit }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="text-body-sm text-text font-medium">{{ $h->changed_at?->format('d M Y H:i') ?? '-' }}</div>
                                        <div class="text-caption text-text-muted">{{ $h->changed_at?->diffForHumans() }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="block md:hidden divide-y divide-border-subtle p-3 space-y-3">
                    @foreach($recentHistories as $item)
                        @php
                            $h = $item['history'];
                            $wp = $item['price'];
                            $unit = $h->unit ?? $wp->wasteCategory->unit ?? 'Kg';
                        @endphp
                        <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-body font-bold text-text block">{{ $wp->wasteCategory->name ?? '-' }}</span>
                                    <span class="font-mono text-caption text-text-muted">{{ $wp->wasteCategory->code ?? '-' }}</span>
                                </div>
                                <span class="font-mono text-caption uppercase px-2 py-0.5 rounded bg-surface-muted border border-border text-text font-semibold">
                                    {{ $unit }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-caption pt-1 border-t border-border-subtle">
                                <div>
                                    <span class="text-text-muted uppercase font-bold block">Harga Lama</span>
                                    @if($h->old_price !== null)
                                        <span class="font-mono font-medium text-text-muted line-through block">
                                            Rp {{ number_format($h->old_price, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="block mt-0.5"><x-ui.badge variant="info" size="sm">Harga Awal</x-ui.badge></span>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-text-muted uppercase font-bold block">Harga Baru</span>
                                    <span class="font-mono font-bold text-body-sm text-text block">
                                        Rp {{ number_format($h->new_price, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-caption text-text-muted pt-1 border-t border-border-subtle">
                                Diubah {{ $h->changed_at?->format('d M Y H:i') ?? '-' }} ({{ $h->changed_at?->diffForHumans() }})
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-10 px-4 text-center border-2 border-dashed border-border rounded-radius-md m-4 flex flex-col items-center justify-center space-y-2.5">
                    <div>
                        <p class="text-body-sm font-bold text-text">Belum ada riwayat perubahan harga.</p>
                        <p class="text-caption text-text-muted mt-0.5 max-w-md mx-auto">
                            Riwayat tercatat otomatis setiap harga lapak ditambahkan atau diubah.
                        </p>
                    </div>
                </div>
            @endif
        </x-ui.card>

        <div x-show="showAddModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div x-show="showAddModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
                 @click="showAddModal = false"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-3 sm:p-4 text-center sm:p-0">
                <div x-show="showAddModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-radius-lg bg-surface border border-border text-left shadow-popover transition-all sm:my-8 sm:w-full sm:max-w-lg p-5 sm:p-6 space-y-4">
                    
                    <div class="flex items-start justify-between border-b border-border-subtle pb-3">
                        <div>
                            <h3 class="text-heading-sm font-bold text-text">Tambah Harga Lapak</h3>
                            <p class="text-caption text-text-muted mt-0.5">Petakan harga beli agregator untuk material dari Master Sampah</p>
                        </div>
                        <button type="button" @click="showAddModal = false" aria-label="Tutup dialog tambah harga" class="min-w-[44px] min-h-[44px] p-2.5 -m-1 rounded-radius-sm text-text-muted hover:text-text hover:bg-surface-muted transition-subtle flex items-center justify-center cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('bank-sampah.collectors.prices.store', $collector) }}" class="space-y-4">
                        @csrf

                        {{-- Pilih Material dari Master Material --}}
                        <div class="space-y-1">
                            <x-input-label for="waste_category_id" value="Pilih Material (Master Sampah)" :required="true" />
                            <select name="waste_category_id"
                                    id="waste_category_id"
                                    required
                                    x-model.number="selectedCategory"
                                    @change="onCategorySelect()"
                                    class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text px-3 py-2.5 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                                <option value="">-- Pilih Material dari Master --</option>
                                @foreach($categories as $cat)
                                    @php
                                        $alreadyHasPrice = $collector->wastePrices->contains('waste_category_id', $cat->id);
                                    @endphp
                                    <option value="{{ $cat->id }}" @disabled($alreadyHasPrice)>
                                        {{ $cat->name }} ({{ $cat->code ?? '-' }}) — Satuan: {{ $cat->unit }} {{ $alreadyHasPrice ? '[Sudah ada harga]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Satuan Mengikuti Master (Read-only Indicator) --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">Satuan Ukuran</span>
                                <div class="px-3 py-2 rounded-radius-md bg-surface-muted border border-border font-mono text-body-sm font-semibold text-text" x-text="categoryUnit || '-'"></div>
                                <span class="text-caption text-text-muted">Mengikuti master kategori</span>
                            </div>

                            {{-- Input Harga Lapak --}}
                            <div class="space-y-1">
                                <x-input-label for="collector_price" value="Harga Lapak (Rp)" :required="true" />
                                <x-text-input type="number"
                                              name="collector_price"
                                              id="collector_price"
                                              step="1"
                                              min="0"
                                              placeholder="Contoh: 2500"
                                              required />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-border-subtle">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-radius-md border border-border bg-surface text-text hover:bg-surface-muted text-body-sm font-semibold transition-subtle">
                                Batal
                            </button>
                            <x-ui.button type="submit" variant="primary" size="md">
                                Simpan Harga
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- MODAL 2: EDIT HARGA LAPAK                                 --}}
        {{-- ========================================================= --}}
        <div x-show="showEditModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div x-show="showEditModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
                 @click="showEditModal = false"></div>

            <div class="flex min-h-full items-end sm:items-center justify-center p-3 sm:p-4 text-center sm:p-0">
                <div x-show="showEditModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-radius-lg bg-surface border border-border text-left shadow-popover transition-all sm:my-8 sm:w-full sm:max-w-lg p-5 sm:p-6 space-y-4">
                    
                    <div class="flex items-start justify-between border-b border-border-subtle pb-3">
                        <div>
                            <h3 class="text-heading-sm font-bold text-text">Edit Harga Lapak</h3>
                            <p class="text-caption text-text-muted mt-0.5">Perbarui harga agregator untuk material ini</p>
                        </div>
                        <button type="button" @click="showEditModal = false" aria-label="Tutup dialog edit harga" class="min-w-[44px] min-h-[44px] p-2.5 -m-1 rounded-radius-sm text-text-muted hover:text-text hover:bg-surface-muted transition-subtle flex items-center justify-center cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" :action="editActionUrl" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">Jenis Barang</span>
                            <div class="mt-1 p-3 rounded-radius-md bg-surface-muted border border-border flex items-center justify-between">
                                <span class="font-bold text-body-sm text-text" x-text="editMaterialName"></span>
                                <span class="font-mono text-caption text-text-muted" x-text="'Satuan: ' + editUnit"></span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <x-input-label for="edit_collector_price" value="Harga Lapak Baru (Rp)" :required="true" />
                            <x-text-input type="number"
                                          name="collector_price"
                                          id="edit_collector_price"
                                          step="1"
                                          min="0"
                                          x-model="editPrice"
                                          required />
                            <p class="text-caption text-text-muted">Perubahan harga akan otomatis dicatat ke riwayat harga (Price History).</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-border-subtle">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-radius-md border border-border bg-surface text-text hover:bg-surface-muted text-body-sm font-semibold transition-subtle">
                                Batal
                            </button>
                            <x-ui.button type="submit" variant="primary" size="md">
                                Perbarui Harga
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Confirmation Modal for Delete Price --}}
        <x-confirm-delete-modal id="confirm-delete-price-modal" />
    </div>

    {{-- Script Logika Alpine --}}
    <script>
        function collectorPricesManager() {
            const categories = @json($categories->map(fn($c) => ['id' => $c->id, 'unit' => $c->unit]));

            return {
                categories: categories,
                showAddModal: false,
                showEditModal: false,
                selectedCategory: '',
                categoryUnit: '',
                editPriceId: null,
                editMaterialName: '',
                editUnit: '',
                editPrice: 0,
                editActionUrl: '',

                openAddModal() {
                    this.selectedCategory = '';
                    this.categoryUnit = '';
                    this.showAddModal = true;
                },

                onCategorySelect() {
                    const found = this.categories.find(c => c.id == this.selectedCategory);
                    this.categoryUnit = found ? found.unit : '';
                },

                openEditModal(id, name, unit, price) {
                    this.editPriceId = id;
                    this.editMaterialName = name;
                    this.editUnit = unit;
                    this.editPrice = price;
                    this.editActionUrl = `{{ url('bank-sampah/collectors/' . $collector->id . '/prices') }}/${id}`;
                    this.showEditModal = true;
                }
            };
        }
    </script>
</x-layouts.dashboard>
