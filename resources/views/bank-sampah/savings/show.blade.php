<x-layouts.dashboard title="Buku Tabungan - {{ $customer->name }}">
    <div class="space-y-6 max-w-5xl mx-auto pb-12">
        {{-- Header & Action Bar --}}
        <div class="space-y-4 border-b border-border-subtle pb-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-caption text-text-muted">
                        <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                        <span>&rarr;</span>
                        <a href="{{ route('bank-sampah.savings') }}" class="hover:text-text transition-subtle">Saldo Nasabah</a>
                        <span>&rarr;</span>
                        <span class="text-text font-bold">{{ $customer->name }}</span>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap pt-0.5">
                        <h1 class="text-heading-lg sm:text-heading-xl font-bold text-text tracking-tight">Buku Tabungan Nasabah</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-radius-sm text-caption font-bold bg-surface-muted text-text border border-border">
                            {{ $customer->rt_number }}
                        </span>
                        <span class="font-mono text-body-sm font-semibold text-text-muted">{{ $customer->customer_code }}</span>
                    </div>
                    <p class="text-body-sm text-text-muted">
                        Riwayat mutasi saldo tabungan secara vertikal dan kronologis untuk nasabah <strong class="text-text">{{ $customer->name }}</strong>.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 self-start sm:self-center shrink-0 flex-wrap">
                    <a href="{{ route('bank-sampah.savings') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-radius-md border border-border bg-surface hover:bg-surface-muted text-text text-body-sm font-semibold shadow-subtle transition-subtle min-h-[44px]">
                        &larr; Kembali ke Saldo Nasabah
                    </a>
                    @can('manage_deposits')
                        <a href="{{ route('bank-sampah.deposits.create', ['customer_id' => $customer->id]) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-radius-md bg-primary hover:bg-primary-hover text-white text-body-sm font-semibold shadow-subtle transition-subtle min-h-[44px]">
                            + Catat Setoran
                        </a>
                    @endcan
                    @can('manage_withdrawals')
                        <a href="{{ route('bank-sampah.withdrawals.create', ['customer_id' => $customer->id]) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-radius-md border border-border bg-surface hover:bg-surface-muted text-text text-body-sm font-semibold shadow-subtle transition-subtle min-h-[44px]">
                            - Tarik Saldo
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Summary Cards (4 Metrics) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-surface rounded-radius-lg p-4 border border-border shadow-subtle flex flex-col justify-between">
                <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider block">Saldo Saat Ini</span>
                <div class="mt-2">
                    <span class="font-mono text-heading-lg font-bold {{ $currentBalance > 0 ? 'text-primary' : 'text-text-muted' }}">
                        Rp {{ number_format($currentBalance, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="bg-surface rounded-radius-lg p-4 border border-border shadow-subtle flex flex-col justify-between">
                <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider block">Total Setoran (Kredit)</span>
                <div class="mt-2">
                    <span class="font-mono text-heading-lg font-bold text-success">+Rp {{ number_format($totalCredit, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="bg-surface rounded-radius-lg p-4 border border-border shadow-subtle flex flex-col justify-between">
                <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider block">Total Penarikan (Debit)</span>
                <div class="mt-2">
                    <span class="font-mono text-heading-lg font-bold text-danger">-Rp {{ number_format($totalDebit, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="bg-surface rounded-radius-lg p-4 border border-border shadow-subtle flex flex-col justify-between">
                <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider block">Total Transaksi</span>
                <div class="mt-2">
                    <span class="font-mono text-heading-lg font-bold text-text">{{ $totalTransactions }}</span>
                    <span class="text-body-sm text-text-muted font-normal ml-1">catatan</span>
                </div>
            </div>
        </div>

        {{-- Filter Toolbar --}}
        <div class="p-4 rounded-radius-lg border border-border bg-surface shadow-subtle">
            <form method="GET" action="{{ route('bank-sampah.savings.show', $customer) }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3" id="ledger-filter-form">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-caption font-bold text-text-muted uppercase">Jenis:</span>
                    <div class="inline-flex rounded-radius-md border border-border p-0.5 bg-surface-muted">
                        <a href="{{ route('bank-sampah.savings.show', array_merge(request()->query(), ['customer' => $customer, 'type' => ''])) }}"
                           class="px-2.5 py-1 text-caption font-semibold rounded-radius-sm transition-subtle {{ empty($typeFilter) ? 'bg-surface text-text shadow-subtle font-bold' : 'text-text-muted hover:text-text' }}">
                            Semua
                        </a>
                        <a href="{{ route('bank-sampah.savings.show', array_merge(request()->query(), ['customer' => $customer, 'type' => 'credit'])) }}"
                           class="px-2.5 py-1 text-caption font-semibold rounded-radius-sm transition-subtle {{ $typeFilter === 'credit' ? 'bg-surface text-success shadow-subtle font-bold' : 'text-text-muted hover:text-text' }}">
                            Setoran (+)
                        </a>
                        <a href="{{ route('bank-sampah.savings.show', array_merge(request()->query(), ['customer' => $customer, 'type' => 'debit'])) }}"
                           class="px-2.5 py-1 text-caption font-semibold rounded-radius-sm transition-subtle {{ $typeFilter === 'debit' ? 'bg-surface text-danger shadow-subtle font-bold' : 'text-text-muted hover:text-text' }}">
                            Penarikan (-)
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-caption font-bold text-text-muted uppercase">Tanggal:</span>
                    <input type="date"
                           name="date_from"
                           value="{{ $dateFrom }}"
                           onchange="document.getElementById('ledger-filter-form').submit()"
                           class="text-body-sm rounded-radius-md border border-border bg-surface text-text py-1 px-2.5 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle"
                           aria-label="Dari tanggal">
                    <span class="text-caption text-text-muted">&ndash;</span>
                    <input type="date"
                           name="date_to"
                           value="{{ $dateTo }}"
                           onchange="document.getElementById('ledger-filter-form').submit()"
                           class="text-body-sm rounded-radius-md border border-border bg-surface text-text py-1 px-2.5 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle"
                           aria-label="Sampai tanggal">

                    @if($typeFilter || $dateFrom || $dateTo)
                        <a href="{{ route('bank-sampah.savings.show', $customer) }}" class="text-body-sm font-semibold text-text-muted hover:text-danger px-1 transition-subtle">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Digital Ledger / Passbook Container (Vertical Timeline) --}}
        <div class="bg-surface rounded-radius-lg shadow-subtle border border-border overflow-hidden">
            <div class="p-4 sm:px-5 sm:py-4 border-b border-border-subtle bg-surface flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    <h2 class="text-heading-sm font-bold text-text">Riwayat Transaksi Tabungan</h2>
                </div>
                <span class="text-caption font-mono text-text-muted">
                    {{ $history->count() }} transaksi
                </span>
            </div>

            @if($history->isNotEmpty())
                {{-- Desktop Passbook Table View --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted/40 text-body-sm font-bold text-text-muted uppercase tracking-wider">
                                <th scope="col" class="py-3 px-5">Tanggal</th>
                                <th scope="col" class="py-3 px-5">Jenis Transaksi</th>
                                <th scope="col" class="py-3 px-5 text-right">Nominal Masuk / Keluar</th>
                                <th scope="col" class="py-3 px-5 text-right font-bold text-text">Saldo Setelah Transaksi</th>
                                <th scope="col" class="py-3 px-5">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle font-mono">
                            @foreach($history as $item)
                                <tr class="hover:bg-surface-muted/40 transition-subtle">
                                    {{-- Tanggal --}}
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        <div class="font-bold text-body text-text">
                                            {{ $item->transaction_date->translatedFormat('d F Y') }}
                                        </div>
                                        <span class="text-caption text-text-muted font-sans font-normal">
                                            {{ $item->transaction_date->translatedFormat('l') }}
                                        </span>
                                    </td>

                                    {{-- Jenis Transaksi --}}
                                    <td class="py-3.5 px-5 whitespace-nowrap font-sans">
                                        @if($item->type === 'credit')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-success-light text-success-text border border-success/30 text-caption font-bold">
                                                ✓ Setoran
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-radius-sm bg-danger-light text-danger-text border border-danger/30 text-caption font-bold">
                                                ↗ Penarikan
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Nominal Masuk / Keluar --}}
                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <span class="text-body font-bold {{ $item->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                            {{ $item->type === 'credit' ? '+' : '-' }} Rp {{ number_format($item->amount, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    {{-- Saldo Setelah Transaksi --}}
                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center px-2.5 py-1 rounded-radius-sm bg-surface-muted border border-border">
                                            <span class="text-body font-bold text-text">
                                                Rp {{ number_format($item->running_balance, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Keterangan --}}
                                    <td class="py-3.5 px-5 font-sans">
                                        <div class="text-body-sm text-text max-w-sm">
                                            {{ $item->transaction_notes ?: '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Passbook View (< md): Stacked Vertical Format matching spreadsheet logic --}}
                <div class="block md:hidden divide-y divide-border-subtle p-3 space-y-3">
                    @foreach($history as $item)
                        <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-2.5">
                            {{-- Tanggal Header --}}
                            <div class="flex items-center justify-between pb-1.5 border-b border-border-subtle">
                                <span class="text-body font-bold text-text">
                                    {{ $item->transaction_date->translatedFormat('d F Y') }}
                                </span>
                                @if($item->type === 'credit')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm bg-success-light text-success-text text-caption font-bold border border-success/30">
                                        Setoran
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm bg-danger-light text-danger-text text-caption font-bold border border-danger/30">
                                        Penarikan
                                    </span>
                                @endif
                            </div>

                            {{-- Nominal Masuk / Keluar --}}
                            <div class="flex items-center justify-between text-caption">
                                <span class="text-text-muted uppercase font-bold">
                                    {{ $item->type === 'credit' ? 'Setoran' : 'Penarikan' }}
                                </span>
                                <span class="font-mono text-body font-bold {{ $item->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ $item->type === 'credit' ? '+' : '-' }} Rp {{ number_format($item->amount, 0, ',', '.') }}
                                </span>
                            </div>

                            {{-- Saldo Setelah Transaksi --}}
                            <div class="flex items-center justify-between text-caption pt-1 border-t border-border-subtle">
                                <span class="text-text-muted uppercase font-bold">Saldo</span>
                                <span class="font-mono text-body font-bold text-text">
                                    Rp {{ number_format($item->running_balance, 0, ',', '.') }}
                                </span>
                            </div>

                            {{-- Keterangan --}}
                            @if($item->transaction_notes)
                                <div class="text-caption text-text-muted pt-1 border-t border-border-subtle">
                                    <span class="font-bold text-text uppercase block text-caption">Keterangan:</span>
                                    <span class="italic text-body-sm">{{ $item->transaction_notes }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center flex flex-col items-center justify-center space-y-2">
                    <div class="w-12 h-12 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <p class="text-heading-sm font-bold text-text">Belum ada riwayat transaksi tabungan untuk nasabah ini.</p>
                    <p class="text-caption text-text-muted">Gunakan tombol Catat Setoran untuk mulai membukukan tabungan sampah warga.</p>
                    @can('manage_deposits')
                        <div class="pt-2">
                            <a href="{{ route('bank-sampah.deposits.create', ['customer_id' => $customer->id]) }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-radius-md bg-primary text-white text-caption font-semibold hover:bg-primary-hover shadow-subtle transition-subtle">
                                + Catat Setoran Baru
                            </a>
                        </div>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
