<x-layouts.dashboard title="Laporan Arus Kas Bank Sampah">
    <div class="space-y-6">
        <!-- Header -->
        <x-ui.page-header title="Laporan Arus Kas" description="Pantau performa finansial operasional kas Bank Sampah (Penjualan vs Pengeluaran Operasional)">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Laporan Arus Kas</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <a href="{{ route('bank-sampah.reports.cashflow.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-surface border border-border text-text px-3.5 py-2 rounded-radius-md text-body-sm font-semibold hover:bg-surface-muted shadow-subtle transition-subtle min-h-[44px]">
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak / Simpan PDF
                </a>
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.reports.cashflow.excel', request()->query()) }}'">
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-body-sm font-bold text-primary uppercase tracking-wide">Total Pemasukan (Penjualan)</p>
                <p class="font-mono text-heading-lg font-bold text-primary mt-1">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</p>
            </div>
            <div class="bg-surface rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-body-sm font-bold text-danger uppercase tracking-wide">Total Pengeluaran Operasional</p>
                <p class="font-mono text-heading-lg font-bold text-danger mt-1">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
            </div>
            <div class="bg-surface-muted/50 rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-sm">
                <p class="text-body-sm font-bold text-text-muted uppercase tracking-wide">Net Flow Keuangan</p>
                <p class="font-mono text-heading-lg font-bold {{ $saldoAkhir >= 0 ? 'text-text' : 'text-danger' }} mt-1">
                    Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Sticky Filter Bar -->
        <div class="bg-surface rounded-radius-lg shadow-sm border border-border p-4 transition-colors duration-300">
            <form action="{{ route('bank-sampah.reports.cashflow') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-body-sm font-semibold text-text-muted uppercase tracking-wider mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="block w-full rounded-radius-md border-border bg-surface text-body-sm shadow-sm focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-body-sm font-semibold text-text-muted uppercase tracking-wider mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="block w-full rounded-radius-md border-border bg-surface text-body-sm shadow-sm focus:ring-primary focus:border-primary">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-primary text-white font-semibold text-body-sm px-4 py-2.5 rounded-radius-md hover:bg-primary transition">
                        Filter Laporan
                    </button>
                </div>
            </form>
        </div>

        <!-- Monthly Summary Breakdown -->
        <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden p-6 space-y-4">
            <h3 class="text-heading-sm font-bold text-text uppercase tracking-wider">Ikhtisar Bulanan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($months as $monthKey => $m)
                    <div class="bg-surface-muted/50 rounded-radius-lg p-4 border border-border flex flex-col justify-between shadow-xs transition hover:shadow-md">
                        <div>
                            <span class="text-caption font-bold text-text-muted">{{ $m['name'] }}</span>
                            <div class="border-t border-border my-2"></div>
                            <div class="flex justify-between text-caption my-1">
                                <span class="text-text-muted">Pemasukan:</span>
                                <span class="font-bold text-primary">Rp {{ number_format($m['pemasukan'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-caption my-1">
                                <span class="text-text-muted">Pengeluaran:</span>
                                <span class="font-bold text-danger font-medium">Rp {{ number_format($m['pengeluaran'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="border-t border-dashed border-border my-2"></div>
                        <div class="flex justify-between text-caption">
                            <span class="font-semibold text-text">Net Flow:</span>
                            <span class="font-bold {{ $m['net'] >= 0 ? 'text-text' : 'text-danger' }}">
                                Rp {{ number_format($m['net'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Ledger/Cashbook -->
        <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
                <h3 class="text-heading-sm font-bold text-text uppercase tracking-wider">Buku Kas Operasional</h3>
            </div>
            
            <!-- DESKTOP VIEW -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface-muted/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-caption font-semibold text-text-muted uppercase">Tanggal</th>
                            <th class="px-6 py-4 text-left text-caption font-semibold text-text-muted uppercase">Kode</th>
                            <th class="px-6 py-4 text-left text-caption font-semibold text-text-muted uppercase">Tipe</th>
                            <th class="px-6 py-4 text-left text-caption font-semibold text-text-muted uppercase">Deskripsi</th>
                            <th class="px-6 py-4 text-right text-caption font-semibold text-text-muted uppercase">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($cashbook as $item)
                            <tr class="hover:bg-surface-muted transition">
                                <td class="px-6 py-4 whitespace-nowrap text-body-sm text-text-muted">
                                    {{ $item['date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-body-sm font-semibold text-text">
                                    {{ $item['code'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-body-sm">
                                    @if($item['is_in'])
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-caption font-medium bg-primary-light text-primary">
                                            Pemasukan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-radius-sm text-caption font-medium bg-danger-light text-danger-text">
                                            Pengeluaran
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-body-sm text-text-muted max-w-sm truncate">
                                    {{ $item['description'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-body-sm font-semibold {{ $item['is_in'] ? 'text-primary' : 'text-danger' }}">
                                    {{ $item['is_in'] ? '+' : '-' }} Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-text-muted text-body-sm">
                                    Tidak ada data kas operasional dalam periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MOBILE VIEW -->
            <div class="block sm:hidden divide-y divide-border-subtle bg-surface">
                @forelse($cashbook as $item)
                    <div class="p-4 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-body-sm font-semibold text-text-muted font-mono">{{ $item['code'] }}</span>
                                <h4 class="text-body font-bold text-text mt-0.5">
                                    {{ $item['description'] }}
                                </h4>
                            </div>
                            <span class="text-caption text-text-muted">{{ $item['date'] }}</span>
                        </div>
                        <div class="border-t border-dashed border-border-subtle my-2"></div>
                        <div class="flex justify-between text-body-sm">
                            <span class="text-text-muted">Tipe:</span>
                            <span class="font-semibold {{ $item['is_in'] ? 'text-primary' : 'text-danger' }}">
                                {{ $item['type'] }}
                            </span>
                        </div>
                        <div class="flex justify-between text-body-sm pt-1">
                            <span class="text-text-muted font-semibold">Nominal:</span>
                            <span class="font-mono font-bold {{ $item['is_in'] ? 'text-primary' : 'text-danger' }}">
                                {{ $item['is_in'] ? '+' : '-' }} Rp {{ number_format($item['amount'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-text-muted text-body-sm">
                        Tidak ada data kas operasional dalam periode ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.dashboard>
