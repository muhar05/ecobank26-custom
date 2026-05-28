<x-layouts.dashboard title="Dashboard Admin RW">
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-6 sm:space-y-8 pb-8">

    {{-- Welcome Hero --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 relative overflow-hidden bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200/60 dark:border-slate-700/60 p-6 sm:p-10 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-br from-emerald-500/10 to-emerald-500/0 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="relative z-10">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Halo, {{ auth()->user()->name }}! 👋</h2>
            <p class="mt-2 text-sm sm:text-base text-slate-500 dark:text-slate-400 max-w-lg leading-relaxed">Pantau data agregasi seluruh Rukun Tetangga (RT), keuangan kas warga, dan aktivitas Bank Sampah Rukun Warga (RW) secara real-time.</p>
        </div>
        <div class="relative z-10 flex flex-wrap gap-3">
            <a href="/members" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm">
                Kelola Warga
            </a>
            <a href="/iuran/tunggakan" class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                Pantau Tunggakan
            </a>
        </div>
    </div>

    {{-- Agregasi Wilayah & Kas RW --}}
    <div>
        <div :class="loaded ? 'opacity-100' : 'opacity-0'" class="flex items-center gap-2 mb-4 transition-opacity duration-500 delay-200">
            <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 tracking-tight">Agregasi Wilayah & Kas RW</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- Total RT --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[100ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total RT</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-slate-100 mt-1.5">{{ $totalRts }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-950/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 mt-3 font-medium">Rukun Tetangga terdaftar</p>
            </div>

            {{-- Total KK --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[200ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total KK</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-slate-100 mt-1.5">{{ $totalKks }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-950/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 mt-3 font-medium">Kartu Keluarga terdaftar</p>
            </div>

            {{-- Total Kas RW --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[300ms] bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 p-6 hover:shadow-lg hover:shadow-emerald-500/5 transition-all relative overflow-hidden">
                <div class="absolute right-0 top-0 p-6 opacity-20">
                    <svg class="w-16 h-16 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-semibold text-emerald-700/80 dark:text-emerald-400/80 uppercase tracking-wider">Saldo Kas Warga</p>
                    <p class="text-2xl font-bold text-emerald-900 dark:text-emerald-50 mt-1.5">Rp {{ number_format($totalKasWarga, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-emerald-600/70 dark:text-emerald-400/70 mt-3 font-medium">Dana terkonsolidasi</p>
                </div>
            </div>

            {{-- Total Tunggakan --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[400ms] bg-rose-50 dark:bg-rose-950/20 rounded-2xl border border-rose-100 dark:border-rose-900/50 p-6 hover:shadow-lg hover:shadow-rose-500/5 transition-all relative overflow-hidden">
                <div class="absolute right-0 top-0 p-6 opacity-20">
                    <svg class="w-16 h-16 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-semibold text-rose-700/80 dark:text-rose-400/80 uppercase tracking-wider">Total Tunggakan Iuran</p>
                    <p class="text-2xl font-bold text-rose-900 dark:text-rose-50 mt-1.5">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-rose-600/70 dark:text-rose-400/70 mt-3 font-medium">Tunggakan KK lintas RT</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Agregasi Aktivitas Bank Sampah --}}
    <div>
        <div :class="loaded ? 'opacity-100' : 'opacity-0'" class="flex items-center gap-2 mt-8 mb-4 transition-opacity duration-500 delay-500">
            <div class="w-2 h-6 bg-blue-500 rounded-full"></div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 tracking-tight">Agregasi Aktivitas Bank Sampah</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- Saldo Nasabah --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[500ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Saldo Nasabah</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1.5">Rp {{ number_format($savingsBalance, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: 100%"></div>
                </div>
                <p class="text-[10px] text-slate-400 mt-2 font-medium">Total kewajiban tabungan warga</p>
            </div>

            {{-- Kas Operasional --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[600ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kas Operasional</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1.5">Rp {{ number_format($wasteBankCashBalance, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 100%"></div>
                </div>
                <p class="text-[10px] text-slate-400 mt-2 font-medium">Kas tunai di Bank Sampah</p>
            </div>

            {{-- Penjualan --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[700ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Penjualan</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1.5">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                    <div class="bg-amber-500 h-1.5 rounded-full" style="width: 100%"></div>
                </div>
                <p class="text-[10px] text-slate-400 mt-2 font-medium">Akumulasi hasil penjualan sampah</p>
            </div>

            {{-- Total Warga --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[800ms] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 p-6 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Warga</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1.5">{{ $totalMembers }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <a href="/members" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Lihat Data Warga &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[850ms] grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 p-6 sm:p-8">
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight mb-6">Pemasukan vs Pengeluaran Kas (RW)</h4>
            <div id="chart-cash-flow" class="min-h-[280px]"></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 p-6 sm:p-8">
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 tracking-tight mb-6">Komposisi Tabungan & Kas Bank Sampah</h4>
            <div id="chart-bank-sampah" class="min-h-[280px] flex items-center justify-center"></div>
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

    // Cash Flow Chart (Bar)
    new ApexCharts(document.querySelector('#chart-cash-flow'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'bar', height: 280, parentHeightOffset: 0 },
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

    // Bank Sampah Composition Chart
    new ApexCharts(document.querySelector('#chart-bank-sampah'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'donut', height: 280 },
        series: [{{ $savingsBalance }}, {{ $wasteBankCashBalance }}, {{ $totalSales }}],
        labels: ['Saldo Nasabah', 'Kas Bank Sampah', 'Penjualan'],
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
