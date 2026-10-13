<x-layouts.dashboard title="Saldo Nasabah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header --}}
        <x-ui.page-header title="Saldo Nasabah" description="Daftar saldo dan pembukuan buku tabungan setiap nasabah Bank Sampah">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Saldo Nasabah</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <a href="{{ route('bank-sampah.savings.export') }}" class="inline-flex items-center gap-2 bg-surface border border-border text-text px-4 py-2.5 rounded-radius-md text-body-sm font-semibold hover:bg-surface-muted shadow-subtle transition-subtle min-h-[44px]">
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Export CSV
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Summary Cards (4 Metrics) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-surface rounded-radius-lg p-4 border border-border shadow-subtle flex flex-col justify-between">
                <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider block">Total Nasabah</span>
                <div class="mt-2">
                    <span class="font-mono text-heading-lg font-bold text-text">{{ $totalCustomers }}</span>
                    <span class="text-body-sm text-text-muted font-normal ml-1">orang</span>
                </div>
            </div>

            <div class="bg-surface rounded-radius-lg p-4 border border-border shadow-subtle flex flex-col justify-between">
                <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider block">Total Saldo Simpanan</span>
                <div class="mt-2">
                    <span class="font-mono text-heading-lg font-bold text-primary">Rp {{ number_format($totalBalanceAll, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="bg-surface rounded-radius-lg p-4 border border-border shadow-subtle flex flex-col justify-between">
                <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider block">Total Setoran (Kredit)</span>
                <div class="mt-2">
                    <span class="font-mono text-heading-lg font-bold text-success">+Rp {{ number_format($totalCreditAll, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="bg-surface rounded-radius-lg p-4 border border-border shadow-subtle flex flex-col justify-between">
                <span class="text-body-sm font-bold text-text-muted uppercase tracking-wider block">Total Penarikan (Debit)</span>
                <div class="mt-2">
                    <span class="font-mono text-heading-lg font-bold text-danger">-Rp {{ number_format($totalDebitAll, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div id="table-section" class="bg-surface rounded-radius-lg shadow-subtle border border-border overflow-hidden transition-all duration-300">
            {{-- Toolbar: Search & Filter RT --}}
            <div class="p-4 sm:p-5 border-b border-border-subtle bg-surface flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <form method="GET" action="{{ route('bank-sampah.savings') }}" data-live-search class="flex flex-col sm:flex-row sm:items-center gap-3 w-full" id="savings-filter-form">
                    {{-- Search Input (Nama / No. RT / Kode) --}}
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input type="text"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Cari nama nasabah atau No. RT..."
                               class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text pl-9 pr-3 py-2 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                    </div>

                    {{-- RT Filter Dropdown --}}
                    @if(!empty($rtOptions) && count($rtOptions) > 1)
                        <div class="w-full sm:w-44">
                            <select name="rt"
                                    class="w-full text-body-sm rounded-radius-md border border-border bg-surface text-text py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                                <option value="">Semua No. RT</option>
                                @foreach($rtOptions as $opt)
                                    <option value="{{ $opt }}" {{ $rtFilter === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-body-sm font-semibold rounded-radius-md shadow-subtle transition-subtle cursor-pointer">
                            Cari
                        </button>
                        @if($search || $rtFilter)
                            <a href="{{ route('bank-sampah.savings') }}" class="text-body font-semibold text-text-muted hover:text-danger px-2 py-2 transition-subtle">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Desktop Table View --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border bg-surface-muted/40 text-body-sm font-bold text-text-muted uppercase tracking-wider">
                            <th scope="col" class="py-3 px-4 w-12 text-center">No</th>
                            <th scope="col" class="py-3 px-4">Nama Nasabah</th>
                            <th scope="col" class="py-3 px-4">No. RT</th>
                            <th scope="col" class="py-3 px-4">Aktivitas / Transaksi Terakhir</th>
                            <th scope="col" class="py-3 px-4 text-right">Saldo Saat Ini</th>
                            <th scope="col" class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($customers as $index => $c)
                            @php
                                $bal = $c->balance;
                                $latest = $c->latestSavingsLedger;
                                $latestDate = $latest?->reference?->date ? \Carbon\Carbon::parse($latest->reference->date) : $latest?->created_at;
                            @endphp
                            <tr class="hover:bg-surface-muted/50 transition-subtle">
                                <td class="py-3.5 px-4 text-center font-mono text-body-sm text-text-muted">
                                    {{ $customers->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-caption shrink-0">
                                            {{ strtoupper(substr($c->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="text-body font-bold text-text block leading-tight">{{ $c->name }}</span>
                                            <span class="font-mono text-body-sm text-text-muted font-semibold block mt-0.5">{{ $c->customer_code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm text-caption font-bold bg-surface-muted text-text border border-border">
                                        {{ $c->rt_number }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($latest)
                                        <div class="text-body">
                                            <span class="font-semibold text-text">
                                                {{ $latestDate->format('d/m/Y') }}
                                            </span>
                                            <span class="text-caption text-text-muted">&bull;</span>
                                            @if($latest->type === 'credit')
                                                <span class="text-success font-semibold font-mono text-body-sm">Setoran +Rp {{ number_format($latest->amount, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-danger font-semibold font-mono text-body-sm">Penarikan -Rp {{ number_format($latest->amount, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-caption text-text-muted italic">Belum ada transaksi</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <span class="font-mono text-body font-bold {{ $bal > 0 ? 'text-primary' : 'text-text-muted' }}">
                                        Rp {{ number_format($bal, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('bank-sampah.savings.show', $c) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-radius-md bg-surface border border-border hover:bg-surface-muted text-primary font-bold text-body-sm shadow-subtle transition-subtle">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="w-12 h-12 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <p class="text-heading-sm font-bold text-text">Belum ada data saldo nasabah yang cocok.</p>
                                        <p class="text-caption text-text-muted">Coba bersihkan filter pencarian atau pastikan nasabah sudah terdaftar.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Adaptive View (< md) --}}
            <div class="block md:hidden divide-y divide-border-subtle">
                @forelse($customers as $c)
                    @php
                        $bal = $c->balance;
                        $latest = $c->latestSavingsLedger;
                        $latestDate = $latest?->reference?->date ? \Carbon\Carbon::parse($latest->reference->date) : $latest?->created_at;
                    @endphp
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-caption shrink-0">
                                    {{ strtoupper(substr($c->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h4 class="text-body font-bold text-text leading-tight">{{ $c->name }}</h4>
                                    <span class="text-body-sm font-mono font-semibold text-text-muted">{{ $c->customer_code }}</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-radius-sm text-caption font-bold bg-surface-muted text-text border border-border">
                                {{ $c->rt_number }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-1 border-t border-border-subtle text-caption">
                            <div>
                                <span class="text-text-muted font-bold uppercase block text-caption">Transaksi Terakhir:</span>
                                @if($latest)
                                    <span class="font-semibold text-text text-body">{{ $latestDate->format('d/m/Y') }}</span>
                                    <span class="{{ $latest->type === 'credit' ? 'text-success' : 'text-danger' }} font-bold font-mono text-body-sm">
                                        {{ $latest->type === 'credit' ? '+' : '-' }}Rp {{ number_format($latest->amount, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-text-muted italic">-</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="text-text-muted font-bold uppercase block text-caption">Saldo Saat Ini:</span>
                                <span class="font-mono text-body font-bold {{ $bal > 0 ? 'text-primary' : 'text-text-muted' }}">
                                    Rp {{ number_format($bal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <a href="{{ route('bank-sampah.savings.show', $c) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-radius-md bg-surface border border-border text-primary font-bold text-body-sm hover:bg-surface-muted shadow-subtle transition-subtle">
                                Detail Riwayat &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-text-muted text-body-sm">
                        Belum ada data saldo nasabah.
                    </div>
                @endforelse
            </div>

            {{-- Pagination Footer --}}
            @if($customers->hasPages())
                <div class="p-4 border-t border-border-subtle bg-surface-muted/30">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
