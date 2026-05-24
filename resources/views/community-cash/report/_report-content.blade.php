<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Buku Kas RT/RW</h1>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Laporan lengkap pemasukan dan pengeluaran kas warga</p>
</div>

<!-- Enhanced Filter Section -->
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 mb-8">
    <form method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                    class="block w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors">
            </div>
            
            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                    class="block w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors">
            </div>
            
            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Kategori</label>
                <select name="fund_category_id" 
                    class="block w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('fund_category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit" 
                    class="flex-1 inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                </button>
                @can('view_cash_reports')
                    <a href="{{ route('community-cash.report.export', request()->only(['date_from', 'date_to', 'fund_category_id'])) }}" 
                        class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export CSV
                    </a>
                @endcan
            </div>
        </div>
        
        @if(request()->hasAny(['date_from','date_to','fund_category_id']))
            <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Filter aktif: 
                    @if(request('date_from')) <span class="font-medium">{{ request('date_from') }}</span> @endif
                    @if(request('date_to')) - <span class="font-medium">{{ request('date_to') }}</span> @endif
                    @if(request('fund_category_id')) 
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 ml-2">
                            {{ $categories->find(request('fund_category_id'))->name ?? 'Kategori' }}
                        </span>
                    @endif
                </div>
                <a href="{{ url()->current() }}" 
                    class="inline-flex items-center gap-1 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reset Filter
                </a>
            </div>
        @endif
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Total Pemasukan</p>
                <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">
                    Rp {{ number_format($totalIn, 0, ',', '.') }}
                </p>
            </div>
            <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-red-600 dark:text-red-400">Total Pengeluaran</p>
                <p class="text-2xl font-bold text-red-800 dark:text-red-300 mt-1">
                    Rp {{ number_format($totalOut, 0, ',', '.') }}
                </p>
            </div>
            <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Saldo Saat Ini</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">
                    Rp {{ number_format($currentBalance, 0, ',', '.') }}
                </p>
            </div>
            <div class="w-12 h-12 bg-slate-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Balance per Category -->
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 mb-8">
    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Saldo Per Kategori</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($balancePerCategory as $item)
            <div class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">{{ $item->fundCategory->name }}</p>
                        <p class="text-lg font-bold {{ $item->balance >= 0 ? 'text-slate-900 dark:text-slate-100' : 'text-red-600 dark:text-red-400' }} mt-1">
                            Rp {{ number_format($item->balance, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="w-8 h-8 {{ $item->balance >= 0 ? 'bg-emerald-100 dark:bg-emerald-900/50' : 'bg-red-100 dark:bg-red-900/50' }} rounded-lg flex items-center justify-center">
                        @if($item->balance >= 0)
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Cash Book Table -->
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Buku Kas</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Riwayat lengkap transaksi kas RT/RW</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Keterangan</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Masuk</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Keluar</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Saldo</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($ledgers as $l)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-slate-100">
                            {{ $l->date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100 max-w-xs">
                            {{ $l->description }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $l->type === 'in' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300' }}">
                                {{ $l->fundCategory->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-emerald-700 dark:text-emerald-400">
                            {{ $l->type === 'in' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-700 dark:text-red-400">
                            {{ $l->type === 'out' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-slate-900 dark:text-slate-100">
                            Rp {{ number_format($l->balance, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-400 dark:text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="text-sm font-medium text-slate-900 dark:text-slate-100 mb-1">Belum ada data transaksi</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Data akan muncul setelah ada transaksi pemasukan atau pengeluaran</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
