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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Setoran Nasabah</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
        </div>
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[400ms] bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
            <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Penarikan Nasabah</p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-400 mt-1">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
        </div>
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[500ms] bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Penjualan ke Pengepul</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
        </div>
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[600ms] bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Saldo Kas Bank Sampah</p>
            <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($wasteBankCashBalance, 0, ',', '.') }}</p>
        </div>
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[700ms] bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Jumlah Nasabah</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ $totalMembers }}</p>
        </div>
    </div>

    {{-- ApexCharts --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[550ms] grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 transition-colors duration-300">
            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Setoran vs Penarikan</h4>
            <div id="chart-bs-flow"></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 transition-colors duration-300">
            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Komposisi Dana</h4>
            <div id="chart-bs-composition"></div>
        </div>
    </div>

    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[600ms] grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Savings Transactions --}}
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
                        <span class="ml-3 text-sm font-medium whitespace-nowrap {{ $l->type === 'credit' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $l->type === 'credit' ? '+' : '-' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400 text-center">Belum ada transaksi.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Waste Bank Cash Transactions --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kas Bank Sampah Terbaru</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentCashLedgers as $c)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-slate-900 dark:text-slate-100 truncate">{{ $c->description }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $c->date->format('d/m/Y') }}</p>
                        </div>
                        <span class="ml-3 text-sm font-medium whitespace-nowrap {{ $c->type === 'in' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $c->type === 'in' ? '+' : '-' }}Rp {{ number_format($c->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400 text-center">Belum ada transaksi kas.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[800ms]">
        <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">Menu Cepat</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <a href="/bank-sampah/waste-categories" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Kategori Sampah</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola jenis sampah</p>
            </a>
            <a href="/bank-sampah/collectors" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Pengepul</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola data pengepul</p>
            </a>
            <a href="/bank-sampah/waste-prices" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Harga Sampah</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Atur harga per kategori</p>
            </a>
            <a href="/bank-sampah/deposits" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Setoran Sampah</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Catat setoran nasabah</p>
            </a>
            <a href="/bank-sampah/withdrawals" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Penarikan Saldo</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Proses penarikan</p>
            </a>
            <a href="/bank-sampah/sales" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Penjualan ke Pengepul</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Catat penjualan</p>
            </a>
            <a href="/bank-sampah/savings" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Saldo Nasabah</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lihat semua saldo</p>
            </a>
            <a href="/bank-sampah/cash-report" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Kas Bank Sampah</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Laporan kas operasional</p>
            </a>
        </div>
    </div>

    {{-- Info --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[900ms] bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 transition-colors duration-300">
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">💡 Info</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Saldo Nasabah = total setoran − total penarikan (tabungan pribadi). Kas Bank Sampah = uang operasional dari penjualan ke pengepul.</p>
    </div>

</div>
</x-layouts.dashboard>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const baseOpts = { chart: { toolbar: { show: false }, fontFamily: 'Figtree, sans-serif', background: 'transparent' }, theme: { mode: isDark ? 'dark' : 'light' }, grid: { borderColor: isDark ? '#334155' : '#e2e8f0' } };

    new ApexCharts(document.querySelector('#chart-bs-flow'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'bar', height: 200 },
        series: [{ name: 'Jumlah', data: [{{ $totalCredit }}, {{ $totalDebit }}] }],
        xaxis: { categories: ['Setoran', 'Penarikan'] },
        colors: ['#10b981', '#f87171'],
        plotOptions: { bar: { borderRadius: 6, distributed: true, columnWidth: '45%' } },
        legend: { show: false },
        yaxis: { labels: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } }
    }).render();

    new ApexCharts(document.querySelector('#chart-bs-composition'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'donut', height: 200 },
        series: [{{ $totalSavings }}, {{ $wasteBankCashBalance }}, {{ $totalSales }}],
        labels: ['Saldo Nasabah', 'Kas Operasional', 'Penjualan'],
        colors: ['#10b981', '#64748b', '#f59e0b'],
        legend: { position: 'bottom', fontSize: '11px' },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } }
    }).render();
});
</script>
@endpush
