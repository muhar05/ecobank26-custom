<x-layouts.dashboard title="Agregator">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header: Master Agregator --}}
        <x-ui.page-header title="Agregator" description="Master mitra pengepul Bank Sampah. Atur profil agregator beserta harga lapak per material dari halaman detail.">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Agregator</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.collectors.create') }}'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Agregator
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @if(session('success'))
            <div class="p-4 bg-success-light border border-success/30 text-success-text rounded-radius-md flex items-center gap-3 shadow-subtle">
                <svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-body-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <x-ui.card id="table-section" variant="bordered" class="p-0 overflow-hidden space-y-0 scroll-mt-20">
            <div class="p-4 sm:p-5 border-b border-border-subtle bg-surface">
                <x-table-toolbar :search="$search ?? ''" placeholder="Cari agregator berdasarkan nama, kontak, atau alamat..." />
            </div>

            @if($collectors->isNotEmpty())
                {{-- DESKTOP TABLE: Master Agregator --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted/40 text-body-sm font-bold text-text-muted uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Nama Agregator</th>
                                <th class="py-3 px-4">Kontak</th>
                                <th class="py-3 px-4">Alamat</th>
                                <th class="py-3 px-4 text-center">Material</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach($collectors as $collector)
                                @php
                                    $materialCount = (int) ($collector->waste_prices_count ?? $collector->wastePrices->count());
                                @endphp
                                <tr class="hover:bg-surface-muted/40 transition-subtle">
                                    <td class="py-3.5 px-4 text-center font-mono text-body-sm text-text-muted">
                                        {{ $collectors->firstItem() + $loop->index }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-body-sm shrink-0 shadow-subtle">
                                                {{ strtoupper(substr($collector->name, 0, 1)) }}
                                            </div>
                                            <a href="{{ route('bank-sampah.collectors.show', $collector) }}" class="text-body font-bold text-text hover:text-primary transition-subtle">
                                                {{ $collector->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if($collector->phone)
                                            @php
                                                $waPhone = preg_replace('/[^0-9]/', '', $collector->phone);
                                                if(substr($waPhone, 0, 1) === '0') {
                                                    $waPhone = '62' . substr($waPhone, 1);
                                                }
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono text-body-sm text-text">{{ $collector->phone }}</span>
                                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 bg-success-light text-success-text border border-success/30 rounded-radius-sm text-caption font-semibold hover:bg-success/20 transition-subtle">
                                                    WhatsApp
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-caption text-text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="text-body-sm text-text-muted max-w-xs truncate" title="{{ $collector->address }}">
                                            {{ $collector->address ?: '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        @if($materialCount > 0)
                                            <a href="{{ route('bank-sampah.collectors.show', $collector) }}#harga-lapak" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-surface-muted border border-border font-mono text-caption font-bold text-text hover:border-primary/40 hover:text-primary transition-subtle" title="Lihat harga lapak">
                                                {{ $materialCount }} Material
                                            </a>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-caption text-text-muted italic">
                                                Belum ada harga
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center justify-end gap-3 text-body-sm font-semibold">
                                            <a href="{{ route('bank-sampah.collectors.show', $collector) }}"
                                               class="text-body-sm font-bold text-primary hover:underline transition-subtle px-1 py-2">
                                                Lihat Detail
                                            </a>
                                            <a href="{{ route('bank-sampah.collectors.edit', $collector) }}"
                                               class="text-body-sm font-semibold text-text-muted hover:text-text transition-subtle px-1 py-2">
                                                Edit
                                            </a>
                                            <button type="button"
                                                    @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.collectors.destroy', $collector) }}'})"
                                                    class="text-body-sm font-semibold text-danger hover:underline transition-subtle cursor-pointer px-1 py-2">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE ADAPTIVE CARDS (< md) --}}
                <div class="block md:hidden divide-y divide-border-subtle p-3 space-y-3">
                    @foreach($collectors as $collector)
                        @php
                            $materialCount = (int) ($collector->waste_prices_count ?? $collector->wastePrices->count());
                        @endphp
                        <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-body-sm shrink-0">
                                        {{ strtoupper(substr($collector->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('bank-sampah.collectors.show', $collector) }}" class="text-body font-bold text-text hover:text-primary transition-subtle block">
                                            {{ $collector->name }}
                                        </a>
                                        @if($materialCount > 0)
                                            <span class="font-mono text-body-sm font-bold text-primary">{{ $materialCount }} Material</span>
                                        @else
                                            <span class="text-caption text-text-muted italic">Belum ada harga</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-caption pt-1 border-t border-border-subtle">
                                <div>
                                    <span class="text-text-muted uppercase font-bold block">Kontak</span>
                                    <span class="font-mono font-medium text-body-sm text-text block">{{ $collector->phone ?: '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-text-muted uppercase font-bold block">Alamat</span>
                                    <span class="text-body-sm text-text-muted block truncate">{{ $collector->address ?: '-' }}</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-border-subtle flex items-center justify-between gap-2">
                                <a href="{{ route('bank-sampah.collectors.show', $collector) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-radius-md bg-primary text-white text-body-sm font-bold hover:bg-primary-hover shadow-subtle transition-subtle">
                                    Lihat Detail
                                </a>
                                <a href="{{ route('bank-sampah.collectors.edit', $collector) }}" class="px-3 py-2.5 text-body-sm font-semibold text-text-muted hover:text-text transition-subtle">Edit</a>
                                <button type="button" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.collectors.destroy', $collector) }}'})" class="px-3 py-2.5 text-body-sm font-semibold text-danger transition-subtle">Hapus</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($collectors->hasPages())
                    <div class="p-4 border-t border-border-subtle bg-surface-muted/30">
                        {{ $collectors->links() }}
                    </div>
                @endif
            @else
                <div class="p-4 sm:p-5">
                    <x-ui.empty-state
                        title="Belum ada agregator yang terdaftar"
                        description="Tambahkan mitra pengepul pertama agar harga lapak per material bisa dikelola dari halaman detail agregator.">
                        <x-slot:action>
                            <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.collectors.create') }}'">
                                + Tambah Agregator
                            </x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            @endif
        </x-ui.card>
    </div>

    <x-confirm-delete-modal />
</x-layouts.dashboard>
