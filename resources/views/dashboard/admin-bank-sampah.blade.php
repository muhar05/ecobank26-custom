<x-layouts.dashboard title="Dashboard Bank Sampah">
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-6 sm:space-y-8 pb-8">

    {{-- Welcome Hero --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 relative overflow-hidden bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-700/60 p-6 sm:p-10 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-br from-blue-500/10 to-emerald-500/0 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="relative z-10">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Bank Sampah ♻️</h2>
            <p class="mt-2 text-sm sm:text-base text-slate-500 dark:text-slate-400 max-w-lg leading-relaxed">Kelola setoran sampah, penarikan saldo, dan tabungan nasabah dengan mudah dan transparan.</p>
        </div>
        <div class="relative z-10 flex flex-wrap gap-3">
            <a href="/bank-sampah/deposits/create" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm shadow-emerald-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Setoran Baru
            </a>
            <a href="/bank-sampah/sales/create" class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                Jual ke Agregator
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- Saldo Nasabah --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="relative overflow-hidden transition-all duration-500 delay-[200ms] bg-blue-50 dark:bg-blue-950/20 rounded-2xl border border-blue-100 dark:border-blue-900/50 p-6 group hover:shadow-lg hover:shadow-blue-500/5 transition-all">
                <div class="absolute right-0 top-0 p-6 opacity-20 group-hover:opacity-40 transition-opacity">
                    <svg class="w-16 h-16 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-semibold text-blue-700/80 dark:text-blue-400/80 uppercase tracking-wider">Total Saldo Nasabah</p>
                    <p class="text-3xl font-bold text-blue-900 dark:text-blue-50 mt-2">Rp {{ number_format($totalSavings, 0, ',', '.') }}</p>
                    <p class="text-xs text-blue-600/70 dark:text-blue-400/70 mt-2 font-medium">Akumulasi tabungan warga</p>
                </div>
            </div>

            {{-- Kas Operasional --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="relative overflow-hidden transition-all duration-500 delay-[300ms] bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 p-6 group hover:shadow-lg hover:shadow-emerald-500/5 transition-all">
                <div class="absolute right-0 top-0 p-6 opacity-20 group-hover:opacity-40 transition-opacity">
                    <svg class="w-16 h-16 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-semibold text-emerald-700/80 dark:text-emerald-400/80 uppercase tracking-wider">Kas Bank Sampah</p>
                    <p class="text-3xl font-bold text-emerald-900 dark:text-emerald-50 mt-2">Rp {{ number_format($wasteBankCashBalance, 0, ',', '.') }}</p>
                    <p class="text-xs text-emerald-600/70 dark:text-emerald-400/70 mt-2 font-medium">Dana operasional berjalan</p>
                </div>
            </div>

            {{-- Nasabah --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[400ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Nasabah</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-slate-100 mt-2">{{ $totalMembers }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                        <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
            </div>
            
            {{-- Setoran --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[500ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Setoran</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1.5">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
                <div class="mt-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 100%"></div>
                </div>
            </div>

            {{-- Penarikan --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[600ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Penarikan</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1.5">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
                <div class="mt-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                    <div class="bg-rose-500 h-1.5 rounded-full" style="width: 100%"></div>
                </div>
            </div>

            {{-- Penjualan Agregator --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[700ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Penjualan Agregator</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1.5">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
                <div class="mt-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                    <div class="bg-amber-500 h-1.5 rounded-full" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[800ms] grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 p-6 sm:p-8">
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight mb-6">Setoran vs Penarikan</h4>
            <div id="chart-bs-flow" class="min-h-[280px]"></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 p-6 sm:p-8">
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight mb-6">Komposisi Dana</h4>
            <div id="chart-bs-composition" class="min-h-[280px] flex items-center justify-center"></div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[900ms] grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Savings --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-800/60 flex justify-between items-center">
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight">Tabungan Terbaru</h4>
                <a href="/bank-sampah/savings" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Semua Saldo</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($recentLedgers as $l)
                    <div class="px-6 py-4 sm:px-8 flex justify-between items-center group hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $l->type === 'credit' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    {!! $l->type === 'credit' ? '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>' : '<path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>' !!}
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{ $l->description }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $l->member->name ?? '-' }} · {{ $l->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <span class="ml-4 text-sm font-bold whitespace-nowrap {{ $l->type === 'credit' ? 'text-blue-600 dark:text-blue-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $l->type === 'credit' ? '+' : '-' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-500 dark:text-slate-400 text-sm">Belum ada transaksi tabungan.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Waste Bank Cash --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-800/60 flex justify-between items-center">
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight">Kas Bank Sampah Terbaru</h4>
                <a href="/bank-sampah/cash-report" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">Laporan Kas</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($recentCashLedgers as $c)
                    <div class="px-6 py-4 sm:px-8 flex justify-between items-center group hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $c->type === 'in' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    {!! $c->type === 'in' ? '<path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>' : '<path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>' !!}
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{ $c->description }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $c->date->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <span class="ml-4 text-sm font-bold whitespace-nowrap {{ $c->type === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $c->type === 'in' ? '+' : '-' }}Rp {{ number_format($c->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-500 dark:text-slate-400 text-sm">Belum ada transaksi kas.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[1000ms]">
        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight mb-4">Menu Cepat</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            <a href="/bank-sampah/waste-categories" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-slate-800/60 p-4 hover:-translate-y-1 hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Kategori Sampah</p>
            </a>
            <a href="/bank-sampah/collectors" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-slate-800/60 p-4 hover:-translate-y-1 hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Agregator</p>
            </a>
            <a href="/bank-sampah/waste-prices" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-slate-800/60 p-4 hover:-translate-y-1 hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Harga Sampah</p>
            </a>
            <a href="/bank-sampah/deposits" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-slate-800/60 p-4 hover:-translate-y-1 hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Daftar Setoran</p>
            </a>
            <a href="/bank-sampah/withdrawals" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-slate-800/60 p-4 hover:-translate-y-1 hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Daftar Penarikan</p>
            </a>
            <a href="/bank-sampah/sales" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-slate-800/60 p-4 hover:-translate-y-1 hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Riwayat Penjualan</p>
            </a>
            <a href="/bank-sampah/savings" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-slate-800/60 p-4 hover:-translate-y-1 hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Saldo Nasabah</p>
            </a>
            <a href="/bank-sampah/cash-report" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-slate-800/60 p-4 hover:-translate-y-1 hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Laporan Kas</p>
            </a>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const baseOpts = { 
        chart: { toolbar: { show: false }, fontFamily: 'Figtree, sans-serif', background: 'transparent' }, 
        theme: { mode: isDark ? 'dark' : 'light' }, 
        grid: { 
            borderColor: isDark ? '#334155' : '#f1f5f9',
            strokeDashArray: 4,
            padding: { top: 0, right: 0, bottom: 0, left: 10 }
        } 
    };

    new ApexCharts(document.querySelector('#chart-bs-flow'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'bar', height: 280, parentHeightOffset: 0 },
        series: [{ name: 'Jumlah', data: [{{ $totalCredit }}, {{ $totalDebit }}] }],
        xaxis: { 
            categories: ['Setoran', 'Penarikan'],
            labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontWeight: 600 } },
            axisBorder: { show: false }, axisTicks: { show: false }
        },
        colors: ['#10b981', '#f43f5e'],
        plotOptions: { bar: { borderRadius: 8, distributed: true, columnWidth: '40%' } },
        dataLabels: { enabled: false },
        legend: { show: false },
        yaxis: { labels: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v), style: { colors: isDark ? '#94a3b8' : '#64748b' } } },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) }, theme: isDark ? 'dark' : 'light' }
    }).render();

    new ApexCharts(document.querySelector('#chart-bs-composition'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'donut', height: 280 },
        series: [{{ $totalSavings }}, {{ $wasteBankCashBalance }}, {{ $totalSales }}],
        labels: ['Saldo Nasabah', 'Kas Operasional', 'Penjualan'],
        colors: ['#3b82f6', '#10b981', '#f59e0b'],
        stroke: { show: true, colors: isDark ? '#0f172a' : '#ffffff', width: 4 },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '13px', markers: { radius: 12 }, itemMargin: { horizontal: 10, vertical: 5 } },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) }, theme: isDark ? 'dark' : 'light' },
        plotOptions: {
            pie: { donut: { size: '75%', labels: { show: true, name: { show: true }, value: { show: true, formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) }, total: { show: true, label: 'Total Aset', formatter: function (w) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } }
        }
    }).render();
});
</script>
@endpush
</x-layouts.dashboard>
