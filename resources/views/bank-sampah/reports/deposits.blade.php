<x-layouts.dashboard title="Laporan Setoran Sampah">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-text">Laporan Setoran Sampah</h2>
                <p class="text-sm text-text-muted mt-1">Pantau rincian setoran sampah nasabah ke bank sampah secara riil</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('bank-sampah.reports.deposits.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-surface border border-border text-text px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-surface-muted shadow-sm transition">
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak / Simpan PDF
                </a>
                <a href="{{ route('bank-sampah.reports.deposits.excel', request()->query()) }}" class="inline-flex items-center gap-1.5 bg-primary text-white px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-primary-hover shadow-sm hover:shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Ekspor Excel
                </a>
            </div>
        </div>

        @if($errors->has('date_range'))
            <div class="p-4 bg-danger-light border border-danger/30 text-danger-text rounded-radius-lg">
                {{ $errors->first('date_range') }}
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-xs text-text-muted font-semibold uppercase tracking-wide">Total Transaksi</p>
                <p class="text-2xl font-bold text-text mt-1">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
            </div>
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-xs text-primary font-semibold uppercase tracking-wide">Total Berat</p>
                <p class="text-2xl font-bold text-text mt-1">{{ number_format($totalWeight, 2, ',', '.') }} <span class="text-xs font-normal">kg</span></p>
            </div>
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-xs text-info-text font-semibold uppercase tracking-wide">Total Nilai Setoran</p>
                <p class="text-2xl font-bold text-text mt-1">Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
            </div>
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-xs text-warning-text font-semibold uppercase tracking-wide">Rata-rata Setoran</p>
                <p class="text-2xl font-bold text-text mt-1">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Sticky Filter Bar -->
        <div class="bg-surface rounded-radius-lg shadow-sm border border-border p-4 transition-colors duration-300">
            <form action="{{ route('bank-sampah.reports.deposits') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-text-muted uppercase tracking-wider mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="block w-full rounded-radius-md border-border bg-surface text-sm shadow-sm focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-muted uppercase tracking-wider mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="block w-full rounded-radius-md border-border bg-surface text-sm shadow-sm focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-muted uppercase tracking-wider mb-1">Nasabah</label>
                    <select name="waste_customer_id" class="block w-full rounded-radius-md border-border bg-surface text-sm shadow-sm focus:ring-primary focus:border-primary">
                        <option value="">Semua Nasabah</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-muted uppercase tracking-wider mb-1">Grup Kategori</label>
                    <select name="waste_category_group_id" class="block w-full rounded-radius-md border-border bg-surface text-sm shadow-sm focus:ring-primary focus:border-primary">
                        <option value="">Semua Grup</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" {{ $groupId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 md:col-span-1 flex items-end">
                    <button type="submit" class="w-full bg-primary text-white font-semibold text-sm px-4 py-2.5 rounded-radius-md hover:bg-primary transition">
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
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase">Kode</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase">Nasabah</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase">Kategori</th>
                            <th class="px-6 py-4 text-right class text-xs font-semibold text-text-muted uppercase">Berat</th>
                            <th class="px-6 py-4 text-right class text-xs font-semibold text-text-muted uppercase">Harga</th>
                            <th class="px-6 py-4 text-right class text-xs font-semibold text-text-muted uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($details as $d)
                            <tr class="hover:bg-surface-muted transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                                    {{ $d->deposit->date ? $d->deposit->date->toDateString() : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-semibold text-text">
                                    DEP-{{ str_pad($d->deposit_id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-text">
                                    {{ $d->deposit->wasteCustomer->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-text">{{ $d->wasteCategory->name ?? '-' }}</div>
                                    <div class="text-xs font-mono text-text-muted mt-0.5">{{ $d->wasteCategory->wasteCategoryGroup->name ?? 'Legacy/Belum Grup' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-text">
                                    {{ number_format($d->weight, 2, ',', '.') }} kg
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-text-muted">
                                    Rp {{ number_format($d->price_per_unit, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-primary">
                                    Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-text-muted text-sm">
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
                                <span class="text-xs font-semibold text-text-muted font-mono">DEP-{{ str_pad($d->deposit_id, 5, '0', STR_PAD_LEFT) }}</span>
                                <h4 class="text-sm font-bold text-text mt-0.5">
                                    {{ $d->deposit->wasteCustomer->name ?? '-' }}
                                </h4>
                            </div>
                            <span class="text-xs text-text-muted">{{ $d->deposit->date ? $d->deposit->date->toDateString() : '-' }}</span>
                        </div>
                        <div class="border-t border-dashed border-border-subtle my-2"></div>
                        <div class="flex justify-between text-xs">
                            <span class="text-text-muted">Kategori:</span>
                            <span class="font-semibold text-text">{{ $d->wasteCategory->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-text-muted">Berat / Harga:</span>
                            <span class="font-semibold text-text">{{ number_format($d->weight, 2, ',', '.') }} kg @ Rp {{ number_format($d->price_per_unit, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs pt-1">
                            <span class="text-text-muted font-semibold">Subtotal:</span>
                            <span class="font-bold text-primary">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-text-muted text-sm">
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
