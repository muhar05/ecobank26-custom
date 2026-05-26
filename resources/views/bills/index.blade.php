<x-layouts.dashboard title="Daftar Tagihan Iuran">
<div x-data="{ loading: false }" class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Tagihan Iuran Wajib</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar tagihan iuran wajib bulanan tingkat Kartu Keluarga (KK)</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('iuran.bills.generate.form') }}" class="inline-flex items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition w-full sm:w-auto justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Generate Tagihan
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-900/30 text-rose-800 dark:text-rose-300 rounded-xl border border-rose-200 dark:border-rose-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif
    @if(session('info'))
        <div class="p-4 bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-xl border border-blue-200 dark:border-blue-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm font-medium">{{ session('info') }}</span>
        </div>
    @endif

    {{-- Mini Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.75L3.75 19.5M21 21H3"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Total Nilai Tagihan</p>
                <p class="text-lg font-bold text-slate-900 dark:text-slate-100 mt-0.5">Rp {{ number_format($stats['total_bills'], 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-500 dark:text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Tagihan Lunas</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-500 dark:text-rose-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Total Tunggakan</p>
                <p class="text-lg font-bold text-rose-600 dark:text-rose-400 mt-0.5">Rp {{ number_format($stats['total_unpaid'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Main Table Area --}}
    <div id="table-section" 
         x-data="{ tableLoading: false }" 
         @click="if($event.target.closest('nav[role=\'navigation\'] a') || $event.target.closest('a.page-link')) tableLoading = true" 
         :class="{'opacity-70 cursor-wait pointer-events-none': tableLoading}"
         class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden relative transition-all duration-300">
         
        {{-- Loading Bar --}}
        <div x-show="tableLoading" style="display: none;" class="absolute top-0 inset-x-0 h-1 z-50 bg-emerald-100 dark:bg-emerald-900/30">
            <div class="h-full bg-emerald-500 w-full animate-pulse"></div>
        </div>

        {{-- Filters & Search --}}
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
            <form method="GET" action="{{ route('iuran.bills.index') }}" @submit="loading = true" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3 w-full">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Kepala Keluarga / Kode..." class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                </div>
                <!-- RT -->
                <div>
                    <select name="rt_id" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="">Semua RT</option>
                        @foreach($rts as $rt)
                            <option value="{{ $rt->id }}" {{ ($rtFilter ?? '') == $rt->id ? 'selected' : '' }}>RT {{ $rt->rt_number }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Kategori -->
                <div>
                    <select name="fund_category_id" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="">Semua Iuran</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($categoryFilter ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Bulan -->
                <div>
                    <select name="month" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="">Semua Bulan</option>
                        @foreach($months as $m => $name)
                            <option value="{{ $m }}" {{ ($monthFilter ?? '') == $m ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Status -->
                <div>
                    <select name="status" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        <option value="">Semua Status</option>
                        <option value="unpaid" {{ ($statusFilter ?? '') === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="partially_paid" {{ ($statusFilter ?? '') === 'partially_paid' ? 'selected' : '' }}>Dicicil</option>
                        <option value="paid" {{ ($statusFilter ?? '') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>
                <!-- Submit -->
                <div>
                    <button type="submit" :disabled="loading" class="w-full h-10 bg-emerald-600 dark:bg-emerald-500 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm inline-flex justify-center items-center">
                        <span x-text="loading ? 'Memproses...' : 'Filter'"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-white dark:bg-slate-900">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kode Tagihan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kepala Keluarga / RT</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Iuran Kas</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Periode</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tagihan</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 tracking-wider">Dibayar</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 tracking-wider">Sisa</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tenggat</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($bills as $bill)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition duration-150">
                            <td class="px-6 py-4">
                                <span class="text-sm font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ $bill->bill_code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-900 dark:text-slate-100">Keluarga {{ $bill->kk->family_head }}</div>
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-600 dark:text-slate-300 mt-1">
                                    RT {{ $bill->kk->rt->rt_number }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300 font-medium">
                                {{ $bill->fundCategory->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-slate-600 dark:text-slate-400 font-semibold">
                                {{ $months[$bill->month] }} {{ $bill->year }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-slate-900 dark:text-slate-100">
                                Rp {{ number_format($bill->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-medium text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($bill->total_paid, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-bold text-rose-600 dark:text-rose-400">
                                Rp {{ number_format($bill->outstanding_balance, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($bill->status === 'paid')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Lunas
                                    </span>
                                @elseif($bill->status === 'partially_paid')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Dicicil
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-800/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-right text-slate-500 dark:text-slate-400 font-semibold">
                                {{ $bill->due_date ? $bill->due_date->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($bill->status !== 'paid')
                                    <button @click="$dispatch('open-pay-modal', { 
                                        id: {{ $bill->id }}, 
                                        code: '{{ $bill->bill_code }}', 
                                        amount: {{ $bill->amount }}, 
                                        outstanding: {{ $bill->outstanding_balance }}, 
                                        head: '{{ addslashes($bill->kk->family_head) }}',
                                        category: '{{ addslashes($bill->fundCategory->name) }}',
                                        period: '{{ $months[$bill->month] }} {{ $bill->year }}'
                                    })" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 dark:bg-emerald-500 hover:bg-emerald-700 text-white transition">
                                        Bayar
                                    </button>
                                @else
                                    <span class="text-slate-400 text-xs font-semibold">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    </div>
                                    <p class="text-slate-500 dark:text-slate-400 text-sm font-semibold">Belum ada data tagihan.</p>
                                    <p class="text-slate-400 dark:text-slate-500 text-xs mt-1">Gunakan tombol Generate Tagihan di atas untuk membuat tagihan iuran KK baru.</p>
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
                <div class="p-4 bg-white dark:bg-slate-900 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">Keluarga {{ $bill->kk->family_head }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-mono">{{ $bill->bill_code }}</p>
                        </div>
                        <div>
                            @if($bill->status === 'paid')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400">Lunas</span>
                            @elseif($bill->status === 'partially_paid')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400">Dicicil</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400">Belum Bayar</span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl">
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 block uppercase tracking-wider text-[9px]">Iuran Kas:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $bill->fundCategory->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 block uppercase tracking-wider text-[9px]">Periode:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $months[$bill->month] }} {{ $bill->year }}</span>
                        </div>
                    </div>
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Total Tagihan:</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100">Rp {{ number_format($bill->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Sudah Dibayar:</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($bill->total_paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Sisa Tagihan:</span>
                            <span class="font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($bill->outstanding_balance, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @if($bill->status !== 'paid')
                        <div class="pt-2">
                            <button @click="$dispatch('open-pay-modal', { 
                                id: {{ $bill->id }}, 
                                code: '{{ $bill->bill_code }}', 
                                amount: {{ $bill->amount }}, 
                                outstanding: {{ $bill->outstanding_balance }}, 
                                head: '{{ addslashes($bill->kk->family_head) }}',
                                category: '{{ addslashes($bill->fundCategory->name) }}',
                                period: '{{ $months[$bill->month] }} {{ $bill->year }}'
                            })" class="w-full inline-flex justify-center items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold bg-emerald-600 dark:bg-emerald-500 hover:bg-emerald-700 text-white transition">
                                Bayar Tagihan
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    Belum ada data tagihan.
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

    {{-- Payment Modal --}}
    <div x-data="{ 
            open: false, 
            billId: null, 
            billCode: '', 
            headName: '', 
            categoryName: '', 
            period: '', 
            totalAmount: 0, 
            outstanding: 0,
            amountToPay: 0,
            paymentMethod: 'cash',
            paidAt: '{{ date('Y-m-d') }}',
            description: ''
         }"
         @open-pay-modal.window="
            billId = $event.detail.id;
            billCode = $event.detail.code;
            headName = $event.detail.head;
            categoryName = $event.detail.category;
            period = $event.detail.period;
            totalAmount = $event.detail.amount;
            outstanding = $event.detail.outstanding;
            amountToPay = outstanding;
            open = true;
         "
         x-show="open" 
         style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="open = false"
                 class="fixed inset-0 bg-slate-900 bg-opacity-50 dark:bg-opacity-70 transition-opacity" 
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Content --}}
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800">
                
                <form :action="'/iuran/tagihan/' + billId + '/pay'" method="POST" class="space-y-0">
                    @csrf
                    
                    {{-- Modal Header --}}
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100" id="modal-title">
                                Catat Pembayaran Iuran
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="billCode"></p>
                        </div>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 space-y-4">
                        {{-- Bill Overview Card --}}
                        <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-xl space-y-2 border border-slate-100 dark:border-slate-800 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Kepala Keluarga:</span>
                                <span class="font-bold text-slate-900 dark:text-slate-100" x-text="headName"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Kategori Iuran:</span>
                                <span class="font-bold text-slate-900 dark:text-slate-100" x-text="categoryName"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Periode:</span>
                                <span class="font-bold text-slate-900 dark:text-slate-100" x-text="period"></span>
                            </div>
                            <div class="h-px bg-slate-200 dark:bg-slate-800 my-2"></div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">Total Tagihan:</span>
                                <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(totalAmount)"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 dark:text-slate-400">Sisa Tagihan:</span>
                                <span class="font-bold text-rose-600 dark:text-rose-400" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(outstanding)"></span>
                            </div>
                        </div>

                        {{-- Input Amount --}}
                        <div class="space-y-1.5">
                            <label for="amount_paid" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Jumlah yang Dibayar (Rp)</label>
                            <input type="number" name="amount_paid" id="amount_paid" x-model="amountToPay" :max="outstanding" min="1" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">Mendukung pembayaran cicilan/sebagian.</span>
                        </div>

                        {{-- Payment Method --}}
                        <div class="space-y-1.5">
                            <label for="payment_method" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Metode Pembayaran</label>
                            <select name="payment_method" id="payment_method" x-model="paymentMethod" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                                <option value="cash">Tunai (Cash)</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>

                        {{-- Paid At --}}
                        <div class="space-y-1.5">
                            <label for="paid_at" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Tanggal Bayar</label>
                            <input type="date" name="paid_at" id="paid_at" x-model="paidAt" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                        </div>

                        {{-- Description --}}
                        <div class="space-y-1.5">
                            <label for="description" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Keterangan / Catatan Tambahan (Opsional)</label>
                            <textarea name="description" id="description" x-model="description" rows="2" placeholder="Contoh: Dibayar oleh istri, lunas." class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 p-2.5"></textarea>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-2.5">
                        <button type="button" @click="open = false" class="px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 dark:bg-emerald-500 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                            Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-layouts.dashboard>
