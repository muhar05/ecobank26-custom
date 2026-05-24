<x-layouts.dashboard title="Riwayat Tabungan">
    @if(!$member)
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 text-center transition-colors duration-300">
            <p class="text-slate-500 dark:text-slate-400">Akun Anda belum terhubung dengan data warga/nasabah.</p>
            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Hubungi admin untuk menghubungkan akun.</p>
        </div>
    @else
        <div class="space-y-6" x-data="{ isSubmitting: false, datePreset: '{{ request('date_preset') }}' }">
            {{-- Filter Toolbar --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-4 transition-colors duration-300">
                <form method="GET" action="{{ route('warga.savings.history') }}" @submit="isSubmitting = true" class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Search --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Pencarian</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari keterangan / nominal..." class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        </div>
                        
                        {{-- Type --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Jenis Transaksi</label>
                            <select name="type" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                                <option value="">Semua Transaksi</option>
                                <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Kredit (Pemasukan)</option>
                                <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit (Penarikan)</option>
                            </select>
                        </div>
                        
                        {{-- Sort --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Urutkan</label>
                            <select name="sort" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Tanggal Terbaru</option>
                                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Tanggal Terlama</option>
                                <option value="amount_desc" {{ request('sort') === 'amount_desc' ? 'selected' : '' }}>Nominal Terbesar</option>
                                <option value="amount_asc" {{ request('sort') === 'amount_asc' ? 'selected' : '' }}>Nominal Terkecil</option>
                            </select>
                        </div>

                        {{-- Date Preset --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Waktu</label>
                            <select name="date_preset" x-model="datePreset" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                                <option value="">Semua Waktu</option>
                                <option value="today">Hari ini</option>
                                <option value="last_week">Minggu lalu</option>
                                <option value="last_month">Sebulan lalu</option>
                                <option value="custom">Kustom Tanggal</option>
                            </select>
                        </div>
                    </div>
                    
                    {{-- Custom Date Fields --}}
                    <div x-show="datePreset === 'custom'" style="display: none;" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Dari Tanggal</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Sampai Tanggal</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="submit" :disabled="isSubmitting" :class="{'opacity-75 cursor-not-allowed': isSubmitting}" class="h-10 bg-emerald-600 dark:bg-emerald-500 text-white px-5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm w-full sm:w-auto flex justify-center items-center">
                            <span x-text="isSubmitting ? 'Memfilter...' : 'Terapkan Filter'"></span>
                        </button>
                        @if(request()->hasAny(['search', 'type', 'sort', 'date_preset']))
                            <a href="{{ route('warga.savings.history') }}" class="h-10 inline-flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-5 rounded-lg text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition w-full sm:w-auto">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div id="table-section" 
                 x-data="{ tableLoading: false }" 
                 @click="if($event.target.closest('nav[role=\'navigation\'] a') || $event.target.closest('a.page-link')) tableLoading = true" 
                 :class="{'opacity-70 cursor-wait pointer-events-none': tableLoading}"
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-all duration-300 relative">
                 
                {{-- Top Loading Bar --}}
                <div x-show="tableLoading" style="display: none;" class="absolute top-0 inset-x-0 h-1 z-50 bg-emerald-100 dark:bg-emerald-900/30">
                    <div class="h-full bg-emerald-500 w-full animate-pulse"></div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Keterangan</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Kredit</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Debit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($ledgers as $l)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                    <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100 whitespace-nowrap">{{ $l->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                                        {{ $l->description }}
                                        @if($l->reference_type)
                                            @php
                                                $refType = class_basename($l->reference_type);
                                                $refName = match($refType) {
                                                    'Deposit' => 'Setoran',
                                                    'Withdrawal' => 'Penarikan',
                                                    'WasteSale' => 'Penjualan',
                                                    default => $refType
                                                };
                                            @endphp
                                            <span class="block text-xs text-slate-400 mt-0.5 font-mono">{{ $refName }} #{{ $l->reference_id }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-emerald-700 dark:text-emerald-400 font-medium whitespace-nowrap">{{ $l->type === 'credit' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '' }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-red-600 dark:text-red-400 font-medium whitespace-nowrap">{{ $l->type === 'debit' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </div>
                                            <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada riwayat transaksi yang sesuai.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($ledgers, 'hasPages') && $ledgers->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                        {{ $ledgers->links() }}
                    </div>
                @elseif(!method_exists($ledgers, 'hasPages') && $ledgers instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                        {{ $ledgers->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-layouts.dashboard>
