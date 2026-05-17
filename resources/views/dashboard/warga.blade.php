<x-layouts.dashboard title="Dashboard Warga">
    <div class="space-y-6">
        {{-- Welcome --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-300">
            <p class="text-slate-900 dark:text-slate-100">Selamat datang, <strong>{{ auth()->user()->name }}</strong>!</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Anda dapat melihat laporan kas RT/RW secara transparan melalui halaman ini.</p>
        </div>

        {{-- Balance card --}}
        <div class="bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-6 transition-colors duration-300">
            <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Saldo Kas RT/RW Saat Ini</p>
            <p class="text-3xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($balance, 0, ',', '.') }}</p>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">Total pemasukan: Rp {{ number_format($totalIn, 0, ',', '.') }} · Total pengeluaran: Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
        </div>

        {{-- Savings balance --}}
        @if($savingsBalance !== null)
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-300">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Saldo Tabungan Bank Sampah</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">Rp {{ number_format($savingsBalance, 0, ',', '.') }}</p>
                </div>
                <a href="{{ route('warga.savings') }}" class="text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:underline">Lihat Detail →</a>
            </div>
        </div>
        @endif

        {{-- Recent public transactions --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors duration-300">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Transaksi Terakhir</h3>
                <a href="{{ route('warga.cash-report') }}" class="text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:underline">Lihat Semua →</a>
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

        {{-- CTA --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-300">
            <h3 class="font-semibold text-slate-700 dark:text-slate-200 mb-2">Laporan Lengkap</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Lihat buku kas lengkap dengan filter tanggal dan kategori dana.</p>
            <a href="{{ route('warga.cash-report') }}" class="inline-block bg-emerald-700 dark:bg-emerald-500 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Lihat Laporan Kas</a>
        </div>
    </div>
</x-layouts.dashboard>
