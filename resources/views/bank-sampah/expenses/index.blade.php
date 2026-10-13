<x-layouts.dashboard title="Pengeluaran Operasional Bank Sampah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header --}}
        <x-ui.page-header title="Pengeluaran Operasional" description="Catat dan pantau pengeluaran operasional Bank Sampah">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Pengeluaran Operasional</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.expenses.create') }}'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Catat Pengeluaran
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @if(session('success'))
            <div class="p-4 bg-primary-light border border-primary/30 text-primary rounded-radius-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Metrics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-surface rounded-radius-lg p-5 border border-border shadow-subtle flex flex-col justify-between">
                <p class="text-body-sm font-bold text-text-muted uppercase tracking-wide">Saldo Kas Bank Sampah</p>
                <p class="font-mono text-heading-lg font-bold text-text mt-2">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
            </div>
            <div class="bg-primary-light rounded-radius-lg p-5 border border-primary/30 dark:border-primary/30/50 shadow-subtle flex flex-col justify-between">
                <p class="text-body-sm font-bold text-primary uppercase tracking-wide">Pemasukan {{ $hasDateFilter ? '(Periode)' : '(Total Kas)' }}</p>
                <p class="font-mono text-heading-lg font-bold text-text mt-2">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
            </div>
            <div class="bg-danger-light rounded-radius-lg p-5 border border-danger/30 dark:border-danger/30/50 shadow-subtle flex flex-col justify-between">
                <p class="text-body-sm font-bold text-danger uppercase tracking-wide">Pengeluaran {{ $hasDateFilter ? '(Periode)' : '(Total Kas)' }}</p>
                <p class="font-mono text-heading-lg font-bold text-danger-text mt-2">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
            </div>
            <div class="bg-info-light rounded-radius-lg p-5 border border-info/30 dark:border-info/30 shadow-subtle flex flex-col justify-between">
                <p class="text-body-sm font-bold text-info-text uppercase tracking-wide">Net Flow {{ $hasDateFilter ? '(Periode)' : '(Total Kas)' }}</p>
                <p class="font-mono text-heading-lg font-bold text-info-text mt-2">Rp {{ number_format($totalIn - $totalOut, 0, ',', '.') }}</p>
            </div>
        </div>

        <div id="table-section" class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden scroll-mt-20">
            <div class="p-4 border-b border-border bg-surface-muted/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <form method="GET" action="{{ route('bank-sampah.expenses.index') }}" data-live-search class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <input type="date" name="start_date" value="{{ request('start_date', '') }}" class="rounded-radius-md border-border bg-surface text-body-sm shadow-subtle focus:ring-primary focus:border-primary">
                        <span class="text-text-muted">-</span>
                        <input type="date" name="end_date" value="{{ request('end_date', '') }}" class="rounded-radius-md border-border bg-surface text-body-sm shadow-subtle focus:ring-primary focus:border-primary">
                    </div>
                    <button type="submit" class="bg-surface text-text border border-border px-4 py-2 rounded-radius-md text-body-sm font-semibold hover:bg-surface-muted transition-subtle cursor-pointer">Filter</button>
                    @if(request('start_date') || request('end_date'))
                        <a href="{{ route('bank-sampah.expenses.index') }}" data-live-reset class="text-body-sm text-danger font-semibold hover:underline">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface">
                        <tr class="border-b border-border bg-surface-muted/40 text-body-sm font-bold text-text-muted uppercase tracking-wider">
                            <th scope="col" class="px-6 py-4 text-left">Tanggal & Kode</th>
                            <th scope="col" class="px-6 py-4 text-left">Keterangan</th>
                            <th scope="col" class="px-6 py-4 text-left">Pencatat</th>
                            <th scope="col" class="px-6 py-4 text-right">Nominal</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-surface-muted transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-body font-semibold text-text">{{ $expense->expense_date->format('d M Y') }}</div>
                                    <div class="font-mono text-body-sm text-text-muted font-semibold mt-0.5">{{ $expense->expense_code }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-body text-text line-clamp-2">{{ $expense->description }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-surface-muted flex items-center justify-center text-body-sm font-bold text-text-muted">
                                            {{ substr($expense->recordedBy->name, 0, 1) }}
                                        </div>
                                        <span class="text-body font-medium text-text">{{ $expense->recordedBy->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="font-mono text-body font-extrabold text-danger">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-body font-semibold">
                                    <a href="{{ route('bank-sampah.expenses.show', $expense) }}" class="text-primary hover:underline transition-subtle">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-surface-muted rounded-full flex items-center justify-center mb-4">
                                            <x-ui.icon name="document-text" class="w-8 h-8 text-text-muted" stroke-width="1.5" />
                                        </div>
                                        <p class="text-body-sm text-text-muted">{{ $hasDateFilter ? 'Belum ada catatan pengeluaran pada rentang tanggal ini.' : 'Belum ada catatan pengeluaran operasional.' }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($expenses->hasPages())
                <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
