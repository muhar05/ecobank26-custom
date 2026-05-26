<x-layouts.dashboard title="Laporan Tunggakan Iuran">
<div x-data="{ loading: false }" class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Laporan Tunggakan & WhatsApp Reminder</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar warga/KK yang masih memiliki tunggakan iuran wajib bulanan</p>
        </div>
    </div>

    {{-- Arrears Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-500 dark:text-rose-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Total Tunggakan</p>
                <p class="text-lg font-bold text-rose-600 dark:text-rose-400 mt-0.5">Rp {{ number_format($stats['total_tunggakan'], 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">KK Menunggak</p>
                <p class="text-lg font-bold text-slate-900 dark:text-slate-100 mt-0.5">{{ $stats['jumlah_kk_menunggak'] }} KK</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-500 dark:text-rose-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Unpaid (Belum Bayar)</p>
                <p class="text-lg font-bold text-rose-600 dark:text-rose-400 mt-0.5">Rp {{ number_format($stats['total_unpaid'], 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-500 dark:text-blue-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Partially Paid (Dicicil)</p>
                <p class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-0.5">Rp {{ number_format($stats['total_partially_paid'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Main Filter & Table Area --}}
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
            <form method="GET" action="{{ route('iuran.bills.arrears') }}" @submit="loading = true" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 w-full">
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Kepala Keluarga..." class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
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
                    <!-- Tahun -->
                    <div>
                        <select name="year" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                            <option value="">Semua Tahun</option>
                            @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ ($yearFilter ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                    <div class="flex items-center">
                        <label class="inline-flex items-center cursor-pointer text-sm text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="overdue" value="1" {{ ($overdueOnly ?? false) ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500 shadow-sm mr-2.5">
                            Hanya Tampilkan yang Terlambat (Overdue)
                        </label>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('iuran.bills.arrears') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-850 hover:bg-slate-50 rounded-lg text-sm font-semibold transition inline-flex items-center h-10">
                            Reset
                        </a>
                        <button type="submit" :disabled="loading" class="px-5 py-2 bg-emerald-600 dark:bg-emerald-500 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm inline-flex justify-center items-center h-10">
                            <span x-text="loading ? 'Memproses...' : 'Terapkan Filter'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-white dark:bg-slate-900">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kepala Keluarga / RT</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Iuran Kas</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Periode</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tagihan</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 tracking-wider">Sisa Tunggakan</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jatuh Tempo</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($bills as $bill)
                        @php
                            $isOverdue = $bill->due_date && $bill->due_date->isPast();
                            
                            $message = "Halo Bapak/Ibu " . $bill->kk->family_head . ",\n\n" .
                                       "Kami mengingatkan bahwa tagihan:\n" .
                                       "• " . $bill->fundCategory->name . "\n" .
                                       "• Periode: " . $months[$bill->month] . " " . $bill->year . "\n" .
                                       "• Nominal: Rp " . number_format($bill->amount, 0, ',', '.') . "\n" .
                                       "• Sisa Tunggakan: Rp " . number_format($bill->outstanding_balance, 0, ',', '.') . "\n\n" .
                                       "belum lunas.\n\n" .
                                       "Kode Tagihan: " . $bill->bill_code . "\n\n" .
                                       "Mohon segera melakukan pembayaran.\n" .
                                       "Terima kasih.";
                            
                            $phone = $bill->kk->phone;
                            if ($phone) {
                                $phone = preg_replace('/[^0-9]/', '', $phone);
                                if (str_starts_with($phone, '0')) {
                                    $phone = '62' . substr($phone, 1);
                                }
                            }
                            
                            $waUrl = $phone 
                                ? "https://wa.me/" . $phone . "?text=" . urlencode($message)
                                : "#";
                        @endphp
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
                            <td class="px-6 py-4 text-sm text-right font-bold text-rose-600 dark:text-rose-400">
                                Rp {{ number_format($bill->outstanding_balance, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center font-semibold text-slate-600 dark:text-slate-400">
                                <span class="{{ $isOverdue ? 'text-rose-600 dark:text-rose-400 font-bold' : '' }}">
                                    {{ $bill->due_date ? $bill->due_date->format('d/m/Y') : '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    @if($bill->status === 'partially_paid')
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30">
                                            Dicicil
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-800/30">
                                            Belum Bayar
                                        </span>
                                    @endif
                                    @if($isOverdue)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 dark:bg-rose-955 text-rose-800 dark:text-rose-300 animate-pulse">
                                            Terlambat
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($phone)
                                    <a href="{{ $waUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.717-1.45L0 24zm6.59-4.846c1.6.95 3.197 1.45 4.817 1.45.006 0 11.002 0 11.002-10.604-.002-5.362-4.364-9.72-9.734-9.72-5.372 0-9.734 4.361-9.736 9.724-.001 1.704.455 3.371 1.32 4.827L2.47 21.132l6.177-1.62-.003-.358z"/></svg>
                                        WhatsApp
                                    </a>
                                @else
                                    <div class="flex flex-col items-center">
                                        <button disabled class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 rounded-lg text-xs font-bold cursor-not-allowed">
                                            WhatsApp
                                        </button>
                                        <span class="text-[9px] text-rose-500 font-bold mt-1">No WhatsApp</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Semua Tagihan Lunas!</p>
                                    <p class="text-slate-400 dark:text-slate-500 text-xs mt-1">Tidak ada data tunggakan tagihan yang ditemukan untuk filter ini.</p>
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
                    $isOverdue = $bill->due_date && $bill->due_date->isPast();
                    
                    $message = "Halo Bapak/Ibu " . $bill->kk->family_head . ",\n\n" .
                               "Kami mengingatkan bahwa tagihan:\n" .
                               "• " . $bill->fundCategory->name . "\n" .
                               "• Periode: " . $months[$bill->month] . " " . $bill->year . "\n" .
                               "• Nominal: Rp " . number_format($bill->amount, 0, ',', '.') . "\n" .
                               "• Sisa Tunggakan: Rp " . number_format($bill->outstanding_balance, 0, ',', '.') . "\n\n" .
                               "belum lunas.\n\n" .
                               "Kode Tagihan: " . $bill->bill_code . "\n\n" .
                               "Mohon segera melakukan pembayaran.\n" .
                               "Terima kasih.";
                    
                    $phone = $bill->kk->phone;
                    if ($phone) {
                        $phone = preg_replace('/[^0-9]/', '', $phone);
                        if (str_starts_with($phone, '0')) {
                            $phone = '62' . substr($phone, 1);
                        }
                    }
                    
                    $waUrl = $phone 
                        ? "https://wa.me/" . $phone . "?text=" . urlencode($message)
                        : "#";
                @endphp
                <div class="p-4 bg-white dark:bg-slate-900 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">Keluarga {{ $bill->kk->family_head }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-mono">{{ $bill->bill_code }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            @if($bill->status === 'partially_paid')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400">Dicicil</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400">Belum Bayar</span>
                            @endif
                            @if($isOverdue)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 dark:bg-rose-955 text-rose-800 dark:text-rose-300">Terlambat</span>
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
                            <span class="text-slate-500 dark:text-slate-400">Sisa Tunggakan:</span>
                            <span class="font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($bill->outstanding_balance, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Jatuh Tempo:</span>
                            <span class="font-semibold {{ $isOverdue ? 'text-rose-600' : 'text-slate-800' }}">{{ $bill->due_date ? $bill->due_date->format('d/m/Y') : '—' }}</span>
                        </div>
                    </div>
                    <div class="pt-2">
                        @if($phone)
                            <a href="{{ $waUrl }}" target="_blank" class="w-full inline-flex justify-center items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.717-1.45L0 24zm6.59-4.846c1.6.95 3.197 1.45 4.817 1.45.006 0 11.002 0 11.002-10.604-.002-5.362-4.364-9.72-9.734-9.72-5.372 0-9.734 4.361-9.736 9.724-.001 1.704.455 3.371 1.32 4.827L2.47 21.132l6.177-1.62-.003-.358z"/></svg>
                                Kirim WhatsApp Reminder
                            </a>
                        @else
                            <button disabled class="w-full inline-flex justify-center items-center gap-1.5 px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 rounded-lg text-xs font-bold cursor-not-allowed">
                                No WhatsApp Available
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    Tidak ada data tunggakan tagihan yang ditemukan.
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
