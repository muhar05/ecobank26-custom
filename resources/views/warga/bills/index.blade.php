<x-layouts.dashboard title="Tagihan Saya">
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)" class="space-y-6 sm:space-y-8 pb-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Tagihan & Riwayat Pembayaran KK</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar lengkap tagihan iuran wajib bulanan tingkat Kartu Keluarga Anda</p>
        </div>
    </div>

    {{-- Main Filter & Table Area --}}
    <div id="table-section" 
         x-data="{ tableLoading: false }" 
         @click="if($event.target.closest('nav[role=\'navigation\'] a') || $event.target.closest('a.page-link')) tableLoading = true" 
         :class="{'opacity-70 cursor-wait pointer-events-none': tableLoading}"
         class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-200/60 dark:border-slate-800/60 overflow-hidden relative transition-all duration-300">
         
        {{-- Loading Bar --}}
        <div x-show="tableLoading" style="display: none;" class="absolute top-0 inset-x-0 h-1 z-50 bg-emerald-100 dark:bg-emerald-900/30">
            <div class="h-full bg-emerald-500 w-full animate-pulse"></div>
        </div>

        {{-- Filters & Search --}}
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20">
            <form method="GET" action="{{ route('warga.bills') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 w-full">
                <!-- Bulan -->
                <div>
                    <select name="month" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="">Semua Bulan</option>
                        @foreach($months as $m => $name)
                            <option value="{{ $m }}" {{ ($monthFilter ?? '') == $m ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Tahun -->
                <div>
                    <select name="year" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="">Semua Tahun</option>
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ ($yearFilter ?? '') == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <!-- Status -->
                <div>
                    <select name="status" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="">Semua Status</option>
                        <option value="unpaid" {{ ($statusFilter ?? '') === 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                        <option value="partially_paid" {{ ($statusFilter ?? '') === 'partially_paid' ? 'selected' : '' }}>Dicicil</option>
                        <option value="paid" {{ ($statusFilter ?? '') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>
                <!-- Submit -->
                <div>
                    <button type="submit" class="w-full h-10 bg-emerald-600 dark:bg-emerald-500 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm inline-flex justify-center items-center">
                        Filter Tagihan
                    </button>
                </div>
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/10 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4 text-left">Kode</th>
                        <th class="px-6 py-4 text-left">Nama Iuran</th>
                        <th class="px-6 py-4 text-center">Periode</th>
                        <th class="px-6 py-4 text-right">Tagihan</th>
                        <th class="px-6 py-4 text-right">Sudah Dibayar</th>
                        <th class="px-6 py-4 text-right">Sisa Tunggakan</th>
                        <th class="px-6 py-4 text-center">Jatuh Tempo</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($bills as $bill)
                        @php
                            $isOverdue = $bill->due_date && $bill->due_date->isPast() && $bill->status !== 'paid';
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                            <td class="px-6 py-4 font-mono font-bold text-slate-600 dark:text-slate-450">{{ $bill->bill_code }}</td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 dark:text-slate-100 block">{{ $bill->fundCategory->name }}</span>
                                {{-- Display Receipt history inside table as micro text if paid --}}
                                @if($bill->payments->isNotEmpty())
                                    <div class="mt-1.5 space-y-1">
                                        @foreach($bill->payments as $p)
                                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] text-slate-500 font-semibold">
                                                <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                                Kwitansi: {{ $p->receipt_number }} (Rp {{ number_format($p->amount_paid, 0, ',', '.') }})
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-medium">{{ $months[$bill->month] }} {{ $bill->year }}</td>
                            <td class="px-6 py-4 text-right font-semibold">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-emerald-600 font-semibold">Rp {{ number_format($bill->total_paid, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-rose-600 font-bold">Rp {{ number_format($bill->outstanding_balance, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="{{ $isOverdue ? 'text-rose-600 font-bold' : '' }}">
                                    {{ $bill->due_date ? $bill->due_date->format('d/m/Y') : '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    @if($bill->status === 'paid')
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-600">Lunas</span>
                                    @elseif($bill->status === 'partially_paid')
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600">Dicicil</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600">Belum Bayar</span>
                                    @endif
                                    @if($isOverdue)
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 animate-pulse">Terlambat</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-emerald-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-sm font-bold text-slate-800">Hebat! Tidak ada tagihan.</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Semua tagihan untuk keluarga Anda dalam keadaan lunas / tidak ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($bills as $bill)
                @php
                    $isOverdue = $bill->due_date && $bill->due_date->isPast() && $bill->status !== 'paid';
                @endphp
                <div class="p-5 bg-white dark:bg-slate-900 space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $bill->fundCategory->name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5 font-mono">{{ $bill->bill_code }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            @if($bill->status === 'paid')
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-600">Lunas</span>
                            @elseif($bill->status === 'partially_paid')
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600">Dicicil</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600">Belum Bayar</span>
                            @endif
                            @if($isOverdue)
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Terlambat</span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl">
                        <div>
                            <span class="text-slate-500 block uppercase tracking-wider text-[9px]">Periode:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $months[$bill->month] }} {{ $bill->year }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block uppercase tracking-wider text-[9px]">Jatuh Tempo:</span>
                            <span class="font-bold {{ $isOverdue ? 'text-rose-600' : 'text-slate-800' }}">{{ $bill->due_date ? $bill->due_date->format('d/m/Y') : '—' }}</span>
                        </div>
                    </div>
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Tagihan:</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100">Rp {{ number_format($bill->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Sudah Dibayar:</span>
                            <span class="font-semibold text-emerald-600">Rp {{ number_format($bill->total_paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-bold">Sisa Tunggakan:</span>
                            <span class="font-bold text-rose-600">Rp {{ number_format($bill->outstanding_balance, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @if($bill->payments->isNotEmpty())
                        <div class="border-t border-slate-100 dark:border-slate-850 pt-3">
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider block font-semibold mb-2">Riwayat Kwitansi Pembayaran:</p>
                            <div class="space-y-1.5">
                                @foreach($bill->payments as $p)
                                    <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-800/40 p-2 rounded-lg text-xs">
                                        <div>
                                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ $p->receipt_number }}</p>
                                            <p class="text-[10px] text-slate-500">{{ $p->paid_at->format('d/m/Y') }}</p>
                                        </div>
                                        <span class="font-bold text-emerald-600">Rp {{ number_format($p->amount_paid, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-500">
                    Tidak ada data tagihan.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($bills->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $bills->links() }}
            </div>
        @endif

    </div>

</div>
</x-layouts.dashboard>
