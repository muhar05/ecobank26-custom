<x-layouts.dashboard title="Laporan Arus Kas Bank Sampah">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Laporan Arus Kas</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau performa finansial operasional kas Bank Sampah (Penjualan vs Pengeluaran Operasional)</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('bank-sampah.reports.cashflow.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak / Simpan PDF
                </a>
                <a href="{{ route('bank-sampah.reports.cashflow.excel', request()->query()) }}" class="inline-flex items-center gap-1.5 bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition">
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold uppercase tracking-wide">Total Pemasukan (Penjualan)</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-rose-600 dark:text-rose-400 font-semibold uppercase tracking-wide">Total Pengeluaran Operasional</p>
                <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wide">Net Flow Keuangan</p>
                <p class="text-2xl font-bold {{ $saldoAkhir >= 0 ? 'text-slate-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }} mt-1">
                    Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Sticky Filter Bar -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-4 transition-colors duration-300">
            <form action="{{ route('bank-sampah.reports.cashflow') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-emerald-600 dark:bg-emerald-500 text-white font-semibold text-sm px-4 py-2.5 rounded-lg hover:bg-emerald-700 transition">
                        Filter Laporan
                    </button>
                </div>
            </form>
        </div>

        <!-- Monthly Summary Breakdown -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Ikhtisar Bulanan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($months as $monthKey => $m)
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-150 dark:border-slate-800 flex flex-col justify-between shadow-xs transition hover:shadow-md">
                        <div>
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500">{{ $m['name'] }}</span>
                            <div class="border-t border-slate-200 dark:border-slate-700 my-2"></div>
                            <div class="flex justify-between text-xs my-1">
                                <span class="text-slate-500">Pemasukan:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($m['pemasukan'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs my-1">
                                <span class="text-slate-500">Pengeluaran:</span>
                                <span class="font-bold text-rose-600 dark:text-rose-400 font-medium">Rp {{ number_format($m['pengeluaran'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="border-t border-dashed border-slate-200 dark:border-slate-700 my-2"></div>
                        <div class="flex justify-between text-xs">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Net Flow:</span>
                            <span class="font-bold {{ $m['net'] >= 0 ? 'text-slate-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                                Rp {{ number_format($m['net'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Ledger/Cashbook -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Buku Kas Operasional</h3>
            </div>
            
            <!-- DESKTOP VIEW -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Kode</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Tipe</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase">Deskripsi</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($cashbook as $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                    {{ $item['date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $item['code'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($item['is_in'])
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                            Pemasukan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400">
                                            Pengeluaran
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 max-w-sm truncate">
                                    {{ $item['description'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold {{ $item['is_in'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $item['is_in'] ? '+' : '-' }} Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400 text-sm">
                                    Tidak ada data kas operasional dalam periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MOBILE VIEW -->
            <div class="block sm:hidden divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                @forelse($cashbook as $item)
                    <div class="p-4 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 font-mono">{{ $item['code'] }}</span>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 mt-0.5">
                                    {{ $item['description'] }}
                                </h4>
                            </div>
                            <span class="text-xs text-slate-500">{{ $item['date'] }}</span>
                        </div>
                        <div class="border-t border-dashed border-slate-100 dark:border-slate-800 my-2"></div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Tipe:</span>
                            <span class="font-semibold {{ $item['is_in'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $item['type'] }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs pt-1">
                            <span class="text-slate-500 font-semibold">Nominal:</span>
                            <span class="font-bold {{ $item['is_in'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $item['is_in'] ? '+' : '-' }} Rp {{ number_format($item['amount'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                        Tidak ada data kas operasional dalam periode ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.dashboard>
