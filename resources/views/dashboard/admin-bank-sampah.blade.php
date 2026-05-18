<x-layouts.dashboard title="Dashboard Bank Sampah">
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-8">

    {{-- Welcome Hero --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 relative overflow-hidden bg-gradient-to-br from-emerald-600 to-emerald-800 dark:from-emerald-800 dark:to-emerald-950 rounded-3xl p-6 lg:p-8">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <h2 class="text-xl lg:text-2xl font-bold text-white">Dashboard Bank Sampah</h2>
            <p class="mt-1 text-sm text-emerald-100">Kelola setoran sampah, penarikan saldo, dan tabungan nasabah dalam satu tempat.</p>
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white backdrop-blur-sm">Setoran Sampah</span>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white backdrop-blur-sm">Saldo Nasabah</span>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white backdrop-blur-sm">Penarikan</span>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[200ms] bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Total Saldo Nasabah</p>
            <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($totalSavings, 0, ',', '.') }}</p>
        </div>
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[300ms] bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Setoran</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
        </div>
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[400ms] bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
            <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Penarikan</p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-400 mt-1">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
        </div>
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[500ms] bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Jumlah Nasabah</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ $totalMembers }}</p>
        </div>
    </div>

    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[600ms] grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Transactions --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Transaksi Tabungan Terbaru</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentLedgers as $l)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-slate-900 dark:text-slate-100 truncate">{{ $l->description }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $l->member->name ?? '-' }} · {{ $l->created_at->format('d/m/Y') }}</p>
                        </div>
                        <div class="ml-3 flex items-center gap-2">
                            <span class="text-sm font-medium whitespace-nowrap {{ $l->type === 'credit' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $l->type === 'credit' ? '+' : '-' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400 text-center">Belum ada transaksi.</p>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="space-y-4">
            <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Menu Cepat</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="/bank-sampah/waste-categories" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Kategori Sampah</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola jenis sampah</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                <a href="/bank-sampah/deposits" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Setoran Sampah</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Catat setoran nasabah</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                <a href="/bank-sampah/withdrawals" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Penarikan Saldo</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Proses penarikan</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                <a href="/bank-sampah/savings" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Saldo Nasabah</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lihat semua saldo</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- Info --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[800ms] bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 transition-colors duration-300">
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">💡 Saldo Nasabah</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Saldo dihitung dari total setoran dikurangi total penarikan berdasarkan buku tabungan nasabah.</p>
    </div>

</div>
</x-layouts.dashboard>
