<x-layouts.dashboard title="Saldo Nasabah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-text">Saldo Nasabah</h2>
                <p class="text-sm text-text-muted mt-1">Laporan rekapitulasi total kredit, debit, dan saldo akhir setiap nasabah</p>
            </div>
            <div class="flex items-center">
                <a href="{{ route('bank-sampah.savings.export') }}" class="inline-flex items-center gap-2 bg-surface border border-border text-text px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-surface-muted shadow-sm transition w-full sm:w-auto justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Export CSV
                </a>
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
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Nasabah</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">Total Setoran (Kredit)</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">Total Penarikan (Debit)</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-text uppercase tracking-wider">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($balances as $b)
                            <tr class="hover:bg-surface-muted transition duration-150">
                                @php
                                    $customerName = $b->wasteCustomer?->name ?? '-';
                                    $customerCode = $b->wasteCustomer?->customer_code ?? '-';
                                    $customerInitial = substr($customerName !== '-' ? $customerName : '?', 0, 1);
                                @endphp
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-info-light flex items-center justify-center text-info-text">
                                            <span class="text-sm font-bold">{{ $customerInitial }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-text">{{ $customerName }}</div>
                                            <div class="text-xs font-mono text-text-muted mt-0.5">{{ $customerCode }}</div>
                                            <span class="text-caption text-text-muted font-normal">{{ $b->wasteCustomer?->customer_code ?? '' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-medium text-primary">
                                        Rp {{ number_format($b->total_credit, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-medium text-danger">
                                        Rp {{ number_format($b->total_debit, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-radius-md {{ $b->balance >= 0 ? 'bg-surface-muted text-text' : 'bg-danger-light text-danger-text' }}">
                                        <span class="text-sm font-bold">Rp {{ number_format($b->balance, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-surface-muted rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <p class="text-text-muted text-sm">Belum ada data saldo nasabah.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($balances, 'hasPages') && $balances->hasPages())
                <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $balances->links() }}
                </div>
            @elseif(!method_exists($balances, 'hasPages') && $balances instanceof \Illuminate\Pagination\LengthAwarePaginator)
                 <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $balances->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
