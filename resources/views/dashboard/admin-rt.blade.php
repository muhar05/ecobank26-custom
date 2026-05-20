<x-layouts.dashboard title="Dashboard Admin RT">
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-8">

    {{-- Welcome Hero --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 relative overflow-hidden bg-gradient-to-br from-emerald-600 to-emerald-800 dark:from-emerald-800 dark:to-emerald-950 rounded-3xl p-6 lg:p-8">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <h2 class="text-xl lg:text-2xl font-bold text-white">Selamat datang, {{ auth()->user()->name }}!</h2>
            <p class="mt-1 text-sm text-emerald-100">Pantau kas warga dan aktivitas bank sampah dalam satu tempat.</p>
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white backdrop-blur-sm">Kas RT/RW</span>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white backdrop-blur-sm">Bank Sampah</span>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white backdrop-blur-sm">Transparansi Warga</span>
            </div>
        </div>
    </div>

    {{-- Kas RT/RW Stats --}}
    <div>
        <h3 :class="loaded ? 'opacity-100' : 'opacity-0'" class="transition-opacity duration-500 delay-200 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">Kas RT/RW</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[200ms] bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Saldo Kas</p>
                <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($balance, 0, ',', '.') }}</p>
            </div>
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[300ms] bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Pemasukan</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
            </div>
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[400ms] bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
                <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950 flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Pengeluaran</p>
                <p class="text-2xl font-bold text-red-700 dark:text-red-400 mt-1">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
            </div>
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[500ms] bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Kategori Dana</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ $totalCategories }}</p>
            </div>
        </div>
    </div>

    {{-- Bank Sampah Stats --}}
    <div>
        <h3 :class="loaded ? 'opacity-100' : 'opacity-0'" class="transition-opacity duration-500 delay-500 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">Bank Sampah</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[600ms] bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Saldo Nasabah</p>
                <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($savingsBalance, 0, ',', '.') }}</p>
            </div>
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[700ms] bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Penjualan</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
            </div>
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[800ms] bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Kas Bank Sampah</p>
                <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($wasteBankCashBalance, 0, ',', '.') }}</p>
            </div>
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[900ms] bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg transition-transform">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Nasabah</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ $totalMembers }}</p>
            </div>
        </div>
    </div>

    {{-- ApexCharts --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[950ms] grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 transition-colors duration-300">
            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Pemasukan vs Pengeluaran</h4>
            <div id="chart-cash-flow"></div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 transition-colors duration-300">
            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Bank Sampah</h4>
            <div id="chart-bank-sampah"></div>
        </div>
    </div>

    @if($categoryBalances->isNotEmpty())
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[980ms]">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 transition-colors duration-300">
            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Saldo per Kategori Dana</h4>
            <div id="chart-categories"></div>
        </div>
    </div>
    @endif

    {{-- Recent Activity --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[1000ms] grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Kas RT/RW Recent --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kas RT/RW Terbaru</h4>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentLedgers as $l)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-slate-900 dark:text-slate-100 truncate">{{ $l->description }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $l->fundCategory->name }} · {{ $l->date->format('d/m/Y') }}</p>
                        </div>
                        <span class="ml-3 text-sm font-medium whitespace-nowrap {{ $l->type === 'in' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $l->type === 'in' ? '+' : '-' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400 text-center">Belum ada transaksi.</p>
                @endforelse
            </div>
        </div>

        {{-- Bank Sampah Recent --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Bank Sampah Terbaru</h4>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentSavings as $s)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-slate-900 dark:text-slate-100 truncate">{{ $s->description }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $s->member->name ?? '-' }}</p>
                        </div>
                        <span class="ml-3 text-sm font-medium whitespace-nowrap {{ $s->type === 'credit' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $s->type === 'credit' ? '+' : '-' }}Rp {{ number_format($s->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400 text-center">Belum ada transaksi.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[1100ms]">
        <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">Menu Cepat</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="/community-cash/contributions" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Pemasukan Warga</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Catat iuran dan kontribusi</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
            <a href="/community-cash/expenses" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Pengeluaran Dana</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Catat pengeluaran kas</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
            <a href="/community-cash/report" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Buku Kas Umum</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Laporan kas lengkap</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
            <a href="/bank-sampah/dashboard" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Dashboard Bank Sampah</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lihat ringkasan bank sampah</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
            <a href="/bank-sampah/savings" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Saldo Nasabah</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lihat saldo semua nasabah</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
            <a href="/bank-sampah/cash-report" class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-800 transition-all duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Kas Bank Sampah</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Laporan kas operasional</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const baseOpts = { chart: { toolbar: { show: false }, fontFamily: 'Figtree, sans-serif', background: 'transparent' }, theme: { mode: isDark ? 'dark' : 'light' }, grid: { borderColor: isDark ? '#334155' : '#e2e8f0' } };

    new ApexCharts(document.querySelector('#chart-cash-flow'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'bar', height: 200 },
        series: [{ name: 'Jumlah', data: [{{ $totalIn }}, {{ $totalOut }}] }],
        xaxis: { categories: ['Pemasukan', 'Pengeluaran'] },
        colors: ['#10b981', '#f87171'],
        plotOptions: { bar: { borderRadius: 6, distributed: true, columnWidth: '50%' } },
        legend: { show: false },
        yaxis: { labels: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } }
    }).render();

    new ApexCharts(document.querySelector('#chart-bank-sampah'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'donut', height: 200 },
        series: [{{ $savingsBalance }}, {{ $wasteBankCashBalance }}, {{ $totalSales }}],
        labels: ['Saldo Nasabah', 'Kas Bank Sampah', 'Penjualan'],
        colors: ['#10b981', '#64748b', '#f59e0b'],
        legend: { position: 'bottom', fontSize: '11px' },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } }
    }).render();

    @if($categoryBalances->isNotEmpty())
    new ApexCharts(document.querySelector('#chart-categories'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'bar', height: {{ min($categoryBalances->count() * 50 + 40, 300) }} },
        series: [{ name: 'Saldo', data: [{!! $categoryBalances->map(fn($cb) => $cb->balance)->implode(',') !!}] }],
        xaxis: { categories: [{!! $categoryBalances->map(fn($cb) => "'" . addslashes($cb->fundCategory->name) . "'")->implode(',') !!}] },
        colors: ['#34d399'],
        plotOptions: { bar: { borderRadius: 4, horizontal: true } },
        yaxis: { labels: { style: { fontSize: '11px' } } },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } }
    }).render();
    @endif
});
</script>
@endpush
</x-layouts.dashboard>
