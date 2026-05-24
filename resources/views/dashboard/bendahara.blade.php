<x-layouts.dashboard title="Dashboard Bendahara">
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-6 sm:space-y-8 pb-8">

    {{-- Welcome Hero --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 relative overflow-hidden bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-700/60 p-6 sm:p-10 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-br from-emerald-500/10 to-emerald-500/0 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="relative z-10">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Halo Bendahara! 👋</h2>
            <p class="mt-2 text-sm sm:text-base text-slate-500 dark:text-slate-400 max-w-lg leading-relaxed">Kelola arus kas RT/RW dengan lebih mudah. Pantau pemasukan dan pengeluaran bulan ini.</p>
        </div>
        <div class="relative z-10 flex flex-wrap gap-3">
            <a href="/community-cash/contributions/create" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm shadow-emerald-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Pemasukan
            </a>
            <a href="/community-cash/expenses/create" class="inline-flex items-center gap-2 bg-rose-600 dark:bg-rose-500 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-rose-700 dark:hover:bg-rose-400 transition shadow-sm shadow-rose-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                Pengeluaran
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        {{-- Saldo --}}
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="relative overflow-hidden transition-all duration-500 delay-[200ms] bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 p-6 group hover:shadow-lg hover:shadow-emerald-500/5 transition-all">
            <div class="absolute right-0 top-0 p-6 opacity-20 group-hover:opacity-40 transition-opacity">
                <svg class="w-16 h-16 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-semibold text-emerald-700/80 dark:text-emerald-400/80 uppercase tracking-wider">Saldo Kas Aktif</p>
                <p class="text-3xl font-bold text-emerald-900 dark:text-emerald-50 mt-2">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                <p class="text-xs text-emerald-600/70 dark:text-emerald-400/70 mt-2 font-medium">Dana RT/RW tersedia</p>
            </div>
        </div>

        {{-- Month In --}}
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[300ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Masuk Bulan Ini</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1.5">Rp {{ number_format($monthIn, 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Akumulasi pemasukan bulan berjalan</p>
            </div>
        </div>

        {{-- Month Out --}}
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[400ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Keluar Bulan Ini</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1.5">Rp {{ number_format($monthOut, 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Akumulasi pengeluaran bulan berjalan</p>
            </div>
        </div>

        {{-- Total In --}}
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[500ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pemasukan</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1.5">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Pemasukan sepanjang waktu</p>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[600ms] grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 p-6 sm:p-8">
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight mb-6">Arus Kas Bulan Ini</h4>
            <div id="chart-bendahara-monthly" class="min-h-[250px]"></div>
        </div>

        @if($categoryBalances->isNotEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 p-6 sm:p-8">
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight mb-6">Distribusi per Kategori</h4>
            <div id="chart-bendahara-categories" class="min-h-[250px]"></div>
        </div>
        @endif
    </div>

    {{-- Recent Transactions --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[700ms]">
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-800/60 flex justify-between items-center">
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight">Transaksi Terakhir</h4>
                <a href="/community-cash/report" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">Lihat Buku Kas</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($recentLedgers as $l)
                    <div class="px-6 py-4 sm:px-8 flex justify-between items-center group hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $l->type === 'in' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    {!! $l->type === 'in' ? '<path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>' : '<path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>' !!}
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{ $l->description }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $l->fundCategory->name }} · {{ $l->date->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <span class="ml-4 text-sm font-bold whitespace-nowrap {{ $l->type === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $l->type === 'in' ? '+' : '-' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-500 dark:text-slate-400 text-sm">Belum ada transaksi kas tercatat.</div>
                @endforelse
            </div>
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

    new ApexCharts(document.querySelector('#chart-bendahara-monthly'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'bar', height: 250, parentHeightOffset: 0 },
        series: [{ name: 'Jumlah', data: [{{ $monthIn }}, {{ $monthOut }}] }],
        xaxis: { 
            categories: ['Pemasukan', 'Pengeluaran'],
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

    @if($categoryBalances->isNotEmpty())
    new ApexCharts(document.querySelector('#chart-bendahara-categories'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'donut', height: 250 },
        series: [{!! $categoryBalances->map(fn($cb) => $cb->balance)->implode(',') !!}],
        labels: [{!! $categoryBalances->map(fn($cb) => "'" . addslashes($cb->fundCategory->name) . "'")->implode(',') !!}],
        colors: ['#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#64748b'],
        stroke: { show: true, colors: isDark ? '#0f172a' : '#ffffff', width: 3 },
        dataLabels: { enabled: false },
        legend: { position: 'right', fontSize: '13px', markers: { radius: 12 }, itemMargin: { horizontal: 5, vertical: 5 } },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) }, theme: isDark ? 'dark' : 'light' },
        plotOptions: {
            pie: { donut: { size: '70%' } }
        }
    }).render();
    @endif
});
</script>
@endpush
</x-layouts.dashboard>
