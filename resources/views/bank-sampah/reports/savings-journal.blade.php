<x-layouts.dashboard title="Jurnal Tabungan Nasabah">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-text">Jurnal Tabungan Nasabah</h2>
                <p class="text-sm text-text-muted mt-1">Pantau seluruh catatan mutasi setoran dan penarikan tabungan nasabah bank sampah</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('bank-sampah.reports.savings-journal.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-surface border border-border text-text px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-surface-muted shadow-sm transition">
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak / Simpan PDF
                </a>
                <a href="{{ route('bank-sampah.reports.savings-journal.excel', request()->query()) }}" class="inline-flex items-center gap-1.5 bg-primary text-white px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-primary-hover shadow-sm hover:shadow transition">
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-xs text-primary font-semibold uppercase tracking-wide">Total Setoran</p>
                <p class="text-2xl font-bold text-primary mt-1">Rp {{ number_format($totalSetor, 0, ',', '.') }}</p>
            </div>
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-xs text-danger font-semibold uppercase tracking-wide">Total Penarikan</p>
                <p class="text-2xl font-bold text-danger mt-1">Rp {{ number_format($totalTarik, 0, ',', '.') }}</p>
            </div>
            <div class="bg-surface-muted/50 rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-xs text-text-muted font-semibold uppercase tracking-wide">Saldo Tabungan Nasabah</p>
                <p class="text-2xl font-bold text-text mt-1">
                    @if($customerId)
                        Rp {{ number_format($totalSaldo, 0, ',', '.') }}
                    @else
                        <span class="text-xs font-normal text-text-muted">Pilih Nasabah untuk melihat Saldo</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Sticky Filter Bar -->
        <div class="bg-surface rounded-radius-lg shadow-sm border border-border p-4 transition-colors duration-300">
            <form action="{{ route('bank-sampah.reports.savings-journal') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
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
                    <label class="block text-xs font-semibold text-text-muted uppercase tracking-wider mb-1">Tipe Transaksi</label>
                    <select name="type" class="block w-full rounded-radius-md border-border bg-surface text-sm shadow-sm focus:ring-primary focus:border-primary">
                        <option value="">Semua</option>
                        <option value="credit" {{ $type === 'credit' ? 'selected' : '' }}>Setoran (Credit)</option>
                        <option value="debit" {{ $type === 'debit' ? 'selected' : '' }}>Penarikan (Debit)</option>
                    </select>
                </div>
                <div class="sm:col-span-2 md:col-span-1 flex items-end">
                    <button type="submit" class="w-full bg-primary text-white font-semibold text-sm px-4 py-2.5 rounded-radius-md hover:bg-primary transition">
                        Filter Jurnal
                    </button>
                </div>
            </form>
        </div>

        @if($customerId && isset($pageOpeningBalance))
            <div class="bg-surface-muted/40 p-4 border border-border rounded-radius-lg flex justify-between items-center text-sm">
                <span class="text-text-muted font-medium">Saldo Awal Halaman Ini (Carry Forward):</span>
                <span class="font-bold text-text">Rp {{ number_format($pageOpeningBalance, 0, ',', '.') }}</span>
            </div>
        @endif

        <!-- Data List -->
        <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden">
            <!-- DESKTOP VIEW (Table) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface-muted/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase">Nasabah</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase">Tipe</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase">Deskripsi</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase">Nominal</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($ledgers as $l)
                            <tr class="hover:bg-surface-muted transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-text-muted">
                                    {{ $l->created_at ? $l->created_at->toDateString() : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-text">
                                    @if($l->wasteCustomer)
                                        {{ $l->wasteCustomer->name }}
                                        <span class="block text-caption text-text-muted font-normal mt-0.5">Nasabah Bank Sampah</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($l->type === 'credit')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-xs font-medium bg-primary-light text-primary">
                                            Setoran
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-xs font-medium bg-danger-light text-danger-text">
                                            Penarikan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-text-muted max-w-xs truncate">
                                    {{ $l->description ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold {{ $l->type === 'credit' ? 'text-primary' : 'text-danger' }}">
                                    {{ $l->type === 'credit' ? '+' : '-' }} Rp {{ number_format($l->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-text">
                                    @if(isset($l->running_balance))
                                        Rp {{ number_format($l->running_balance, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-text-muted text-sm">
                                    Tidak ada data mutasi tabungan nasabah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MOBILE VIEW (Card List Collapse) -->
            <div class="block sm:hidden divide-y divide-border-subtle bg-surface">
                @forelse($ledgers as $l)
                    <div class="p-4 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-bold text-text">
                                    @if($l->wasteCustomer)
                                        {{ $l->wasteCustomer->name }}
                                    @else
                                        -
                                    @endif
                                </h4>
                                <span class="text-caption text-text-muted">{{ $l->type === 'credit' ? 'Setoran (Credit)' : 'Penarikan (Debit)' }}</span>
                            </div>
                            <span class="text-xs text-text-muted">{{ $l->created_at ? $l->created_at->toDateString() : '-' }}</span>
                        </div>
                        <div class="border-t border-dashed border-border-subtle my-2"></div>
                        <div class="flex justify-between text-xs">
                            <span class="text-text-muted">Keterangan:</span>
                            <span class="text-text">{{ $l->description ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between text-xs pt-1">
                            <span class="text-text-muted font-semibold">Nominal:</span>
                            <span class="font-bold {{ $l->type === 'credit' ? 'text-primary' : 'text-danger' }}">
                                {{ $l->type === 'credit' ? '+' : '-' }} Rp {{ number_format($l->amount, 0, ',', '.') }}
                            </span>
                        </div>
                        @if(isset($l->running_balance))
                            <div class="flex justify-between text-xs pt-1">
                                <span class="text-text-muted font-semibold">Saldo Akhir:</span>
                                <span class="font-bold text-text">Rp {{ number_format($l->running_balance, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center text-text-muted text-sm">
                        Tidak ada data mutasi tabungan nasabah.
                    </div>
                @endforelse
            </div>

            @if($ledgers->hasPages())
                <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $ledgers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
