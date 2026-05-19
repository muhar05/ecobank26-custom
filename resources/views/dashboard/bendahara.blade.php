<x-layouts.dashboard title="Dashboard Bendahara">
    <div class="space-y-6">
        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-5 transition-colors duration-300">
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Saldo Kas</p>
                <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">Rp {{ number_format($balance, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 transition-colors duration-300">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Pemasukan Bulan Ini</p>
                <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">Rp {{ number_format($monthIn, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 transition-colors duration-300">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Pengeluaran Bulan Ini</p>
                <p class="text-2xl font-bold text-red-700 dark:text-red-400 mt-1">Rp {{ number_format($monthOut, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 transition-colors duration-300">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Pemasukan</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-300">
            <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">Aksi Cepat</h3>
            <div class="flex flex-wrap gap-3">
                <a href="/community-cash/contributions/create" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">+ Catat Pemasukan</a>
                <a href="/community-cash/expenses/create" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">+ Catat Pengeluaran</a>
                <a href="/community-cash/report" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Buku Kas</a>
                <a href="/community-cash/categories" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Kategori Dana</a>
            </div>
        </div>

        {{-- ApexChart: Monthly Cashflow --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-300">
            <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Arus Kas Bulan Ini</h3>
            <div id="chart-bendahara-monthly"></div>
        </div>

        {{-- ApexChart: Category Balances --}}
        @if($categoryBalances->isNotEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-300">
            <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Saldo per Kategori</h3>
            <div id="chart-bendahara-categories"></div>
        </div>
        @endif

        {{-- Recent transactions --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors duration-300">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Transaksi Terakhir</h3>
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
</x-layouts.dashboard>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const baseOpts = { chart: { toolbar: { show: false }, fontFamily: 'Figtree, sans-serif', background: 'transparent' }, theme: { mode: isDark ? 'dark' : 'light' }, grid: { borderColor: isDark ? '#334155' : '#e2e8f0' } };

    new ApexCharts(document.querySelector('#chart-bendahara-monthly'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'bar', height: 180 },
        series: [{ name: 'Jumlah', data: [{{ $monthIn }}, {{ $monthOut }}] }],
        xaxis: { categories: ['Pemasukan', 'Pengeluaran'] },
        colors: ['#10b981', '#f87171'],
        plotOptions: { bar: { borderRadius: 6, distributed: true, columnWidth: '45%' } },
        legend: { show: false },
        yaxis: { labels: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } }
    }).render();

    @if($categoryBalances->isNotEmpty())
    new ApexCharts(document.querySelector('#chart-bendahara-categories'), {
        ...baseOpts,
        chart: { ...baseOpts.chart, type: 'bar', height: {{ min($categoryBalances->count() * 45 + 40, 250) }} },
        series: [{ name: 'Saldo', data: [{!! $categoryBalances->map(fn($cb) => $cb->balance)->implode(',') !!}] }],
        xaxis: { categories: [{!! $categoryBalances->map(fn($cb) => "'" . addslashes($cb->fundCategory->name) . "'")->implode(',') !!}] },
        colors: ['#34d399'],
        plotOptions: { bar: { borderRadius: 4, horizontal: true } },
        tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } }
    }).render();
    @endif
});
</script>
@endpush
