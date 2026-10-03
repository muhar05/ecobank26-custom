<x-layouts.dashboard title="Kas Bank Sampah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header --}}
        <x-ui.page-header title="Laporan Kas Bank Sampah" description="Laporan arus kas masuk (margin/operasional) dan keluar">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Kas Bank Sampah</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <a href="{{ route('bank-sampah.cash-report.export', request()->only(['date_from', 'date_to'])) }}" class="inline-flex items-center gap-2 bg-surface border border-border text-text px-3.5 py-2 rounded-radius-md text-body-sm font-semibold hover:bg-surface-muted shadow-subtle transition-subtle min-h-[44px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Export CSV
                </a>
                <a href="{{ route('bank-sampah.cash-report.pdf', request()->all()) }}" target="_blank" class="inline-flex items-center gap-2 bg-danger text-white px-3.5 py-2 rounded-radius-md text-body-sm font-semibold hover:bg-danger shadow-subtle transition-subtle min-h-[44px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Download PDF
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Premium Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-6 flex flex-col justify-center relative overflow-hidden">
                <div class="relative">
                    <p class="text-body-sm font-bold text-text-muted uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Total Kas Masuk
                    </p>
                    <p class="font-mono text-heading-lg font-bold text-text mt-2">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-surface rounded-radius-lg shadow-subtle border border-border p-6 flex flex-col justify-center relative overflow-hidden">
                <div class="relative">
                    <p class="text-body-sm font-bold text-text-muted uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        Total Kas Keluar
                    </p>
                    <p class="font-mono text-heading-lg font-bold text-text mt-2">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-primary rounded-radius-lg shadow-subtle p-6 flex flex-col justify-center relative overflow-hidden">
                <div class="relative">
                    <p class="text-body-sm font-bold text-white/80 uppercase tracking-wider">
                        Saldo Kas Saat Ini
                    </p>
                    <p class="font-mono text-heading-lg font-bold text-white mt-2">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div id="table-section" 
             x-data="{ tableLoading: false }" 
             @click="if($event.target.closest('nav[role=\'navigation\'] a') || $event.target.closest('a.page-link')) tableLoading = true" 
             :class="{'opacity-70 cursor-wait pointer-events-none': tableLoading}"
             class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden transition-all duration-300 relative">
             
            {{-- Top Loading Bar --}}
            <div x-show="tableLoading" style="display: none;" class="absolute top-0 inset-x-0 h-1 z-50 bg-primary-light">
                <div class="h-full bg-primary w-full animate-pulse"></div>
            </div>

            {{-- Filter --}}
            <div class="p-4 border-b border-border bg-surface-muted/50">
                <form method="GET" action="{{ route('bank-sampah.cash-report') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="flex flex-col sm:flex-row items-end gap-4 w-full">
                    <div class="space-y-1.5 w-full sm:w-auto">
                        <label class="block text-body-sm font-semibold text-text-muted">Periode Dari</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-radius-md border-border bg-surface text-text shadow-subtle text-body focus:border-primary focus:ring-primary h-10">
                    </div>
                    <div class="space-y-1.5 w-full sm:w-auto">
                        <label class="block text-body-sm font-semibold text-text-muted">Sampai</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-radius-md border-border bg-surface text-text shadow-subtle text-body focus:border-primary focus:ring-primary h-10">
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="submit" :disabled="isSubmitting" :class="{'opacity-75 cursor-not-allowed': isSubmitting}" class="w-full sm:w-auto h-10 bg-primary text-white px-5 rounded-radius-md text-body-sm font-semibold hover:bg-primary-hover transition-subtle shadow-subtle">
                            <span x-text="isSubmitting ? 'Memfilter...' : 'Terapkan Filter'"></span>
                        </button>
                        @if(request('date_from') || request('date_to'))
                            <a href="{{ route('bank-sampah.cash-report') }}" class="h-10 inline-flex items-center justify-center bg-surface-muted text-text-muted px-5 rounded-radius-md text-body-sm font-semibold hover:bg-surface-muted transition-subtle">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Cash Book Table --}}
            @if($ledgers->isEmpty())
                <div class="p-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-surface-muted rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-body-sm text-text-muted">Belum ada transaksi kas pada periode ini.</p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-surface">
                            <tr class="border-b border-border bg-surface-muted/40 text-body-sm font-bold text-text-muted uppercase tracking-wider">
                                <th scope="col" class="px-6 py-4 text-left w-32">Tanggal</th>
                                <th scope="col" class="px-6 py-4 text-left">Keterangan Transaksi</th>
                                <th scope="col" class="px-6 py-4 text-right">Kas Masuk</th>
                                <th scope="col" class="px-6 py-4 text-right">Kas Keluar</th>
                                <th scope="col" class="px-6 py-4 text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach($ledgers as $ledger)
                                <tr class="hover:bg-surface-muted transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-body font-semibold text-text">{{ $ledger->date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($ledger->type === 'in')
                                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-light flex items-center justify-center text-primary">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                </div>
                                            @else
                                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-danger-light flex items-center justify-center text-danger">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                                </div>
                                            @endif
                                            <div class="text-body text-text">{{ $ledger->description }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if($ledger->type === 'in')
                                            <div class="font-mono text-body font-extrabold text-primary">Rp {{ number_format($ledger->amount, 0, ',', '.') }}</div>
                                        @else
                                            <div class="text-body text-text-muted">-</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if($ledger->type === 'out')
                                            <div class="font-mono text-body font-extrabold text-danger">Rp {{ number_format($ledger->amount, 0, ',', '.') }}</div>
                                        @else
                                            <div class="text-body text-text-muted">-</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="inline-flex items-center px-3 py-1 rounded-radius-sm bg-surface-muted/50">
                                            <span class="font-mono text-body font-bold text-text">Rp {{ number_format($ledger->balance, 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            @if(method_exists($ledgers, 'hasPages') && $ledgers->hasPages())
                <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $ledgers->links() }}
                </div>
            @elseif(!method_exists($ledgers, 'hasPages') && $ledgers instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $ledgers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
