<x-layouts.dashboard title="Laporan Penjualan Agregator">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Laporan Penjualan Agregator</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau rincian hasil penjualan sampah terpilah ke agregator agregator/pengepul</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('bank-sampah.reports.sales.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak / Simpan PDF
                </a>
                <a href="{{ route('bank-sampah.reports.sales.excel', request()->query()) }}" class="inline-flex items-center gap-1.5 bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Ekspor Excel
                </a>
            </div>
        </div>

        @if($errors->has('date_range'))
            <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-xl">
                {{ $errors->first('date_range') }}
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wide">Total Transaksi</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($totalSales, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold uppercase tracking-wide">Total Berat Terjual</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($totalWeight, 2, ',', '.') }} <span class="text-xs font-normal">kg</span></p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-blue-600 dark:text-blue-400 font-semibold uppercase tracking-wide">Total Pendapatan</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold uppercase tracking-wide">Agregator Terbanyak</p>
                <p class="text-base font-bold text-slate-900 dark:text-white mt-2 truncate">{{ $topAgregatorName }}</p>
            </div>
        </div>

        <!-- Sticky Filter Bar -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-4 transition-colors duration-300">
            <form action="{{ route('bank-sampah.reports.sales') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Agregator</label>
                    <select name="collector_id" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Semua Agregator</option>
                        @foreach($collectors as $c)
                            <option value="{{ $c->id }}" {{ $collectorId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Grup Kategori</label>
                    <select name="waste_category_group_id" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Semua Grup</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" {{ $groupId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 md:col-span-1 flex items-end">
                    <button type="submit" class="w-full bg-emerald-600 dark:bg-emerald-500 text-white font-semibold text-sm px-4 py-2.5 rounded-lg hover:bg-emerald-700 transition">
                        Filter Laporan
                    </button>
                </div>
            </form>
        </div>

        <!-- Data List -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <!-- DESKTOP VIEW (Table) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Kode Penjualan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Agregator</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Kategori</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase">Berat</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase">Harga Jual</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($details as $d)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                    {{ $d->sale->date ? $d->sale->date->toDateString() : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    SAL-{{ str_pad($d->sale_id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 dark:text-slate-100">
                                    {{ $d->sale->collector->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $d->wasteCategory->name ?? '-' }}</div>
                                    <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">{{ $d->wasteCategory->wasteCategoryGroup->name ?? 'Legacy/Belum Grup' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ number_format($d->weight, 2, ',', '.') }} kg
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-slate-600 dark:text-slate-400">
                                    Rp {{ number_format($d->price_per_unit, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400 text-sm">
                                    Tidak ada data penjualan dalam periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MOBILE VIEW (Card List Collapse) -->
            <div class="block sm:hidden divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                @forelse($details as $d)
                    <div class="p-4 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 font-mono">SAL-{{ str_pad($d->sale_id, 5, '0', STR_PAD_LEFT) }}</span>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 mt-0.5">
                                    {{ $d->sale->collector->name ?? '-' }}
                                </h4>
                            </div>
                            <span class="text-xs text-slate-500">{{ $d->sale->date ? $d->sale->date->toDateString() : '-' }}</span>
                        </div>
                        <div class="border-t border-dashed border-slate-100 dark:border-slate-800 my-2"></div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Kategori:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $d->wasteCategory->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Berat / Harga Jual:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ number_format($d->weight, 2, ',', '.') }} kg @ Rp {{ number_format($d->price_per_unit, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs pt-1">
                            <span class="text-slate-500 font-semibold">Subtotal:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                        Tidak ada data penjualan dalam periode ini.
                    </div>
                @endforelse
            </div>

            @if($details->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    {{ $details->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
