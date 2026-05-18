<x-layouts.dashboard title="Kas Bank Sampah">
    <div class="space-y-6">

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Kas Masuk</p>
                    <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Kas Keluar</p>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-400 mt-1">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-950 rounded-xl border border-emerald-200 dark:border-emerald-800 p-6">
                    <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Saldo Kas Bank Sampah</p>
                    <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Filter --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <form method="GET" action="{{ route('bank-sampah.cash-report') }}" class="flex flex-wrap items-end gap-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">
                        Filter
                    </button>
                    @if(request('date_from') || request('date_to'))
                        <a href="{{ route('bank-sampah.cash-report') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                            Reset
                        </a>
                    @endif
                    <a href="{{ route('bank-sampah.cash-report.export', request()->only(['date_from', 'date_to'])) }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        Export CSV
                    </a>
                </form>
            </div>

            {{-- Cash Book Table --}}
            @if($ledgers->isEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-12 text-center">
                    <p class="text-slate-400 dark:text-slate-500 text-sm">Belum ada transaksi kas bank sampah.</p>
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Keterangan</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Masuk</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Keluar</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Saldo Berjalan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($ledgers as $ledger)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $ledger->date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $ledger->description }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-medium text-emerald-700 dark:text-emerald-400">
                                            {{ $ledger->type === 'in' ? 'Rp ' . number_format($ledger->amount, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right font-medium text-red-600 dark:text-red-400">
                                            {{ $ledger->type === 'out' ? 'Rp ' . number_format($ledger->amount, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right font-semibold text-slate-900 dark:text-slate-100">
                                            Rp {{ number_format($ledger->balance, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

    </div>
</x-layouts.dashboard>
