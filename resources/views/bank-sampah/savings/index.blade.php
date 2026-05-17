<x-layouts.dashboard title="Saldo Nasabah">
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Saldo Nasabah</h2>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Nama</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Total Kredit</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Total Debit</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($balances as $b)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                <td class="px-6 py-4 text-sm font-mono text-slate-900 dark:text-slate-100">{{ $b->member->member_code ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $b->member->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-right text-emerald-700 dark:text-emerald-400">Rp {{ number_format($b->total_credit, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-right text-red-600 dark:text-red-400">Rp {{ number_format($b->total_debit, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-right font-bold {{ $b->balance >= 0 ? 'text-slate-900 dark:text-slate-100' : 'text-red-600 dark:text-red-400' }}">Rp {{ number_format($b->balance, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">Belum ada data saldo nasabah.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
