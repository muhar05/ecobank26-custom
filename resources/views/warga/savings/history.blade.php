<x-layouts.dashboard title="Riwayat Tabungan">
    @if(!$member)
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 text-center transition-colors duration-300">
            <p class="text-slate-500 dark:text-slate-400">Akun Anda belum terhubung dengan data warga/nasabah.</p>
            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Hubungi admin untuk menghubungkan akun.</p>
        </div>
    @else
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Keterangan</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Kredit</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Debit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($ledgers as $l)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                    <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $l->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $l->description }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-emerald-700 dark:text-emerald-400 font-medium">{{ $l->type === 'credit' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '' }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-red-600 dark:text-red-400 font-medium">{{ $l->type === 'debit' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">Belum ada riwayat transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">{{ $ledgers->links() }}</div>
        </div>
    @endif
</x-layouts.dashboard>
