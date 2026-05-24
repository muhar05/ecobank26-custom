<x-layouts.dashboard title="Saldo Nasabah">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Saldo Nasabah</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Laporan rekapitulasi total kredit, debit, dan saldo akhir setiap nasabah</p>
            </div>
            <div class="flex items-center">
                <a href="{{ route('bank-sampah.savings.export') }}" class="inline-flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition w-full sm:w-auto justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Export CSV
                </a>
            </div>
        </div>

        <div id="table-section" 
             x-data="{ tableLoading: false }" 
             @click="if($event.target.closest('nav[role=\'navigation\'] a') || $event.target.closest('a.page-link')) tableLoading = true" 
             :class="{'opacity-70 cursor-wait pointer-events-none': tableLoading}"
             class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-all duration-300 relative">
             
            {{-- Top Loading Bar --}}
            <div x-show="tableLoading" style="display: none;" class="absolute top-0 inset-x-0 h-1 z-50 bg-emerald-100 dark:bg-emerald-900/30">
                <div class="h-full bg-emerald-500 w-full animate-pulse"></div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-white dark:bg-slate-900">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nasabah</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Setoran (Kredit)</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Penarikan (Debit)</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($balances as $b)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                            <span class="text-sm font-bold">{{ substr($b->member->name ?? '?', 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $b->member->name ?? '-' }}</div>
                                            <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">{{ $b->member->member_code ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-medium text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($b->total_credit, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-medium text-rose-600 dark:text-rose-400">
                                        Rp {{ number_format($b->total_debit, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg {{ $b->balance >= 0 ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100' : 'bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400' }}">
                                        <span class="text-sm font-bold">Rp {{ number_format($b->balance, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada data saldo nasabah.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($balances, 'hasPages') && $balances->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    {{ $balances->links() }}
                </div>
            @elseif(!method_exists($balances, 'hasPages') && $balances instanceof \Illuminate\Pagination\LengthAwarePaginator)
                 <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    {{ $balances->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
