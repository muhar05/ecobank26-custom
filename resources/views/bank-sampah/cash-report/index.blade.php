<x-layouts.dashboard title="Kas Bank Sampah">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Laporan Kas Bank Sampah</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Laporan arus kas masuk (margin/operasional) dan keluar</p>
            </div>
            <div class="flex items-center">
                <a href="{{ route('bank-sampah.cash-report.export', request()->only(['date_from', 'date_to'])) }}" class="inline-flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition w-full sm:w-auto justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Export Laporan
                </a>
            </div>
        </div>

        {{-- Premium Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex flex-col justify-center relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-full blur-2xl group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/40 transition-colors duration-500"></div>
                <div class="relative">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Total Kas Masuk
                    </p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-slate-100 mt-2">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex flex-col justify-center relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-50 dark:bg-rose-900/20 rounded-full blur-2xl group-hover:bg-rose-100 dark:group-hover:bg-rose-900/40 transition-colors duration-500"></div>
                <div class="relative">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        Total Kas Keluar
                    </p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-slate-100 mt-2">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-emerald-600 dark:bg-emerald-500 rounded-2xl shadow-sm shadow-emerald-600/20 p-6 flex flex-col justify-center relative overflow-hidden">
                <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                <div class="relative">
                    <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider">
                        Saldo Kas Saat Ini
                    </p>
                    <p class="text-3xl font-bold text-white mt-2">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            {{-- Filter --}}
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <form method="GET" action="{{ route('bank-sampah.cash-report') }}" class="flex flex-col sm:flex-row items-end gap-4 w-full">
                    <div class="space-y-1.5 w-full sm:w-auto">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Periode Dari</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                    </div>
                    <div class="space-y-1.5 w-full sm:w-auto">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400">Sampai</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="submit" class="w-full sm:w-auto h-10 bg-emerald-600 dark:bg-emerald-500 text-white px-5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm">
                            Terapkan Filter
                        </button>
                        @if(request('date_from') || request('date_to'))
                            <a href="{{ route('bank-sampah.cash-report') }}" class="h-10 inline-flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-5 rounded-lg text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Cash Book Table --}}
            @if($ledgers->isEmpty())
                <div class="p-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada transaksi kas pada periode ini.</p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-white dark:bg-slate-900">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32">Tanggal</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Keterangan Transaksi</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kas Masuk</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kas Keluar</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($ledgers as $ledger)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $ledger->date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($ledger->type === 'in')
                                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                </div>
                                            @else
                                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-600 dark:text-rose-400">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                                </div>
                                            @endif
                                            <div class="text-sm text-slate-700 dark:text-slate-300">{{ $ledger->description }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if($ledger->type === 'in')
                                            <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($ledger->amount, 0, ',', '.') }}</div>
                                        @else
                                            <div class="text-sm text-slate-400 dark:text-slate-600">-</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if($ledger->type === 'out')
                                            <div class="text-sm font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($ledger->amount, 0, ',', '.') }}</div>
                                        @else
                                            <div class="text-sm text-slate-400 dark:text-slate-600">-</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="inline-flex items-center px-3 py-1 rounded-md bg-slate-50 dark:bg-slate-800/50">
                                            <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($ledger->balance, 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
