<x-layouts.dashboard title="Dashboard Admin RT">
    <div class="space-y-8">

        {{-- Section: Kas RT/RW --}}
        <div class="space-y-4">
            <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kas RT/RW</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-5 transition-colors duration-300">
                    <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Saldo Kas</p>
                    <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 transition-colors duration-300">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Pemasukan</p>
                    <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 transition-colors duration-300">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Pengeluaran</p>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-400 mt-1">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 transition-colors duration-300">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Kategori Dana</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ $totalCategories }}</p>
                </div>
            </div>

            {{-- Recent community cash --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                    <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Transaksi Kas Terakhir</h4>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentLedgers as $l)
                        <div class="px-5 py-3 flex justify-between items-center">
                            <div>
                                <p class="text-sm text-slate-900 dark:text-slate-100">{{ $l->description }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $l->fundCategory->name }} · {{ $l->date->format('d/m/Y') }}</p>
                            </div>
                            <span class="text-sm font-medium {{ $l->type === 'in' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $l->type === 'in' ? '+' : '-' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <p class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400 text-center">Belum ada transaksi.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Section: Bank Sampah --}}
        <div class="space-y-4">
            <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Bank Sampah</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-5 transition-colors duration-300">
                    <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Saldo Nasabah</p>
                    <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($savingsBalance, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 transition-colors duration-300">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Setoran</p>
                    <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 transition-colors duration-300">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Penarikan</p>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-400 mt-1">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 transition-colors duration-300">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Nasabah</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ $totalMembers }}</p>
                </div>
            </div>

            {{-- Recent savings --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors duration-300">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                    <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Transaksi Bank Sampah Terakhir</h4>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentSavings as $s)
                        <div class="px-5 py-3 flex justify-between items-center">
                            <div>
                                <p class="text-sm text-slate-900 dark:text-slate-100">{{ $s->description }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $s->member->name ?? '-' }}</p>
                            </div>
                            <span class="text-sm font-medium {{ $s->type === 'credit' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $s->type === 'credit' ? '+' : '-' }}Rp {{ number_format($s->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <p class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400 text-center">Belum ada transaksi.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-300">
            <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">Menu Cepat</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <a href="/community-cash/contributions" class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition">Pemasukan Warga</a>
                <a href="/community-cash/expenses" class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition">Pengeluaran Dana</a>
                <a href="/community-cash/report" class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition">Buku Kas Umum</a>
                <a href="/bank-sampah/deposits" class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition">Setoran Sampah</a>
                <a href="/bank-sampah/withdrawals" class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition">Penarikan Saldo</a>
                <a href="/bank-sampah/savings" class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition">Saldo Nasabah</a>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
