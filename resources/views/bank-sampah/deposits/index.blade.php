<x-layouts.dashboard title="Setoran / Timbangan Sampah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header --}}
        <x-ui.page-header title="Setoran Sampah" description="Riwayat transaksi penimbangan sampah nasabah dan mutasi buku tabungan">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Setoran</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                @can('manage_waste_customers')
                    <a href="{{ route('bank-sampah.customers.index') }}" class="text-body-sm font-semibold text-text-muted hover:text-primary transition-subtle px-1 py-1 inline-flex items-center min-h-[44px]">
                        Data Nasabah &rarr;
                    </a>
                @endcan
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.deposits.import') }}'">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Excel
                </x-ui.button>
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.deposits.create') }}'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Catat Setoran Baru
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Search & Date Range Filter Toolbar --}}
        <x-ui.card variant="bordered" class="p-4 sm:p-5">
            <form action="{{ route('bank-sampah.deposits.index') }}" method="GET" data-live-search class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                {{-- Pencarian Nama / Kode Nasabah / Catatan --}}
                <div class="flex-1 min-w-[260px]">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input type="text"
                               name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Cari nama nasabah, kode (NSB-...), atau catatan..."
                               class="w-full text-body rounded-radius-md border border-border bg-surface text-text pl-10 pr-4 py-2 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                    </div>
                </div>

                {{-- Filter Rentang Tanggal --}}
                <div class="flex flex-wrap items-center gap-2.5">
                    <div class="flex items-center gap-2 bg-surface rounded-radius-md border border-border px-3 py-1.5 shadow-subtle">
                        <span class="text-body-sm font-semibold text-text-muted uppercase">Dari:</span>
                        <input type="date"
                               name="start_date"
                               value="{{ $startDate ?? '' }}"
                               class="border-0 p-0 text-body bg-transparent text-text focus:ring-0">
                    </div>

                    <div class="flex items-center gap-2 bg-surface rounded-radius-md border border-border px-3 py-1.5 shadow-subtle">
                        <span class="text-body-sm font-semibold text-text-muted uppercase">Sampai:</span>
                        <input type="date"
                               name="end_date"
                               value="{{ $endDate ?? '' }}"
                               class="border-0 p-0 text-body bg-transparent text-text focus:ring-0">
                    </div>

                    {{-- Tombol Filter --}}
                    <x-ui.button type="submit" variant="secondary" size="md">
                        Filter
                    </x-ui.button>

                    {{-- Tombol Reset jika filter aktif --}}
                    <a href="{{ route('bank-sampah.deposits.index') }}" data-live-reset
                       class="text-body font-semibold text-text-muted hover:text-danger px-2.5 py-2 transition-subtle inline-flex items-center min-h-[44px] {{ (!empty($search) || !empty($startDate) || !empty($endDate)) ? '' : 'hidden' }}">
                        Reset
                    </a>
                </div>
            </form>
        </x-ui.card>

        {{-- Main Table / Card Container --}}
        @if($deposits->count() > 0)
            <x-ui.card id="table-section" variant="bordered" class="p-0 overflow-hidden scroll-mt-20">
                {{-- DESKTOP VIEW: Dense Operational Table (md+) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted/40 text-body-sm font-bold text-text-muted uppercase tracking-wider">
                                <th scope="col" class="py-3.5 px-4 w-36">Tanggal</th>
                                <th scope="col" class="py-3.5 px-4 min-w-[200px]">Nasabah</th>
                                <th scope="col" class="py-3.5 px-4 min-w-[180px]">Jumlah Material</th>
                                <th scope="col" class="py-3.5 px-4 w-36 text-right">Total Berat</th>
                                <th scope="col" class="py-3.5 px-4 w-40 text-right">Nilai Setoran</th>
                                <th scope="col" class="py-3.5 px-4 w-44 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach($deposits as $d)
                                @php
                                    $detailCount = $d->details->count();
                                    $totalWeight = $d->details->sum('weight');
                                @endphp
                                    <tr class="hover:bg-surface-muted/50 transition-subtle">
                                        {{-- Kolom Tanggal & ID --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <div class="text-body font-semibold text-text">
                                                {{ $d->date ? $d->date->translatedFormat('d M Y') : '-' }}
                                            </div>
                                            <div class="font-mono text-body-sm text-text-muted mt-0.5">
                                                DEP-{{ str_pad($d->id, 5, '0', STR_PAD_LEFT) }}
                                            </div>
                                        </td>

                                        {{-- Kolom Nasabah (Anchor Visual Utama) --}}
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-body-sm shrink-0 shadow-subtle">
                                                    {{ strtoupper(substr($d->wasteCustomer->name ?? 'N', 0, 1)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-body font-bold text-text truncate">
                                                        {{ $d->wasteCustomer->name ?? 'Nasabah Umum' }}
                                                    </div>
                                                    @if($d->wasteCustomer && $d->wasteCustomer->customer_code)
                                                        <div class="font-mono text-body-sm text-text-muted font-semibold mt-0.5">
                                                            {{ $d->wasteCustomer->customer_code }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Kolom Jumlah Material (jumlah DepositDetail transaksi) --}}
                                        <td class="py-3.5 px-4">
                                            @if($detailCount > 0)
                                                <span class="text-body font-semibold text-text">
                                                    {{ $detailCount }} Material
                                                </span>
                                            @else
                                                <span class="text-body text-text-muted italic">Tanpa item</span>
                                            @endif
                                        </td>

                                        {{-- Kolom Total Berat (Rata Kanan) --}}
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <span class="font-mono text-body font-bold text-text">
                                                {{ number_format($totalWeight, 2, ',', '.') }}
                                            </span>
                                            <span class="text-body-sm text-text-muted font-medium ml-0.5">kg</span>
                                        </td>

                                        {{-- Kolom Total Setoran (Rata Kanan, Menonjol) --}}
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <span class="font-mono text-body font-extrabold text-primary">
                                                Rp {{ number_format($d->total_amount, 0, ',', '.') }}
                                            </span>
                                        </td>

                                        {{-- Kolom Aksi --}}
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center justify-end gap-3 text-body font-semibold">
                                                <a href="{{ route('bank-sampah.deposits.show', $d) }}"
                                                   class="text-primary hover:underline transition-subtle">
                                                    Detail
                                                </a>

                                                <a href="{{ route('bank-sampah.deposits.edit', $d) }}"
                                                   class="text-text-muted hover:text-text transition-subtle">
                                                    Edit
                                                </a>

                                                <button type="button" 
                                                        @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.deposits.destroy', $d) }}'})" 
                                                        class="text-danger hover:underline transition-subtle cursor-pointer">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE VIEW: Adaptive Cards (< md) --}}
                <div class="block md:hidden divide-y divide-border-subtle p-3 space-y-3">
                    @foreach($deposits as $d)
                        @php
                            $detailCount = $d->details->count();
                            $totalWeight = $d->details->sum('weight');
                        @endphp
                        <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                            {{-- Card Header: Nasabah & Tanggal --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-body shrink-0 shadow-subtle">
                                        {{ strtoupper(substr($d->wasteCustomer->name ?? 'N', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-body font-bold text-text truncate">
                                            {{ $d->wasteCustomer->name ?? 'Nasabah Umum' }}
                                        </h4>
                                        @if($d->wasteCustomer && $d->wasteCustomer->customer_code)
                                            <p class="font-mono text-body-sm text-text-muted font-semibold">
                                                {{ $d->wasteCustomer->customer_code }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-body font-semibold text-text block">
                                        {{ $d->date ? $d->date->translatedFormat('d M Y') : '-' }}
                                    </span>
                                    <span class="font-mono text-body-sm text-text-muted mt-0.5 block">
                                        DEP-{{ str_pad($d->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Ringkasan Metrik (Berat & Nilai) --}}
                            <div class="grid grid-cols-2 gap-2 p-3 bg-surface-muted rounded-radius-md border border-border-subtle">
                                <div>
                                    <span class="block text-body-sm uppercase font-bold text-text-muted">Total Berat</span>
                                    <span class="font-mono text-body font-bold text-text mt-0.5 block">
                                        {{ number_format($totalWeight, 2, ',', '.') }} kg
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-body-sm uppercase font-bold text-primary">Total Nilai</span>
                                    <span class="font-mono text-heading-sm font-bold text-primary mt-0.5 block">
                                        Rp {{ number_format($d->total_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Material Summary --}}
                            <div class="text-body">
                                @if($detailCount > 0)
                                    <span class="font-bold text-text">
                                        {{ $detailCount }} Material
                                    </span>
                                @else
                                    <span class="text-text-muted italic">Tanpa item</span>
                                @endif
                            </div>

                            {{-- Actions Footer --}}
                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-border-subtle text-body">
                                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.deposits.show', $d) }}'">
                                    Detail
                                </x-ui.button>
                                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.deposits.edit', $d) }}'">
                                    Edit
                                </x-ui.button>
                                <x-ui.button variant="ghost" size="sm" class="text-danger" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.deposits.destroy', $d) }}'})">
                                    Hapus
                                </x-ui.button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination Footer --}}
                @if($deposits->hasPages())
                    <div class="p-4 border-t border-border-subtle bg-surface-muted/30">
                        {{ $deposits->links() }}
                    </div>
                @endif
            </x-ui.card>
        @else
            {{-- HONEST EMPTY STATES --}}
            @if(!empty($search) || !empty($startDate) || !empty($endDate))
                {{-- Search / Filter Empty State --}}
                <div id="table-section" class="p-10 text-center border-2 border-dashed border-border rounded-radius-lg bg-surface flex flex-col items-center justify-center space-y-3 scroll-mt-20">
                    <div class="w-12 h-12 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-heading-sm font-bold text-text">Setoran Tidak Ditemukan</h3>
                        <p class="text-body-sm text-text-muted mt-1 max-w-md mx-auto">
                            Tidak ada riwayat setoran yang cocok dengan kata kunci atau rentang tanggal yang dipilih.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="{{ route('bank-sampah.deposits.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-radius-md bg-surface border border-border text-body font-semibold text-text hover:bg-surface-muted transition-subtle">
                            Reset Filter
                        </a>
                        <a href="{{ route('bank-sampah.deposits.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-radius-md bg-primary text-white text-body font-semibold hover:bg-primary-hover shadow-subtle transition-subtle">
                            + Catat Setoran Baru
                        </a>
                    </div>
                </div>
            @else
                {{-- Zero Database Empty State --}}
                <div id="table-section" class="p-12 text-center border-2 border-dashed border-border rounded-radius-lg bg-surface flex flex-col items-center justify-center space-y-3 scroll-mt-20">
                    <div class="w-14 h-14 rounded-full bg-primary-light text-primary flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-heading-sm font-bold text-text">Belum Ada Riwayat Setoran</h3>
                        <p class="text-body-sm text-text-muted mt-1 max-w-md mx-auto">
                            Mulai mencatat penimbangan sampah nasabah aktif hari ini untuk mengisi pembukuan tabungan warga.
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('bank-sampah.deposits.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-radius-md bg-primary text-white text-body font-semibold hover:bg-primary-hover shadow-subtle transition-subtle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            + Catat Setoran Pertama
                        </a>
                    </div>
                </div>
            @endif
        @endif

        {{-- Modal Konfirmasi Hapus --}}
        <x-confirm-delete-modal />
    </div>
</x-layouts.dashboard>
