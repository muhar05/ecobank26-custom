<x-layouts.dashboard title="Dashboard Warga">
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-6 sm:space-y-8 pb-8">

    {{-- Welcome Hero --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 relative overflow-hidden bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-700/60 p-6 sm:p-10 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-br from-emerald-500/10 to-emerald-500/0 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="relative z-10">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Halo, {{ auth()->user()->name }}! 👋</h2>
            <p class="mt-2 text-sm sm:text-base text-slate-500 dark:text-slate-400 max-w-lg leading-relaxed">Pantau secara transparan kas RT/RW dan aktivitas bank sampah Anda di lingkungan ini.</p>
        </div>
        <div class="relative z-10 flex flex-wrap gap-3">
            <a href="{{ route('warga.cash-report') }}" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm shadow-emerald-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan Kas Lengkap
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Kas RT/RW --}}
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="relative overflow-hidden transition-all duration-500 delay-[200ms] bg-emerald-50 dark:bg-emerald-950/20 rounded-[2rem] border border-emerald-100 dark:border-emerald-900/50 p-8 group hover:shadow-lg hover:shadow-emerald-500/5 transition-all">
            <div class="absolute right-0 top-0 p-8 opacity-20 group-hover:opacity-40 transition-opacity">
                <svg class="w-20 h-20 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-1.5 h-4 bg-emerald-500 rounded-full"></div>
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Saldo Kas RT/RW Bersama</p>
                </div>
                <p class="text-4xl font-bold text-emerald-900 dark:text-emerald-50 mt-4 mb-6">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                
                <div class="grid grid-cols-2 gap-4 border-t border-emerald-200/50 dark:border-emerald-800/50 pt-4">
                    <div>
                        <p class="text-[10px] text-emerald-600 dark:text-emerald-400 uppercase font-semibold">Total Pemasukan</p>
                        <p class="text-sm font-bold text-emerald-800 dark:text-emerald-200">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-emerald-600 dark:text-emerald-400 uppercase font-semibold">Total Pengeluaran</p>
                        <p class="text-sm font-bold text-emerald-800 dark:text-emerald-200">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabungan Bank Sampah Pribadi --}}
        @if($savingsBalance !== null)
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="relative overflow-hidden transition-all duration-500 delay-[300ms] bg-blue-50 dark:bg-blue-950/20 rounded-[2rem] border border-blue-100 dark:border-blue-900/50 p-8 group hover:shadow-lg hover:shadow-blue-500/5 transition-all flex flex-col justify-between">
            <div class="absolute right-0 top-0 p-8 opacity-20 group-hover:opacity-40 transition-opacity">
                <svg class="w-20 h-20 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-1.5 h-4 bg-blue-500 rounded-full"></div>
                    <p class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Tabungan Pribadi (Bank Sampah)</p>
                </div>
                <p class="text-4xl font-bold text-blue-900 dark:text-blue-50 mt-4 mb-6">Rp {{ number_format($savingsBalance, 0, ',', '.') }}</p>
                
                <div class="grid grid-cols-2 gap-4 border-t border-blue-200/50 dark:border-blue-800/50 pt-4">
                    <div>
                        <p class="text-[10px] text-blue-600 dark:text-blue-400 uppercase font-semibold">Total Setoran</p>
                        <p class="text-sm font-bold text-blue-800 dark:text-blue-200">Rp {{ number_format($savingsCredit, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-blue-600 dark:text-blue-400 uppercase font-semibold">Total Penarikan</p>
                        <p class="text-sm font-bold text-blue-800 dark:text-blue-200">Rp {{ number_format($savingsDebit, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="relative z-10 mt-4">
                <a href="{{ route('warga.savings') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Lihat Detail Tabungan &rarr;</a>
            </div>
        </div>
        @else
        <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="relative overflow-hidden transition-all duration-500 delay-[300ms] bg-slate-50 dark:bg-slate-800/50 rounded-[2rem] border border-slate-200/60 dark:border-slate-700/60 p-8 flex flex-col items-center justify-center text-center">
            <svg class="w-12 h-12 text-slate-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Belum Terhubung</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs">Akun ini belum dihubungkan dengan data nasabah Bank Sampah.</p>
        </div>
        @endif
    </div>

    {{-- Charts Section --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[400ms] grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 p-6 sm:p-8">
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight mb-6">Arus Kas RT/RW</h4>
            <div id="chart-warga-cash" class="min-h-[250px]"></div>
        </div>

        @if($savingsBalance !== null)
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 p-6 sm:p-8">
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight mb-6">Aktivitas Tabungan Pribadi</h4>
            <div id="chart-warga-savings" class="min-h-[250px]"></div>
        </div>
        @endif
    </div>

    {{-- Recent Transactions --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[500ms]">
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-800/60 flex justify-between items-center">
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight">Transaksi Kas RT/RW Terbaru</h4>
                <a href="{{ route('warga.cash-report') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">Lihat Laporan Lengkap</a>
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

    new ApexCharts(document.querySelector('#chart-warga-cash'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'bar', height: 250, parentHeightOffset: 0 },
        series: [{ name: 'Jumlah', data: [{{ $totalIn }}, {{ $totalOut }}] }],
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

    @if($savingsBalance !== null)
    new ApexCharts(document.querySelector('#chart-warga-savings'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'bar', height: 250, parentHeightOffset: 0 },
        series: [{ name: 'Jumlah', data: [{{ $savingsCredit }}, {{ $savingsDebit }}] }],
        xaxis: { 
            categories: ['Setoran', 'Penarikan'],
            labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontWeight: 600 } },
            axisBorder: { show: false }, axisTicks: { show: false }
        },
        colors: ['#3b82f6', '#f43f5e'],
        plotOptions: { bar: { borderRadius: 8, distributed: true, columnWidth: '40%' } },
        dataLabels: { enabled: false },
        legend: { show: false },
        yaxis: { labels: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v), style: { colors: isDark ? '#94a3b8' : '#64748b' } } },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) }, theme: isDark ? 'dark' : 'light' }
    }).render();
    @endif
});
</script>
@endpush
</x-layouts.dashboard>
