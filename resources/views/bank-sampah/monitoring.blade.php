<x-layouts.dashboard title="Monitoring Konsistensi Bank Sampah">
<div x-data="{ activeTab: 'mismatch', loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-6 sm:space-y-8 pb-8">

    {{-- Welcome & Audit Status Header --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 relative overflow-hidden bg-surface rounded-radius-lg border border-border p-6 sm:p-10 shadow-subtle flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-text tracking-tight">Monitoring & Audit Konsistensi</h2>
            </div>
            <p class="mt-2 text-sm sm:text-base text-text-muted max-w-lg leading-relaxed">
                Halaman audit read-only real-time untuk memantau integritas transaksi, saldo ledger, dan memvalidasi anomali sistem secara otomatis.
            </p>
            <div class="mt-4 p-3 bg-surface-muted/50 rounded-radius-lg border border-border/60 dark:border-border/60 text-xs text-text-muted">
                <p class="font-semibold text-text mb-1">Sumber Data Monitoring:</p>
                <p class="mb-1">Halaman ini mengambil data dari nasabah, setoran, penarikan, buku tabungan, dan audit consistency checker berdasarkan tabel berikut:</p>
                <ul class="list-disc list-inside mt-1 grid grid-cols-2 gap-x-2 gap-y-0.5">
                    <li><code class="text-caption bg-surface-muted px-1 py-0.5 rounded">waste_customers</code></li>
                    <li><code class="text-caption bg-surface-muted px-1 py-0.5 rounded">deposits</code></li>
                    <li><code class="text-caption bg-surface-muted px-1 py-0.5 rounded">withdrawals</code></li>
                    <li><code class="text-caption bg-surface-muted px-1 py-0.5 rounded">savings_ledgers</code></li>
                    <li><code class="text-caption bg-surface-muted px-1 py-0.5 rounded">audit service</code></li>
                    <li><code class="text-caption bg-surface-muted px-1 py-0.5 rounded">activity_logs</code></li>
                </ul>
            </div>
        </div>

        {{-- Audit Status Badge --}}
        <div class="relative z-10 flex flex-col items-start md:items-end gap-2">
            <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">Status Audit Terakhir</span>
            @if ($exitCode === 0)
                <div class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-radius-lg border border-primary/30 font-bold text-sm">
                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    HEALTHY
                </div>
            @elseif ($exitCode === 1)
                <div class="inline-flex items-center gap-2 bg-warning/10 text-warning-text px-4 py-2 rounded-radius-lg border border-warning/30 font-bold text-sm">
                    <span class="w-2 h-2 rounded-full bg-warning animate-pulse"></span>
                    WARNING
                </div>
            @else
                <div class="inline-flex items-center gap-2 bg-danger/10 text-danger-text px-4 py-2 rounded-radius-lg border border-danger/30 font-bold text-sm">
                    <span class="w-2 h-2 rounded-full bg-danger animate-pulse"></span>
                    CRITICAL
                </div>
            @endif
            <p class="text-caption text-text-muted mt-1">Selesai dalam {{ $metrics['duration_ms'] }}ms</p>
        </div>
    </div>

    {{-- Metrics Grid --}}
    <div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- Health Score Card --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="relative overflow-hidden transition-all duration-500 delay-[100ms] bg-gradient-to-br from-slate-900 to-slate-950 rounded-radius-lg p-6 hover:shadow-lg transition-all border border-border">
                <div class="absolute right-0 top-0 p-6 opacity-10">
                    <svg class="w-16 h-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Health Score</p>
                    <p class="text-4xl font-extrabold text-white mt-2">{{ $healthScore }}%</p>
                    <div class="mt-4 flex gap-1.5">
                        <span class="text-caption text-danger font-medium">CRT: {{ $severitySummary['critical_count'] }}</span>
                        <span class="text-caption text-warning-text font-medium">· HI: {{ $severitySummary['high_count'] }}</span>
                        <span class="text-caption text-text-muted font-medium">· WRN: {{ $severitySummary['warning_count'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Total Nasabah --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[200ms] bg-surface rounded-radius-lg border border-border p-6 hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Total Nasabah</p>
                        <p class="text-3xl font-bold text-text mt-2">{{ $totalCustomers }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-surface-muted flex items-center justify-center">
                        <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <p class="text-caption text-text-muted mt-4">Total keseluruhan profil nasabah</p>
            </div>

            {{-- Total Saldo Tabungan --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[300ms] bg-surface rounded-radius-lg border border-border p-6 hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Total Saldo Tabungan</p>
                        <p class="text-3xl font-bold text-text mt-2">Rp {{ number_format($totalSavings, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-surface-muted flex items-center justify-center">
                        <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-caption text-text-muted mt-4">Jumlah saldo tersimpan di ledger</p>
            </div>

            {{-- Transaksi Hari Ini --}}
            <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-500 delay-[400ms] bg-surface rounded-radius-lg border border-border p-6 hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Transaksi Hari Ini</p>
                        <p class="text-3xl font-bold text-text mt-2">{{ $todayTransactions }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-surface-muted flex items-center justify-center">
                        <svg class="w-6 h-6 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-caption text-text-muted mt-4">Hari ini: {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Monthly Flow Indicators --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[500ms] grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Setoran Bulan Ini --}}
        <div class="bg-surface rounded-radius-lg border border-border p-6 flex items-center gap-6 shadow-sm">
            <div class="w-14 h-14 rounded-radius-lg bg-primary-light/30 text-primary flex items-center justify-center shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Setoran Bulan Ini</p>
                <p class="text-2xl font-black text-text mt-1">Rp {{ number_format($thisMonthDeposits, 0, ',', '.') }}</p>
                <p class="text-caption text-text-muted mt-1">Total volume credit bulan ini</p>
            </div>
        </div>

        {{-- Penarikan Bulan Ini --}}
        <div class="bg-surface rounded-radius-lg border border-border p-6 flex items-center gap-6 shadow-sm">
            <div class="w-14 h-14 rounded-radius-lg bg-danger-light/30 text-danger flex items-center justify-center shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Penarikan Bulan Ini</p>
                <p class="text-2xl font-black text-text mt-1">Rp {{ number_format($thisMonthWithdrawals, 0, ',', '.') }}</p>
                <p class="text-caption text-text-muted mt-1">Total volume debit bulan ini</p>
            </div>
        </div>
    </div>

    {{-- Tabs Section --}}
    <div :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" class="transition-all duration-700 delay-[600ms] bg-surface rounded-[2.5rem] shadow-sm border border-border overflow-hidden">
        {{-- Tabs Navigation --}}
        <div class="border-b border-border-subtle/60 px-6 sm:px-8 py-5 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-text tracking-tight">Detail Temuan Audit</h3>
                <span class="text-xs bg-surface-muted px-2 py-0.5 rounded-radius-sm text-text-muted font-medium">Read-Only</span>
            </div>
            
            {{-- Tabs Controls --}}
            <div class="flex flex-wrap gap-2">
                <button @click="activeTab = 'mismatch'" :class="activeTab === 'mismatch' ? 'bg-primary text-white' : 'text-text-muted hover:bg-surface-muted'" class="px-4 py-2 rounded-radius-lg text-xs font-bold transition">
                    Mismatch Balance ({{ $balanceMismatches->total() }})
                </button>
                <button @click="activeTab = 'duplicate'" :class="activeTab === 'duplicate' ? 'bg-primary text-white' : 'text-text-muted hover:bg-surface-muted'" class="px-4 py-2 rounded-radius-lg text-xs font-bold transition">
                    Duplicate Ledger ({{ $duplicateLedgers->total() }})
                </button>
                <button @click="activeTab = 'orphan_tx'" :class="activeTab === 'orphan_tx' ? 'bg-primary text-white' : 'text-text-muted hover:bg-surface-muted'" class="px-4 py-2 rounded-radius-lg text-xs font-bold transition">
                    Orphan Tx ({{ $orphanTransactions->total() }})
                </button>
                <button @click="activeTab = 'orphan_ledger'" :class="activeTab === 'orphan_ledger' ? 'bg-primary text-white' : 'text-text-muted hover:bg-surface-muted'" class="px-4 py-2 rounded-radius-lg text-xs font-bold transition">
                    Orphan Ledger ({{ $orphanLedgers->total() }})
                </button>
                <button @click="activeTab = 'legacy'" :class="activeTab === 'legacy' ? 'bg-primary text-white' : 'text-text-muted hover:bg-surface-muted'" class="px-4 py-2 rounded-radius-lg text-xs font-bold transition">
                    Legacy Unmapped ({{ $legacyUnmapped->total() }})
                </button>
            </div>
        </div>

        {{-- Tab Contents --}}
        <div class="p-6 sm:p-8">
            {{-- Tab: Mismatch Balance --}}
            <div x-show="activeTab === 'mismatch'" class="space-y-4">
                @if ($balanceMismatches->isEmpty())
                    <div class="py-8 text-center text-text-muted text-sm">Tidak ada ketidaksesuaian saldo nasabah (100% konsisten).</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs uppercase text-text-muted border-b border-border-subtle">
                                    <th class="py-3 px-4">Kode Nasabah</th>
                                    <th class="py-3 px-4">Nama</th>
                                    <th class="py-3 px-4 text-right">Kalkulasi Transaksi</th>
                                    <th class="py-3 px-4 text-right">Saldo Ledger</th>
                                    <th class="py-3 px-4 text-right">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-subtle/60 text-sm text-text">
                                @foreach ($balanceMismatches as $m)
                                    <tr>
                                        <td class="py-3.5 px-4 font-mono font-bold">{{ $m['customer_code'] }}</td>
                                        <td class="py-3.5 px-4 font-medium">{{ $m['name'] }}</td>
                                        <td class="py-3.5 px-4 text-right">Rp {{ number_format($m['calculated'], 2, ',', '.') }}</td>
                                        <td class="py-3.5 px-4 text-right">Rp {{ number_format($m['ledger'], 2, ',', '.') }}</td>
                                        <td class="py-3.5 px-4 text-right text-danger font-bold">Rp {{ number_format($m['diff'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $balanceMismatches->links() }}</div>
                @endif
            </div>

            {{-- Tab: Duplicate Ledger --}}
            <div x-show="activeTab === 'duplicate'" class="space-y-4" style="display: none;">
                @if ($duplicateLedgers->isEmpty())
                    <div class="py-8 text-center text-text-muted text-sm">Tidak ada ledger terduplikasi.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs uppercase text-text-muted border-b border-border-subtle">
                                    <th class="py-3 px-4">Nasabah ID</th>
                                    <th class="py-3 px-4">Tipe Referensi</th>
                                    <th class="py-3 px-4">ID Referensi</th>
                                    <th class="py-3 px-4 text-right">Jumlah (Amount)</th>
                                    <th class="py-3 px-4 text-center">Jumlah Ledger Terduplikasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-subtle/60 text-sm text-text">
                                @foreach ($duplicateLedgers as $d)
                                    <tr>
                                        <td class="py-3.5 px-4 font-mono">{{ $d['waste_customer_id'] }}</td>
                                        <td class="py-3.5 px-4 font-bold">{{ $d['type'] }}</td>
                                        <td class="py-3.5 px-4">#{{ $d['id'] }}</td>
                                        <td class="py-3.5 px-4 text-right">Rp {{ number_format($d['amount'], 2, ',', '.') }}</td>
                                        <td class="py-3.5 px-4 text-center text-danger font-bold">{{ $d['count'] }} Entri</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $duplicateLedgers->links() }}</div>
                @endif
            </div>

            {{-- Tab: Orphan Tx --}}
            <div x-show="activeTab === 'orphan_tx'" class="space-y-4" style="display: none;">
                @if ($orphanTransactions->isEmpty())
                    <div class="py-8 text-center text-text-muted text-sm">Tidak ada transaksi yatim (seluruh transaksi memiliki ledger).</div>
                @else
                    <ul class="divide-y divide-border-subtle/60">
                        @foreach ($orphanTransactions as $ot)
                            <li class="py-3 flex items-center justify-between text-sm">
                                <span class="font-semibold text-text">{{ $ot }}</span>
                                <span class="text-xs bg-danger/10 text-danger-text px-2 py-0.5 rounded-radius-sm font-bold">Ledger Hilang</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4">{{ $orphanTransactions->links() }}</div>
                @endif
            </div>

            {{-- Tab: Orphan Ledger --}}
            <div x-show="activeTab === 'orphan_ledger'" class="space-y-4" style="display: none;">
                @if ($orphanLedgers->isEmpty())
                    <div class="py-8 text-center text-text-muted text-sm">Tidak ada ledger yatim (seluruh ledger bersumber dari transaksi valid).</div>
                @else
                    <ul class="divide-y divide-border-subtle/60">
                        @foreach ($orphanLedgers as $ol)
                            <li class="py-3 flex items-center justify-between text-sm">
                                <span class="font-semibold text-text">{{ $ol }}</span>
                                <span class="text-xs bg-danger/10 text-danger-text px-2 py-0.5 rounded-radius-sm font-bold">Transaksi Sumber Hilang</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4">{{ $orphanLedgers->links() }}</div>
                @endif
            </div>

            {{-- Tab: Legacy Unmapped --}}
            <div x-show="activeTab === 'legacy'" class="space-y-4" style="display: none;">
                @if ($legacyUnmapped->isEmpty())
                    <div class="py-8 text-center text-text-muted text-sm">Seluruh data transaksi lama telah berhasil dipetakan ke nasabah.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs uppercase text-text-muted border-b border-border-subtle">
                                    <th class="py-3 px-4">Tabel Database</th>
                                    <th class="py-3 px-4">ID Transaksi</th>
                                    <th class="py-3 px-4">Tanggal Transaksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-subtle/60 text-sm text-text">
                                @foreach ($legacyUnmapped as $lu)
                                    <tr>
                                        <td class="py-3.5 px-4 font-mono font-bold">{{ $lu['table'] }}</td>
                                        <td class="py-3.5 px-4 font-medium">#{{ $lu['transaction_id'] }}</td>
                                        <td class="py-3.5 px-4 text-text-muted text-xs">
                                            {{ $lu['created_at'] ? \Carbon\Carbon::parse($lu['created_at'])->format('d/m/Y H:i:s') : 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $legacyUnmapped->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
</x-layouts.dashboard>
