<x-layouts.dashboard title="Grup Kategori Sampah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Header Halaman --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-text">Grup Kategori Sampah</h2>
                <p class="text-sm text-text-muted mt-1">Kelola master data Grup Kategori Sampah secara fleksibel</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('bank-sampah.waste-categories.index') }}" class="inline-flex items-center gap-2 bg-surface border border-border text-text px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-surface-muted shadow-sm transition">
                    Kembali ke Kategori
                </a>
                <a href="{{ route('bank-sampah.waste-category-groups.create') }}" class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-primary-hover shadow-sm hover:shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Grup
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-primary-light border border-primary/30 text-primary rounded-radius-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div id="table-section" class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden transition-colors duration-300 scroll-mt-20">
            <div class="p-4 border-b border-border bg-surface-muted/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="w-full sm:max-w-md">
                    <x-table-toolbar :search="$search ?? ''" placeholder="Cari kode atau nama grup..." />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Kode Grup</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Nama Grup</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Deskripsi</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-text-muted uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-text-muted uppercase tracking-wider">Jumlah Kategori</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($groups as $group)
                            <tr class="hover:bg-surface-muted/50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-text">
                                    {{ $group->code }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-text">
                                    {{ $group->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-text-muted max-w-xs truncate">
                                    {{ $group->description ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($group->is_active)
                                        <x-ui.badge variant="success" size="sm">Aktif</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="danger" size="sm">Nonaktif</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-text">
                                    {{ $group->waste_categories_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <form action="{{ route('bank-sampah.waste-category-groups.toggle', $group) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs px-2.5 py-1 rounded-radius-md border {{ $group->is_active ? 'border-danger/30 text-danger hover:bg-danger-light' : 'border-primary/30 text-primary hover:bg-primary-light' }} transition-subtle">
                                            {{ $group->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('bank-sampah.waste-category-groups.edit', $group) }}" class="text-primary hover:underline transition-subtle">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <p class="text-text-muted text-sm">Belum ada grup kategori sampah yang ditambahkan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($groups->hasPages())
                <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $groups->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
