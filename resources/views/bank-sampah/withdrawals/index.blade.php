<x-layouts.dashboard title="Penarikan Saldo">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header --}}
        <x-ui.page-header title="Penarikan Saldo" description="Kelola transaksi penarikan saldo oleh nasabah">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Penarikan Saldo</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.withdrawals.create') }}'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Catat Penarikan
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @if(session('success'))
            <div class="p-4 bg-primary-light border border-primary/30 text-primary rounded-radius-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden transition-colors duration-300">
            <div class="p-4 border-b border-border bg-surface-muted/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="w-full sm:max-w-md">
                    <x-table-toolbar :search="$search ?? ''" placeholder="Cari data penarikan..." />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface">
                        <tr class="border-b border-border bg-surface-muted/40 text-body-sm font-bold text-text-muted uppercase tracking-wider">
                            <th scope="col" class="px-6 py-4 text-left">Tanggal</th>
                            <th scope="col" class="px-6 py-4 text-left">Nasabah</th>
                            <th scope="col" class="px-6 py-4 text-right">Jumlah Penarikan</th>
                            <th scope="col" class="px-6 py-4 text-left">Catatan</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($withdrawals as $w)
                            <tr class="hover:bg-surface-muted transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-body font-semibold text-text">{{ $w->date->format('d/m/Y') }}</div>
                                    <div class="text-caption text-text-muted mt-0.5">{{ $w->date->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-info-light flex items-center justify-center text-info-text">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                        <div class="text-body font-bold text-text">{{ $w->wasteCustomer->name ?? '-' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-danger-light border border-danger/30/30">
                                        <svg class="w-3.5 h-3.5 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                        <span class="text-body font-bold text-danger">Rp {{ number_format($w->amount, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-body-sm text-text-muted max-w-xs truncate" title="{{ $w->notes }}">
                                        {{ $w->notes ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-body font-semibold space-x-3">
                                    <a href="{{ route('bank-sampah.withdrawals.edit', $w) }}" class="text-primary hover:underline transition-subtle">Edit</a>
                                    <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.withdrawals.destroy', $w) }}'})" class="text-danger hover:underline transition-subtle">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-surface-muted rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"/></svg>
                                        </div>
                                        <p class="text-body-sm text-text-muted">Belum ada data transaksi penarikan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($withdrawals->hasPages())
                <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $withdrawals->links() }}
                </div>
            @endif
        </div>
    </div>

    <x-confirm-delete-modal />
</x-layouts.dashboard>
