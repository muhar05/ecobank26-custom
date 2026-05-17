{{-- Filters --}}
<form method="GET" class="mb-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 flex flex-wrap gap-4 items-end transition-colors duration-300">
    <div>
        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Dari Tanggal</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>
    <div>
        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Sampai Tanggal</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>
    <div>
        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Kategori</label>
        <select name="fund_category_id" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">Semua</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('fund_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Filter</button>
    @can('view_cash_reports')
        <a href="{{ route('community-cash.report.export', request()->only(['date_from', 'date_to', 'fund_category_id'])) }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Export CSV</a>
    @endcan
    @if(request()->hasAny(['date_from','date_to','fund_category_id']))
        <a href="{{ url()->current() }}" class="text-sm text-slate-500 dark:text-slate-400 hover:underline">Reset</a>
    @endif
</form>

{{-- Summary --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-4 transition-colors duration-300">
        <p class="text-xs text-emerald-600 dark:text-emerald-400 uppercase font-medium">Total Pemasukan</p>
        <p class="text-xl font-bold text-emerald-800 dark:text-emerald-300">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
    </div>
    <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-2xl p-4 transition-colors duration-300">
        <p class="text-xs text-red-600 dark:text-red-400 uppercase font-medium">Total Pengeluaran</p>
        <p class="text-xl font-bold text-red-800 dark:text-red-300">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 transition-colors duration-300">
        <p class="text-xs text-slate-600 dark:text-slate-400 uppercase font-medium">Saldo Saat Ini</p>
        <p class="text-xl font-bold text-slate-900 dark:text-slate-50">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
    </div>
</div>

{{-- Balance per category --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Saldo Per Kategori</h3>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
        @foreach($balancePerCategory as $item)
            <div class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-3 transition-colors duration-300">
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->fundCategory->name }}</p>
                <p class="font-semibold {{ $item->balance >= 0 ? 'text-slate-900 dark:text-slate-100' : 'text-red-600 dark:text-red-400' }}">Rp {{ number_format($item->balance, 0, ',', '.') }}</p>
            </div>
        @endforeach
    </div>
</div>

{{-- Cash book table --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors duration-300">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Keterangan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Kategori</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Masuk</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Keluar</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($ledgers as $l)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $l->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $l->description }}</td>
                        <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $l->fundCategory->name }}</td>
                        <td class="px-4 py-3 text-sm text-right text-emerald-700 dark:text-emerald-400 font-medium">{{ $l->type === 'in' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '' }}</td>
                        <td class="px-4 py-3 text-sm text-right text-red-700 dark:text-red-400 font-medium">{{ $l->type === 'out' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '' }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium text-slate-900 dark:text-slate-100">Rp {{ number_format($l->balance, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-4 text-center text-slate-500 dark:text-slate-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
