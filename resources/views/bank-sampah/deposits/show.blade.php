<x-layouts.dashboard title="Detail Setoran DEP-{{ str_pad($deposit->id, 5, '0', STR_PAD_LEFT) }}">
    @php
        $details = $deposit->details;
        $totalWeight = $details->sum('weight');
        $totalAmount = $details->sum('subtotal');
        $detailCount = $details->count();
        $depCode = 'DEP-' . str_pad($deposit->id, 5, '0', STR_PAD_LEFT);
    @endphp

    <div class="max-w-7xl mx-auto space-y-5 pb-10">
        {{-- Page Header & Breadcrumb --}}
        <x-ui.page-header title="Detail Setoran" :description="$depCode">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.deposits.index') }}" class="hover:text-text transition-subtle">Setoran</a>
                <span>&rarr;</span>
                <span class="text-text font-mono font-bold">{{ $depCode }}</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <a href="{{ route('bank-sampah.deposits.index') }}" class="text-body-sm font-semibold text-text-muted hover:text-text transition-subtle px-1 py-1">
                    &larr; Kembali ke Daftar
                </a>
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.deposits.edit', $deposit) }}'">
                    Edit Setoran
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Konteks Transaksi --}}
        <div class="bg-surface rounded-radius-lg border border-border p-4 sm:p-5 shadow-subtle">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-radius-md bg-surface-muted/60 border border-border">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-radius-md bg-primary text-white flex items-center justify-center font-bold text-heading-sm shadow-subtle shrink-0">
                        <span>{{ strtoupper(substr($deposit->wasteCustomer->name ?? 'N', 0, 1)) }}</span>
                    </div>
                    <div>
                        <h3 class="text-heading-sm font-bold text-text">{{ $deposit->wasteCustomer->name ?? 'Nasabah Umum' }}</h3>
                        @if($deposit->wasteCustomer && $deposit->wasteCustomer->customer_code)
                            <p class="text-body-sm text-text-muted font-mono mt-0.5">{{ $deposit->wasteCustomer->customer_code }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-body">
                    <div>
                        <span class="text-body-sm uppercase tracking-wider text-text-muted font-semibold">Tanggal:</span>
                        <span class="font-semibold text-text ml-1">{{ $deposit->date ? $deposit->date->translatedFormat('d M Y') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-body-sm uppercase tracking-wider text-text-muted font-semibold">Nomor:</span>
                        <span class="font-mono font-bold text-text ml-1 bg-surface px-2 py-0.5 rounded-radius-sm border border-border">{{ $depCode }}</span>
                    </div>
                </div>
            </div>

            @if($deposit->notes)
                <div class="mt-3 px-1 text-body-sm text-text-muted">
                    <span class="font-semibold text-text-muted">Catatan:</span> {{ $deposit->notes }}
                </div>
            @endif
        </div>

        {{-- Tabel Rincian Material Transaksi --}}
        <div class="bg-surface rounded-radius-lg border border-border shadow-subtle overflow-hidden">
            <div class="p-4 border-b border-border-subtle bg-surface">
                <h2 class="text-heading-sm font-bold text-text">Rincian Material Timbangan</h2>
                <p class="text-body-sm text-text-muted mt-0.5">
                    {{ $detailCount }} material tercatat dalam transaksi ini
                </p>
            </div>

            @if($detailCount > 0)
                {{-- DESKTOP TABLE (md+) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-muted/40 border-b border-border text-body-sm font-bold text-text-muted uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4 w-14 text-center">No</th>
                                <th class="py-3 px-4 min-w-[240px]">Material</th>
                                <th class="py-3 px-4 w-36 text-right">Berat</th>
                                <th class="py-3 px-4 w-44 text-right">Harga Satuan</th>
                                <th class="py-3 px-4 w-44 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach($details as $i => $det)
                                <tr class="hover:bg-surface-muted/50 transition-subtle">
                                    <td class="py-3 px-4 text-center text-body-sm font-mono text-text-muted">{{ $i + 1 }}</td>
                                    <td class="py-3 px-4">
                                        <span class="text-body font-semibold text-text">{{ $det->wasteCategory->name ?? 'Material' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <span class="font-mono text-body font-semibold text-text">{{ number_format($det->weight, 2, ',', '.') }}</span>
                                        <span class="text-body-sm text-text-muted ml-0.5">{{ $det->wasteCategory->unit ?? 'kg' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <span class="font-mono text-body text-text">Rp {{ number_format($det->price_per_unit, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <span class="font-mono text-body font-bold text-primary">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARDS (<md) --}}
                <div class="block md:hidden divide-y divide-border-subtle">
                    @foreach($details as $i => $det)
                        <div class="p-4 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-body-sm font-mono text-text-muted shrink-0">{{ $i + 1 }}.</span>
                                    <span class="text-body font-semibold text-text truncate">{{ $det->wasteCategory->name ?? 'Material' }}</span>
                                </div>
                                <div class="text-body text-text-muted mt-0.5 pl-6">
                                    {{ number_format($det->weight, 2, ',', '.') }} {{ $det->wasteCategory->unit ?? 'kg' }}
                                    @ Rp {{ number_format($det->price_per_unit, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-mono text-body font-bold text-primary">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Strip Ringkasan Total --}}
                <div class="border-t border-border bg-surface-muted/40 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-body">
                            <div>
                                <span class="text-text-muted font-semibold">Total Material:</span>
                                <span class="font-bold text-text ml-1">{{ $detailCount }}</span>
                            </div>
                            <div>
                                <span class="text-text-muted font-semibold">Total Berat:</span>
                                <span class="font-mono font-bold text-text ml-1">{{ number_format($totalWeight, 2, ',', '.') }} kg</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-body-sm text-text-muted font-semibold">Total Nilai Setoran:</span>
                            <span class="font-mono text-heading-sm font-bold text-primary ml-2">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="p-10 text-center">
                    <p class="text-body text-text-muted">Tidak ada rincian material pada transaksi ini.</p>
                </div>
            @endif
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('bank-sampah.deposits.index') }}" class="text-body-sm font-semibold text-text-muted hover:text-text transition-subtle">
                &larr; Kembali ke Daftar Setoran
            </a>
            <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.deposits.edit', $deposit) }}'">
                Edit Setoran
            </x-ui.button>
        </div>
    </div>
</x-layouts.dashboard>
