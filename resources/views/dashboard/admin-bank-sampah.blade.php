<x-layouts.dashboard title="Dashboard Bank Sampah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">

        {{-- 1. Hero / action-first header --}}
        <div class="bg-surface border border-border rounded-radius-lg shadow-subtle p-5 sm:p-7 flex flex-col md:flex-row md:items-center gap-5 md:gap-6 transition-subtle">
            {{-- LEFT: contextual label, title, description, primary actions --}}
            <div class="flex-1 min-w-0 space-y-4">
                <div class="space-y-1.5 max-w-xl">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-radius-sm bg-primary-light text-primary text-caption font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        Pusat Kendali Bank Sampah
                    </div>
                    <h1 class="text-heading-lg sm:text-heading-xl font-bold text-text tracking-tight">
                        Bank Sampah EcoBank026
                    </h1>
                    <p class="text-body text-text-muted leading-relaxed">
                        Kelola penimbangan sampah nasabah, kas operasional, dan transaksi penjualan agregator secara terpadu dan transparan.
                    </p>
                </div>

                {{-- Action-First Priority (One Click principle) --}}
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('bank-sampah.deposits.create') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-radius-md bg-primary hover:bg-primary-hover text-white text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer select-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    Setoran Baru
                </a>
                    <a href="{{ route('bank-sampah.sales.create') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-radius-md bg-surface hover:bg-surface-muted text-text border border-border text-body-sm font-semibold shadow-subtle transition-subtle cursor-pointer select-none">
                        <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Jual ke Agregator
                    </a>
                </div>
            </div>

            {{-- RIGHT: official supporting illustration (30-40% hero on desktop, stacked last on mobile) --}}
            <div class="shrink-0 flex justify-center md:justify-end md:basis-[36%] md:max-w-[36%]">
                <img src="{{ asset('images/dashboard-bank-sampah-illustration.png') }}"
                     alt="" aria-hidden="true" draggable="false"
                     class="hero-illustration w-56 sm:w-64 md:w-full md:max-w-[320px] h-auto object-contain select-none">
            </div>
        </div>

        {{-- 2. Core operational KPIs (4 primary cards) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            {{-- KPI 1: Kas Bank Sampah --}}
            <div class="bg-surface border border-border rounded-radius-lg p-5 shadow-subtle hover:border-primary/40 transition-subtle flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider">Kas Bank Sampah</span>
                    <div class="w-9 h-9 rounded-radius-md bg-primary-light text-primary flex items-center justify-center shrink-0 shadow-subtle">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="font-mono text-heading-lg font-bold text-text tracking-tight truncate">
                        Rp {{ number_format($wasteBankCashBalance, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- KPI 2: Total Saldo Nasabah --}}
            <div class="bg-surface border border-border rounded-radius-lg p-5 shadow-subtle hover:border-primary/40 transition-subtle flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider">Total Saldo Nasabah</span>
                    <div class="w-9 h-9 rounded-radius-md bg-surface-muted text-text flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="font-mono text-heading-lg font-bold text-text tracking-tight truncate">
                        Rp {{ number_format($totalSavings, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- KPI 3: Total Nasabah --}}
            <div class="bg-surface border border-border rounded-radius-lg p-5 shadow-subtle hover:border-primary/40 transition-subtle flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider">Nasabah Terdaftar</span>
                    <div class="w-9 h-9 rounded-radius-md bg-surface-muted text-text flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="font-mono text-heading-lg font-bold text-text tracking-tight">
                        {{ number_format($totalCustomers, 0, ',', '.') }} <span class="text-body-sm font-normal text-text-muted">Orang</span>
                    </p>
             
                </div>
            </div>

            {{-- KPI 4: Total Setoran Masuk --}}
            <div class="bg-surface border border-border rounded-radius-lg p-5 shadow-subtle hover:border-primary/40 transition-subtle flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider">Total Setoran</span>
                    <div class="w-9 h-9 rounded-radius-md bg-success-light text-success-text flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="font-mono text-heading-lg font-bold text-text tracking-tight truncate">
                        Rp {{ number_format($totalCredit, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- 3. Supporting volume metrics (secondary row) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-surface border border-border rounded-radius-md p-4 shadow-subtle flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-radius-sm bg-danger-light text-danger-text flex items-center justify-center shrink-0 font-bold text-sm">
                        &minus;
                    </div>
                    <div class="min-w-0">
                        <span class="block text-body-sm font-semibold text-text-muted uppercase">Total Penarikan Saldo</span>
                        <span class="font-mono font-bold text-body text-text truncate block">
                            Rp {{ number_format($totalDebit, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('bank-sampah.withdrawals.index') }}" class="text-body-sm font-semibold text-text-muted hover:text-primary shrink-0 transition-subtle">
                    Daftar Penarikan &rarr;
                </a>
            </div>

            <div class="bg-surface border border-border rounded-radius-md p-4 shadow-subtle flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-radius-sm bg-secondary-light text-secondary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="block text-body-sm font-semibold text-text-muted uppercase">Total Penjualan Agregator</span>
                        <span class="font-mono font-bold text-body text-text truncate block">
                            Rp {{ number_format($totalSales, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('bank-sampah.sales.index') }}" class="text-body-sm font-semibold text-text-muted hover:text-primary shrink-0 transition-subtle">
                    Riwayat Penjualan &rarr;
                </a>
            </div>
        </div>

        {{-- 4. Charts and fund flow (honest empty states) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            {{-- Chart 1: Arus Timbangan (Setoran vs Penarikan) --}}
            <div class="lg:col-span-7 bg-surface border border-border rounded-radius-lg p-5 sm:p-6 shadow-subtle flex flex-col justify-between space-y-4">
                <div class="flex items-start justify-between gap-4 border-b border-border-subtle pb-3">
                    <div>
                        <h2 class="text-heading-sm font-bold text-text">Arus Timbangan (Setoran vs Penarikan)</h2>
                    </div>
                </div>

                @if($totalCredit > 0 || $totalDebit > 0)
                    <div id="chart-bs-flow" class="min-h-[260px]"></div>
                @else
                    <div class="py-12 px-4 text-center border-2 border-dashed border-border-subtle rounded-radius-md flex flex-col items-center justify-center space-y-3">
                        <div class="w-11 h-11 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-body-sm font-bold text-text">Belum Ada Arus Timbangan</p>
                        </div>
                        <a href="{{ route('bank-sampah.deposits.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-radius-md bg-primary text-white text-body-sm font-semibold shadow-subtle hover:bg-primary-hover transition-subtle">
                            + Catat Setoran Sekarang
                        </a>
                    </div>
                @endif

                <div class="pt-3 border-t border-border-subtle flex items-center justify-between text-body-sm text-text-muted font-mono">
                    <span>Setoran: <strong class="text-text font-bold">Rp {{ number_format($totalCredit, 0, ',', '.') }}</strong></span>
                    <span>Penarikan: <strong class="text-text font-bold">Rp {{ number_format($totalDebit, 0, ',', '.') }}</strong></span>
                </div>
            </div>

            {{-- Chart 2: Setoran per Material --}}
            <div class="lg:col-span-5 bg-surface border border-border rounded-radius-lg p-5 sm:p-6 shadow-subtle flex flex-col justify-between space-y-4">
                <div class="flex items-start justify-between gap-4 border-b border-border-subtle pb-3">
                    <div>
                        <h2 class="text-heading-sm font-bold text-text">Setoran per Material</h2>
                    </div>
                </div>

                @if($depositWeightByMaterial->isNotEmpty())
                    <div id="chart-bs-material" class="min-h-[260px]"></div>
                @else
                    <div class="py-12 px-4 text-center border-2 border-dashed border-border-subtle rounded-radius-md flex flex-col items-center justify-center space-y-3">
                        <div class="w-11 h-11 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-body-sm font-bold text-text">Belum Ada Data Setoran</p>
                        </div>
                        <a href="{{ route('bank-sampah.deposits.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-radius-md bg-primary text-white text-body-sm font-semibold shadow-subtle hover:bg-primary-hover transition-subtle">
                            + Catat Setoran Sekarang
                        </a>
                    </div>
                @endif

                <div class="pt-3 border-t border-border-subtle text-body-sm text-text-muted flex items-center justify-between">
                    <span>Total berat setoran:</span>
                    <span class="font-mono font-bold text-text">{{ number_format($totalDepositWeight, 2, ',', '.') }} kg</span>
                </div>
            </div>
        </div>

        {{-- 5. Recent activity lists (tabungan and kas) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            {{-- Activity Left: Mutasi Tabungan Nasabah Terbaru --}}
            <div class="bg-surface border border-border rounded-radius-lg p-5 sm:p-6 shadow-subtle space-y-4">
                <div class="flex items-center justify-between border-b border-border-subtle pb-3">
                    <div>
                        <h3 class="text-heading-sm font-bold text-text">Mutasi Tabungan Terbaru</h3>
                    </div>
                    <a href="{{ route('bank-sampah.savings') }}" class="text-body-sm font-semibold text-primary hover:underline">
                        Buku Tabungan &rarr;
                    </a>
                </div>

                <div class="divide-y divide-border-subtle">
                    @forelse($recentLedgers as $l)
                        <div class="py-3 flex items-center justify-between gap-3 text-body-sm hover:bg-surface-muted/60 rounded-radius-md px-2 -mx-2 transition-subtle">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-caption shrink-0 {{ $l->type === 'credit' ? 'bg-success-light text-success-text' : 'bg-danger-light text-danger-text' }}">
                                    {{ $l->type === 'credit' ? '+' : '−' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-text truncate">{{ $l->wasteCustomer?->name ?? 'Nasabah' }}</p>
                                    <p class="text-caption text-text-muted truncate mt-0.5">{{ $l->description }} &bull; {{ $l->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <span class="font-mono font-bold whitespace-nowrap text-body-sm {{ $l->type === 'credit' ? 'text-success-text font-bold' : 'text-danger-text font-bold' }}">
                                {{ $l->type === 'credit' ? '+' : '−' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-body-sm text-text-muted border border-dashed border-border-subtle rounded-radius-md">
                            Belum ada riwayat mutasi tabungan nasabah.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Activity Right: Kas Operasional Bank Sampah --}}
            <div class="bg-surface border border-border rounded-radius-lg p-5 sm:p-6 shadow-subtle space-y-4">
                <div class="flex items-center justify-between border-b border-border-subtle pb-3">
                    <div>
                        <h3 class="text-heading-sm font-bold text-text">Arus Kas Operasional Terbaru</h3>
                    </div>
                    <a href="{{ route('bank-sampah.cash-report') }}" class="text-body-sm font-semibold text-primary hover:underline">
                        Laporan Kas &rarr;
                    </a>
                </div>

                <div class="divide-y divide-border-subtle">
                    @forelse($recentCashLedgers as $c)
                        <div class="py-3 flex items-center justify-between gap-3 text-body-sm hover:bg-surface-muted/60 rounded-radius-md px-2 -mx-2 transition-subtle">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-caption shrink-0 {{ $c->type === 'in' ? 'bg-success-light text-success-text' : 'bg-danger-light text-danger-text' }}">
                                    {{ $c->type === 'in' ? '↓' : '↑' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-text truncate">{{ $c->description }}</p>
                                    <p class="text-caption text-text-muted mt-0.5">{{ $c->date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <span class="font-mono font-bold whitespace-nowrap text-body-sm {{ $c->type === 'in' ? 'text-success-text' : 'text-danger-text' }}">
                                {{ $c->type === 'in' ? '+' : '−' }}Rp {{ number_format($c->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-body-sm text-text-muted border border-dashed border-border-subtle rounded-radius-md">
                            Belum ada catatan arus kas operasional.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Chart scripts (lifecycle and SPA compatible) --}}
    @push('scripts')
    <script>
    function initDashboardCharts() {
        if (typeof ApexCharts === 'undefined') return;
        const flowEl = document.querySelector('#chart-bs-flow');
        const matEl = document.querySelector('#chart-bs-material');
        const charts = [];

        const isDark = document.documentElement.classList.contains('dark');
        const baseOpts = { 
            chart: { 
                toolbar: { show: false }, 
                fontFamily: 'Plus Jakarta Sans, sans-serif',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 350 }
            }, 
            theme: { mode: isDark ? 'dark' : 'light' }, 
            grid: { 
                borderColor: isDark ? '#292524' : '#e7e5e4', 
                strokeDashArray: 3, 
                padding: { top: 0, right: 10, bottom: 0, left: 10 } 
            } 
        };

        if (flowEl) {
            flowEl.innerHTML = '';
            const chartFlow = new ApexCharts(flowEl, {
                ...baseOpts,
                chart: { ...baseOpts.chart, type: 'bar', height: 260, parentHeightOffset: 0 },
                series: [{ name: 'Jumlah', data: [{{ (float)$totalCredit }}, {{ (float)$totalDebit }}] }],
                xaxis: { 
                    categories: ['Setoran Masuk', 'Penarikan Saldo'],
                    labels: { style: { colors: isDark ? '#a8a29e' : '#78716c', fontWeight: 600 } },
                    axisBorder: { show: false }, axisTicks: { show: false }
                },
                colors: ['#059669', '#e11d48'],
                plotOptions: { bar: { borderRadius: 6, distributed: true, columnWidth: '38%' } },
                dataLabels: { enabled: false },
                legend: { show: false },
                yaxis: { labels: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v), style: { colors: isDark ? '#a8a29e' : '#78716c' } } },
                tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) }, theme: isDark ? 'dark' : 'light' }
            });
            chartFlow.render();
            charts.push(chartFlow);
        }

        if (matEl) {
            matEl.innerHTML = '';
            const matCategories = @json($depositWeightByMaterial->pluck('name'));
            const matWeights = @json($depositWeightByMaterial->pluck('total_weight')->map(fn ($w) => (float) $w));
            const matKg = v => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(v) + ' kg';
            const chartMat = new ApexCharts(matEl, {
                ...baseOpts,
                chart: { ...baseOpts.chart, type: 'bar', height: 260, parentHeightOffset: 0, animations: { enabled: false } },
                series: [{ name: 'Berat setoran', data: matWeights }],
                xaxis: {
                    labels: { formatter: matKg, style: { colors: isDark ? '#a8a29e' : '#78716c' } },
                    axisBorder: { show: false }, axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: isDark ? '#a8a29e' : '#78716c', fontSize: '12px', fontWeight: 600 } }
                },
                colors: ['#059669'],
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
                dataLabels: { enabled: false },
                legend: { show: false },
                tooltip: { y: { formatter: matKg }, theme: isDark ? 'dark' : 'light' }
            });
            chartMat.render();
            charts.push(chartMat);
        }

        window._ecoDashboardCharts = charts;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
    } else {
        initDashboardCharts();
    }
    </script>
    @endpush
</x-layouts.dashboard>
