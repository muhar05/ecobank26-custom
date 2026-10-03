<x-layouts.dashboard title="Data Agregator">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-text">Data Agregator</h2>
                <p class="text-sm text-text-muted mt-1">Kelola daftar agregator atau mitra bank sampah</p>
            </div>
            <div class="flex items-center">
                <a href="{{ route('bank-sampah.collectors.create') }}" class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-primary-hover shadow-sm hover:shadow transition w-full sm:w-auto justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Agregator
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-primary-light border border-primary/30 text-primary rounded-radius-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden transition-colors duration-300">
            <div class="p-4 border-b border-border bg-surface-muted/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="w-full sm:max-w-md">
                    <x-table-toolbar :search="$search ?? ''" placeholder="Cari agregator berdasarkan nama..." />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Nama & Kontak</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Alamat</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($collectors as $collector)
                            <tr class="hover:bg-surface-muted transition duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-info-light flex items-center justify-center text-info-text">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <div>
                                            <a href="{{ route('bank-sampah.collectors.show', $collector) }}" class="text-sm font-semibold text-text hover:text-primary transition-subtle">
                                                {{ $collector->name }}
                                            </a>
                                            <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-caption font-mono font-semibold bg-surface-muted text-text-muted dark:bg-surface-muted dark:text-text">
                                                {{ $collector->waste_prices_count ?? $collector->wastePrices->count() }} harga
                                            </span>
                                            @if($collector->phone)
                                                @php
                                                    $waPhone = preg_replace('/[^0-9]/', '', $collector->phone);
                                                    if(substr($waPhone, 0, 1) === '0') {
                                                        $waPhone = '62' . substr($waPhone, 1);
                                                    }
                                                @endphp
                                                <div class="mt-1 flex items-center gap-2">
                                                    <span class="text-xs text-text-muted">{{ $collector->phone }}</span>
                                                    <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 bg-primary-light text-primary rounded-radius-sm text-caption font-medium hover:bg-primary/20 transition-subtle">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                                        WhatsApp
                                                    </a>
                                                </div>
                                            @else
                                                <div class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 bg-surface-muted text-text-muted dark:bg-surface-muted dark:text-text-muted rounded text-caption font-medium border border-border">
                                                    No WhatsApp
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-text-muted max-w-xs truncate" title="{{ $collector->address }}">
                                        {{ $collector->address ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('bank-sampah.collectors.show', $collector) }}" class="text-primary hover:underline transition">Harga Lapak &rarr;</a>
                                    <a href="{{ route('bank-sampah.collectors.edit', $collector) }}" class="text-text-muted hover:text-text transition">Edit</a>
                                    <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.collectors.destroy', $collector) }}'})" class="text-danger hover:underline transition-subtle">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-surface-muted rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <p class="text-text-muted text-sm">Belum ada data agregator yang ditambahkan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($collectors->hasPages())
                <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $collectors->links() }}
                </div>
            @endif
        </div>
    </div>

    <x-confirm-delete-modal />
</x-layouts.dashboard>
