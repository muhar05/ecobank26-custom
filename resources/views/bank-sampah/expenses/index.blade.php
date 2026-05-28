<x-layouts.dashboard title="Pengeluaran Operasional Bank Sampah">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Pengeluaran Operasional</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Catat dan pantau pengeluaran operasional Bank Sampah</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('bank-sampah.expenses.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Catat Pengeluaran
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Metrics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Saldo Kas Bank Sampah</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-2">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-5 border border-emerald-100 dark:border-emerald-900/50 shadow-sm flex flex-col justify-between">
                <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">Pemasukan (Bulan Ini)</p>
                <p class="text-2xl font-bold text-emerald-900 dark:text-emerald-100 mt-2">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
            </div>
            <div class="bg-rose-50 dark:bg-rose-900/20 rounded-xl p-5 border border-rose-100 dark:border-rose-900/50 shadow-sm flex flex-col justify-between">
                <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wide">Pengeluaran (Bulan Ini)</p>
                <p class="text-2xl font-bold text-rose-900 dark:text-rose-100 mt-2">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5 border border-blue-100 dark:border-blue-900/50 shadow-sm flex flex-col justify-between">
                <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide">Net Flow (Bulan Ini)</p>
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-2">Rp {{ number_format($totalIn - $totalOut, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <form method="GET" action="{{ route('bank-sampah.expenses.index') }}" class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <input type="date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}" class="rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-slate-500">-</span>
                        <input type="date" name="end_date" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}" class="rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <button type="submit" class="bg-slate-800 dark:bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-700 dark:hover:bg-slate-600 transition">Filter</button>
                    @if(request('start_date'))
                        <a href="{{ route('bank-sampah.expenses.index') }}" class="text-sm text-rose-600 dark:text-rose-400 font-medium hover:underline">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-white dark:bg-slate-900">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal & Kode</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Keterangan</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pencatat</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nominal</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $expense->expense_date->format('d M Y') }}</div>
                                    <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">{{ $expense->expense_code }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-slate-700 dark:text-slate-300 line-clamp-2">{{ $expense->description }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300">
                                            {{ substr($expense->recordedBy->name, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $expense->recordedBy->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('bank-sampah.expenses.show', $expense) }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-300 transition">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada catatan pengeluaran pada rentang tanggal ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($expenses->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
