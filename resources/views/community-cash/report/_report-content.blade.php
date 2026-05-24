<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ request()->routeIs('warga.*') ? 'Laporan Kas Warga' : 'Buku Kas RT/RW' }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Laporan lengkap pemasukan dan pengeluaran kas warga</p>
    </div>
</div>

<!-- Enhanced Filter Section -->
<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 mb-8">
    <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Kategori</label>
                <select name="fund_category_id" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 h-10">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('fund_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="flex items-center gap-2 w-full md:w-auto">
            <button type="submit" class="h-10 bg-emerald-600 dark:bg-emerald-500 text-white px-5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm inline-flex justify-center items-center w-full md:w-auto">
                Filter
            </button>
            @if(request()->hasAny(['date_from','date_to','fund_category_id']))
                <a href="{{ url()->current() }}" class="h-10 inline-flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-5 rounded-lg text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    Reset
                </a>
            @endif
            @can('view_cash_reports')
                <a href="{{ route('community-cash.report.export', request()->only(['date_from', 'date_to', 'fund_category_id'])) }}" class="h-10 inline-flex items-center justify-center bg-blue-600 dark:bg-blue-500 text-white px-5 rounded-lg text-sm font-semibold hover:bg-blue-700 dark:hover:bg-blue-400 transition shadow-sm w-full md:w-auto">
                    Export CSV
                </a>
            @endcan
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 shadow-lg shadow-emerald-500/20 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-20">
            <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </div>
        <div class="relative z-10">
            <p class="text-emerald-100 text-sm font-medium uppercase tracking-wider mb-1">Total Pemasukan</p>
            <p class="text-3xl font-bold">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-2xl p-6 shadow-lg shadow-rose-500/20 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-20">
            <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
        </div>
        <div class="relative z-10">
            <p class="text-rose-100 text-sm font-medium uppercase tracking-wider mb-1">Total Pengeluaran</p>
            <p class="text-3xl font-bold">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
        </div>
    </div>
    
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 shadow-lg shadow-slate-900/20 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-20">
            <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div class="relative z-10">
            <p class="text-slate-400 text-sm font-medium uppercase tracking-wider mb-1">Saldo Saat Ini</p>
            <p class="text-3xl font-bold">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
        </div>
    </div>
</div>

<!-- Balance per Category -->
<div class="mb-8">
    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4">Saldo Per Kategori</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($balancePerCategory as $item)
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider mb-0.5">{{ $item->fundCategory->name }}</p>
                    <p class="text-lg font-bold {{ $item->balance >= 0 ? 'text-slate-900 dark:text-slate-100' : 'text-rose-600 dark:text-rose-400' }}">
                        Rp {{ number_format($item->balance, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-full {{ $item->balance >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400' }} flex items-center justify-center">
                    @if($item->balance >= 0)
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    @else
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Cash Book Table -->
<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Buku Kas</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <thead class="bg-slate-50/50 dark:bg-slate-800/50">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Keterangan</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Masuk</th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Keluar</th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($ledgers as $l)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-slate-100">
                            {{ $l->date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $l->type === 'in' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' }}">
                                    @if($l->type === 'in')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                    @endif
                                </div>
                                <span class="text-sm font-medium text-slate-900 dark:text-slate-100 max-w-[200px] truncate" title="{{ $l->description }}">{{ $l->description }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium {{ $l->type === 'in' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30' : 'bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-800/30' }}">
                                {{ $l->fundCategory->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($l->type === 'in')
                                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($l->amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-slate-300 dark:text-slate-600">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($l->type === 'out')
                                <span class="text-sm font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($l->amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-slate-300 dark:text-slate-600">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-slate-900 dark:text-slate-100">
                            Rp {{ number_format($l->balance, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada data transaksi.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
