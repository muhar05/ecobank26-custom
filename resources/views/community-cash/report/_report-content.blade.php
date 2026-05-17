{{-- Filters --}}
<form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
    <div>
        <label class="block text-xs text-gray-500">Dari Tanggal</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-xs text-gray-500">Sampai Tanggal</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-xs text-gray-500">Kategori</label>
        <select name="fund_category_id" class="rounded border-gray-300 text-sm">
            <option value="">Semua</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('fund_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Filter</button>
    @if(request()->hasAny(['date_from','date_to','fund_category_id']))
        <a href="{{ url()->current() }}" class="text-sm text-gray-500 hover:underline">Reset</a>
    @endif
</form>

{{-- Summary --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-green-50 border border-green-200 rounded p-4">
        <p class="text-xs text-green-600 uppercase font-medium">Total Pemasukan</p>
        <p class="text-xl font-bold text-green-800">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
    </div>
    <div class="bg-red-50 border border-red-200 rounded p-4">
        <p class="text-xs text-red-600 uppercase font-medium">Total Pengeluaran</p>
        <p class="text-xl font-bold text-red-800">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded p-4">
        <p class="text-xs text-blue-600 uppercase font-medium">Saldo Saat Ini</p>
        <p class="text-xl font-bold text-blue-800">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
    </div>
</div>

{{-- Balance per category --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-2">Saldo Per Kategori</h3>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
        @foreach($balancePerCategory as $item)
            <div class="bg-gray-50 border rounded p-3">
                <p class="text-xs text-gray-500">{{ $item->fundCategory->name }}</p>
                <p class="font-semibold {{ $item->balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">Rp {{ number_format($item->balance, 0, ',', '.') }}</p>
            </div>
        @endforeach
    </div>
</div>

{{-- Cash book table --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Masuk</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Keluar</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($ledgers as $l)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $l->date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $l->description }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $l->fundCategory->name }}</td>
                    <td class="px-4 py-3 text-sm text-right text-green-700">{{ $l->type === 'in' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '' }}</td>
                    <td class="px-4 py-3 text-sm text-right text-red-700">{{ $l->type === 'out' ? 'Rp ' . number_format($l->amount, 0, ',', '.') : '' }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium">Rp {{ number_format($l->balance, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
