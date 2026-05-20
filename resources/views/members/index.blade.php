<x-layouts.dashboard title="Data Warga">
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Data Warga</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('members.import') }}" class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import
                </a>
                <a href="{{ route('members.create') }}" class="inline-flex items-center gap-2 bg-emerald-700 dark:bg-emerald-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah
                </a>
            </div>
        </div>

        <x-table-toolbar :search="$search ?? ''" placeholder="Cari nama, kode, telepon..." />

        @if(session('success'))
            <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Telepon</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Akun</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($members as $member)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                <td class="px-6 py-4 text-sm font-mono text-slate-900 dark:text-slate-100">{{ $member->member_code }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $member->name }}</div>
                                    @if($member->address)
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ Str::limit($member->address, 40) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $member->phone ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if($member->user_id)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">Terhubung</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Belum terhubung</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <a href="{{ route('members.show', $member) }}" class="text-slate-600 dark:text-slate-300 hover:underline">Detail</a>
                                    <a href="{{ route('members.edit', $member) }}" class="text-emerald-700 dark:text-emerald-400 hover:underline">Edit</a>
                                    <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('members.destroy', $member) }}'})" class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">Belum ada data warga.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            {{ $members->links() }}
        </div>
    </div>

    <x-confirm-delete-modal />
</x-layouts.dashboard>
