<x-layouts.dashboard title="Kategori Sampah">
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Kategori Sampah</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('bank-sampah.waste-categories.import') }}" class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import
                </a>
                <a href="{{ route('bank-sampah.waste-categories.create') }}" class="inline-flex items-center gap-2 bg-emerald-700 dark:bg-emerald-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
        @endif

        <x-table-toolbar :search="$search ?? ''" placeholder="Cari kategori..." />

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors duration-300">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Satuan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $category->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $category->unit }}</td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="{{ route('bank-sampah.waste-categories.edit', $category) }}" class="text-emerald-700 dark:text-emerald-400 hover:underline">Edit</a>
                                <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.waste-categories.destroy', $category) }}'})" class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">Belum ada kategori sampah.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </div>

    <x-confirm-delete-modal />
</x-layouts.dashboard>
