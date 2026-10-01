<x-layouts.dashboard title="Setoran / Timbangan Sampah">
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span>Setoran / Timbangan Sampah</span>
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Riwayat transaksi penimbangan sampah dari nasabah
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                @can('manage_waste_customers')
                    <a href="{{ route('bank-sampah.customers.index') }}" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition px-2 py-1">Data Nasabah &rarr;</a>
                @endcan
                <a href="{{ route('bank-sampah.deposits.import') }}" class="inline-flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 rounded-lg text-sm font-semibold shadow-xs transition w-full sm:w-auto justify-center">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import Excel
                </a>
                <a href="{{ route('bank-sampah.deposits.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition w-full sm:w-auto justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Catat Setoran Baru
                </a>
            </div>
        </div>

        {{-- Flash Notification --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        {{-- Main Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            
            {{-- Compact Filter & Search Toolbar --}}
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                <form action="{{ route('bank-sampah.deposits.index') }}" method="GET" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                    {{-- Pencarian Nama / Kode Nasabah --}}
                    <div class="flex-1 min-w-[240px]">
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search ?? '' }}" 
                                   placeholder="Cari nama atau kode nasabah..." 
                                   class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Filter Rentang Tanggal --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1 text-xs">
                            <span class="text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px]">Dari:</span>
                            <input type="date" 
                                   name="start_date" 
                                   value="{{ $startDate ?? '' }}" 
                                   class="border-0 p-0 text-xs bg-transparent text-slate-800 dark:text-slate-200 focus:ring-0">
                        </div>
                        <div class="flex items-center gap-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2.5 py-1 text-xs">
                            <span class="text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px]">Sampai:</span>
                            <input type="date" 
                                   name="end_date" 
                                   value="{{ $endDate ?? '' }}" 
                                   class="border-0 p-0 text-xs bg-transparent text-slate-800 dark:text-slate-200 focus:ring-0">
                        </div>

                        {{-- Tombol Filter --}}
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            Filter
                        </button>

                        {{-- Tombol Reset jika filter aktif --}}
                        @if(!empty($search) || !empty($startDate) || !empty($endDate))
                            <a href="{{ route('bank-sampah.deposits.index') }}" 
                               class="text-xs text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 px-2 py-1 transition font-medium">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- DESKTOP VIEW: Tabel dengan Hirarki Jelas (md ke atas) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/60 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="py-3.5 px-4 w-36">Tanggal</th>
                            <th scope="col" class="py-3.5 px-4 min-w-[180px]">Nasabah</th>
                            <th scope="col" class="py-3.5 px-4 min-w-[200px]">Material</th>
                            <th scope="col" class="py-3.5 px-4 w-32 text-right">Total Berat</th>
                            <th scope="col" class="py-3.5 px-4 w-36 text-right">Total Setoran</th>
                            <th scope="col" class="py-3.5 px-4 w-36 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse($deposits as $d)
                            @php
                                $detailCount = $d->details->count();
                                $materialNames = $d->details->map(fn($item) => $item->wasteCategory->name ?? null)->filter()->unique()->values();
                                $totalWeight = $d->details->sum('weight');
                            @endphp
                            <tbody x-data="{ expanded: false }" class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                    {{-- Kolom Tanggal & ID --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                            {{ $d->date ? $d->date->translatedFormat('d M Y') : '-' }}
                                        </div>
                                        <div class="text-[11px] font-mono text-slate-400 dark:text-slate-500 mt-0.5">
                                            DEP-{{ str_pad($d->id, 5, '0', STR_PAD_LEFT) }}
                                        </div>
                                    </td>

                                    {{-- Kolom Nasabah (Anchor Visual Utama) --}}
                                    <td class="py-3.5 px-4">
                                        <div class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                            {{ $d->wasteCustomer->name ?? 'Nasabah Umum' }}
                                        </div>
                                        @if($d->wasteCustomer && $d->wasteCustomer->customer_code)
                                            <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-0.5">
                                                {{ $d->wasteCustomer->customer_code }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Kolom Material (Compact Summary) --}}
                                    <td class="py-3.5 px-4">
                                        @if($detailCount > 0)
                                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                                {{ $detailCount }} Jenis Material
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-xs mt-0.5" title="{{ $materialNames->join(', ') }}">
                                                @if($materialNames->count() <= 3)
                                                    {{ $materialNames->join(' · ') }}
                                                @else
                                                    {{ $materialNames->take(3)->join(' · ') }} 
                                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">+{{ $materialNames->count() - 3 }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Tanpa item</span>
                                        @endif

                                        @if($d->notes)
                                            <div class="text-[11px] text-slate-400 dark:text-slate-500 italic truncate max-w-xs mt-1" title="{{ $d->notes }}">
                                                "{{ $d->notes }}"
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Kolom Total Berat (Rata Kanan) --}}
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <span class="font-mono text-sm font-bold text-slate-800 dark:text-slate-200">
                                            {{ number_format($totalWeight, 2, ',', '.') }}
                                        </span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">kg</span>
                                    </td>

                                    {{-- Kolom Total Setoran (Rata Kanan, Menonjol) --}}
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <span class="font-mono text-sm font-extrabold text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($d->total_amount, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    {{-- Kolom Aksi --}}
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center justify-end gap-2 text-xs font-semibold">
                                            {{-- Toggle Detail --}}
                                            <button type="button" 
                                                    @click="expanded = !expanded" 
                                                    class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition px-2 py-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800">
                                                <span x-text="expanded ? 'Tutup' : 'Rincian'"></span>
                                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>

                                            <a href="{{ route('bank-sampah.deposits.edit', $d) }}" 
                                               class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition px-2 py-1 rounded hover:bg-emerald-50 dark:hover:bg-emerald-950/40">
                                                Edit
                                            </a>

                                            <button type="button" 
                                                    @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.deposits.destroy', $d) }}'})" 
                                                    class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-300 transition px-2 py-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/40">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Expandable Drawer Rincian Timbangan --}}
                                <tr x-show="expanded" x-cloak class="bg-slate-50/90 dark:bg-slate-800/60">
                                    <td colspan="6" class="py-3 px-6">
                                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 space-y-3">
                                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                                    Rincian Material — DEP-{{ str_pad($d->id, 5, '0', STR_PAD_LEFT) }} ({{ $d->wasteCustomer->name ?? '-' }})
                                                </span>
                                                @if($d->notes)
                                                    <span class="text-xs text-slate-500 italic">Catatan: {{ $d->notes }}</span>
                                                @endif
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                                @foreach($d->details as $det)
                                                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/70 border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                                                        <div>
                                                            <div class="font-bold text-slate-800 dark:text-slate-200">
                                                                {{ $det->wasteCategory->name ?? 'Material' }}
                                                            </div>
                                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                                                                {{ number_format($det->weight, 2, ',', '.') }} {{ $det->wasteCategory->unit ?? 'kg' }} @ Rp {{ number_format($det->price_per_unit, 0, ',', '.') }}
                                                            </div>
                                                        </div>
                                                        <div class="text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                                            Rp {{ number_format($det->subtotal, 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-4 text-center">
                                    <div class="max-w-xs mx-auto space-y-3">
                                        <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        @if(!empty($search) || !empty($startDate) || !empty($endDate))
                                            <div>
                                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Tidak ada setoran yang sesuai filter.</p>
                                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Coba ubah kata kunci pencarian atau tanggal.</p>
                                            </div>
                                            <a href="{{ route('bank-sampah.deposits.index') }}" class="inline-flex items-center text-xs font-semibold text-emerald-600 hover:underline">
                                                Tampilkan Semua Setoran
                                            </a>
                                        @else
                                            <div>
                                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Belum ada transaksi setoran.</p>
                                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Mulai catat penimbangan sampah nasabah hari ini.</p>
                                            </div>
                                            <a href="{{ route('bank-sampah.deposits.create') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 text-white text-xs font-semibold px-3 py-2 rounded-lg hover:bg-emerald-700 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Catat Setoran Sekarang
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE VIEW: Card List Bersih Tanpa Overflow Horisontal (< md) --}}
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                @forelse($deposits as $d)
                    @php
                        $detailCount = $d->details->count();
                        $materialNames = $d->details->map(fn($item) => $item->wasteCategory->name ?? null)->filter()->unique()->values();
                        $totalWeight = $d->details->sum('weight');
                    @endphp
                    <div x-data="{ expanded: false }" class="p-4 space-y-3">
                        {{-- Card Header: Nasabah & Tanggal --}}
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-base font-bold text-slate-900 dark:text-slate-100">
                                    {{ $d->wasteCustomer->name ?? 'Nasabah Umum' }}
                                </h4>
                                @if($d->wasteCustomer && $d->wasteCustomer->customer_code)
                                    <p class="text-xs font-mono text-slate-400">
                                        {{ $d->wasteCustomer->customer_code }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $d->date ? $d->date->translatedFormat('d M Y') : '-' }}
                                </span>
                                <span class="block text-[10px] font-mono text-slate-400 mt-0.5">
                                    DEP-{{ str_pad($d->id, 5, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                        </div>

                        {{-- Ringkasan Metrik --}}
                        <div class="grid grid-cols-2 gap-2 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-800">
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400">Total Berat</span>
                                <span class="font-mono text-sm font-bold text-slate-800 dark:text-slate-200">
                                    {{ number_format($totalWeight, 2, ',', '.') }} kg
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[10px] uppercase font-bold text-emerald-700 dark:text-emerald-400">Total Nilai</span>
                                <span class="font-mono text-sm font-extrabold text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($d->total_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Material Chips --}}
                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">
                                {{ $detailCount }} Material:
                            </span>
                            @if($materialNames->count() > 0)
                                <span class="text-slate-500 dark:text-slate-400">
                                    {{ $materialNames->take(3)->join(', ') }}
                                    @if($materialNames->count() > 3)
                                        <span class="font-bold text-emerald-600">+{{ $materialNames->count() - 3 }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-slate-400 italic">Tanpa item</span>
                            @endif
                        </div>

                        @if($d->notes)
                            <p class="text-xs text-slate-500 dark:text-slate-400 italic">
                                "{{ $d->notes }}"
                            </p>
                        @endif

                        {{-- Rincian Expandable pada Mobile --}}
                        <div x-show="expanded" x-cloak class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Item Timbangan:</p>
                            @foreach($d->details as $det)
                                <div class="flex items-center justify-between text-xs py-1 border-b border-dashed border-slate-100 dark:border-slate-800 last:border-b-0">
                                    <div>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $det->wasteCategory->name ?? 'Material' }}</span>
                                        <span class="text-[10px] text-slate-400 block font-mono">
                                            {{ number_format($det->weight, 2, ',', '.') }} kg @ Rp {{ number_format($det->price_per_unit, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($det->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Actions Footer --}}
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-xs font-semibold">
                            <button type="button" 
                                    @click="expanded = !expanded" 
                                    class="text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 flex items-center gap-1">
                                <span x-text="expanded ? 'Sembunyikan' : 'Lihat Rincian'"></span>
                                <svg class="w-3.5 h-3.5 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('bank-sampah.deposits.edit', $d) }}" 
                                   class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 font-bold">
                                    Edit
                                </a>
                                <button type="button" 
                                        @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.deposits.destroy', $d) }}'})" 
                                        class="text-rose-600 dark:text-rose-400 hover:text-rose-800">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-sm">
                        Belum ada data transaksi setoran.
                    </div>
                @endforelse
            </div>

            {{-- Pagination Footer --}}
            @if($deposits->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                    {{ $deposits->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus Existing --}}
    <x-confirm-delete-modal />
</x-layouts.dashboard>