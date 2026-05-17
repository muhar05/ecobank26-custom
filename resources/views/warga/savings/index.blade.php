<x-layouts.dashboard title="Saldo Saya">
    @if(!$member)
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 text-center transition-colors duration-300">
            <p class="text-slate-500 dark:text-slate-400">Akun Anda belum terhubung dengan data warga/nasabah.</p>
            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Hubungi admin untuk menghubungkan akun.</p>
        </div>
    @else
        <div class="space-y-6">
            {{-- Balance --}}
            <div class="bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-6 transition-colors duration-300">
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Saldo Tabungan Bank Sampah</p>
                <p class="text-3xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">Total setoran: Rp {{ number_format($totalCredit, 0, ',', '.') }} · Total penarikan: Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
            </div>

            {{-- Recent transactions --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Transaksi Terakhir</h3>
                    <a href="/warga/savings/history" class="text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:underline">Lihat Semua →</a>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentLedgers as $l)
                        <div class="px-5 py-3 flex justify-between items-center">
                            <div>
                                <p class="text-sm text-slate-900 dark:text-slate-100">{{ $l->description }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $l->created_at->format('d/m/Y') }}</p>
                            </div>
                            <span class="text-sm font-medium {{ $l->type === 'credit' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $l->type === 'credit' ? '+' : '-' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <p class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400 text-center">Belum ada transaksi.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</x-layouts.dashboard>
