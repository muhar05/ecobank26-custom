<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Iuran Warga</h2>
            <a href="{{ route('community-cash.contributions.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">Catat Iuran</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Warga</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($contributions as $c)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $c->date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $c->fundCategory->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $c->member_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($c->amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $c->description ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $c->recorder->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data iuran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
