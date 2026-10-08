<x-layouts.dashboard title="Laporan Setoran Sampah">
    <div class="space-y-6">
        <!-- Header -->
        <x-ui.page-header title="Laporan Setoran Sampah" description="Pantau rincian setoran sampah nasabah ke bank sampah secara riil">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Laporan Setoran</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <a href="{{ route('bank-sampah.reports.deposits.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-surface border border-border text-text px-3.5 py-2 rounded-radius-md text-body-sm font-semibold hover:bg-surface-muted shadow-subtle transition-subtle min-h-[44px]">
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak / Simpan PDF
                </a>
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.reports.deposits.excel', request()->query()) }}'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Ekspor Excel
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @if($errors->has('date_range'))
            <div class="p-4 bg-danger-light border border-danger/30 text-danger-text rounded-radius-lg">
                {{ $errors->first('date_range') }}
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-body-sm font-bold text-text-muted uppercase tracking-wide">Total Transaksi</p>
                <p class="font-mono text-heading-lg font-bold text-text mt-1">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
            </div>
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-body-sm font-bold text-primary uppercase tracking-wide">Total Berat</p>
                <p class="font-mono text-heading-lg font-bold text-text mt-1">{{ number_format($totalWeight, 2, ',', '.') }} <span class="text-caption font-normal">kg</span></p>
            </div>
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-body-sm font-bold text-info-text uppercase tracking-wide">Total Nilai Setoran</p>
                <p class="font-mono text-heading-lg font-bold text-text mt-1">Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
            </div>
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-body-sm font-bold text-warning-text uppercase tracking-wide">Rata-rata Setoran</p>
                <p class="font-mono text-heading-lg font-bold text-text mt-1">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Sticky Filter Bar -->
        <div class="bg-surface rounded-radius-lg shadow-sm border border-border p-4 transition-colors duration-300">
            <form action="{{ route('bank-sampah.reports.deposits') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-body-sm font-semibold text-text-muted uppercase tracking-wider mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="block w-full rounded-radius-md border-border bg-surface text-body-sm shadow-sm focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-body-sm font-semibold text-text-muted uppercase tracking-wider mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="block w-full rounded-radius-md border-border bg-surface text-body-sm shadow-sm focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-body-sm font-semibold text-text-muted uppercase tracking-wider mb-1">Nasabah</label>
                    <x-ui.combobox
                        name="waste_customer_id"
                        :options="$customers"
                        :value="$customerId"
                        placeholder="Semua Nasabah"
                        searchPlaceholder="Cari nasabah..."
                        allowClear
                        compact
                    />
                </div>
                <div>
                    <label class="block text-body-sm font-semibold text-text-muted uppercase tracking-wider mb-1">Grup Kategori</label>
                    <select name="waste_category_group_id" class="block w-full rounded-radius-md border-border bg-surface text-body-sm shadow-sm focus:ring-primary focus:border-primary">
                        <option value="">Semua Grup</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" {{ $groupId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 md:col-span-1 flex items-end">
                    <button type="submit" class="w-full bg-primary text-white font-semibold text-body-sm px-4 py-2.5 rounded-radius-md hover:bg-primary transition">
                        Filter Laporan
                    </button>
                </div>
            </form>
        </div>

        <!-- Data List -->
        <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden">
            <!-- DESKTOP VIEW (Table) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface-muted/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-caption font-bold text-text-muted uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-4 text-left text-caption font-bold text-text-muted uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-4 text-left text-caption font-bold text-text-muted uppercase tracking-wider">Nasabah</th>
                            <th class="px-6 py-4 text-left text-caption font-bold text-text-muted uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-4 text-right class text-caption font-bold text-text-muted uppercase tracking-wider">Berat</th>
                            <th class="px-6 py-4 text-right class text-caption font-bold text-text-muted uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-4 text-right class text-caption font-bold text-text-muted uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($details as $d)
                            <tr class="hover:bg-surface-muted transition">
                                <td class="px-6 py-4 whitespace-nowrap text-body text-text-muted">
                                    {{ $d->deposit->date ? $d->deposit->date->toDateString() : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-body font-bold text-text">
                                    DEP-{{ str_pad($d->deposit_id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-body font-bold text-text">
                                    {{ $d->deposit->wasteCustomer->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-body font-semibold text-text">{{ $d->display_label }}</div>
                                    <div class="text-caption font-mono text-text-muted mt-0.5">{{ $d->wasteCategory?->wasteCategoryGroup?->name ?? ($d->wasteCategory ? 'Legacy/Belum Grup' : 'Historical Exception') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-body font-semibold text-text">
                                    {{ number_format($d->weight, 2, ',', '.') }} kg
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-body text-text-muted">
                                    Rp {{ number_format($d->price_per_unit, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-body font-extrabold text-primary">
                                    Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-text-muted text-body-sm">
                                    Tidak ada data setoran dalam periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MOBILE VIEW (Card List Collapse) -->
            <div class="block sm:hidden divide-y divide-border-subtle bg-surface">
                @forelse($details as $d)
                    <div class="p-4 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-body-sm font-semibold text-text-muted font-mono">DEP-{{ str_pad($d->deposit_id, 5, '0', STR_PAD_LEFT) }}</span>
                                <h4 class="text-body font-bold text-text mt-0.5">
                                    {{ $d->deposit->wasteCustomer->name ?? '-' }}
                                </h4>
                            </div>
                            <span class="text-caption text-text-muted">{{ $d->deposit->date ? $d->deposit->date->toDateString() : '-' }}</span>
                        </div>
                        <div class="border-t border-dashed border-border-subtle my-2"></div>
                        <div class="flex justify-between text-body-sm">
                            <span class="text-text-muted">Kategori:</span>
                            <span class="font-semibold text-text">{{ $d->display_label }}</span>
                        </div>
                        <div class="flex justify-between text-body-sm">
                            <span class="text-text-muted">Berat / Harga:</span>
                            <span class="font-semibold text-text">{{ number_format($d->weight, 2, ',', '.') }} kg @ Rp {{ number_format($d->price_per_unit, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-body-sm pt-1">
                            <span class="text-text-muted font-semibold">Subtotal:</span>
                            <span class="font-bold text-primary">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-text-muted text-body-sm">
                        Tidak ada data setoran dalam periode ini.
                    </div>
                @endforelse
            </div>

            @if($details->hasPages())
                <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $details->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
