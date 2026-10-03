<x-layouts.dashboard title="Pengeluaran Operasional Bank Sampah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-text">Pengeluaran Operasional</h2>
                <p class="text-sm text-text-muted mt-1">Catat dan pantau pengeluaran operasional Bank Sampah</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('bank-sampah.expenses.create') }}" class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-radius-md text-sm font-semibold hover:bg-primary-hover shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Catat Pengeluaran
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-primary-light border border-primary/30 text-primary rounded-radius-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Metrics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-surface rounded-radius-lg p-5 border border-border shadow-sm flex flex-col justify-between">
                <p class="text-xs font-semibold text-text-muted uppercase tracking-wide">Saldo Kas Bank Sampah</p>
                <p class="text-2xl font-bold text-text mt-2">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
            </div>
            <div class="bg-primary-light rounded-radius-lg p-5 border border-primary/30 dark:border-primary/30/50 shadow-sm flex flex-col justify-between">
                <p class="text-xs font-semibold text-primary uppercase tracking-wide">Pemasukan (Bulan Ini)</p>
                <p class="text-2xl font-bold text-text mt-2">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
            </div>
            <div class="bg-danger-light rounded-radius-lg p-5 border border-danger/30 dark:border-danger/30/50 shadow-sm flex flex-col justify-between">
                <p class="text-xs font-semibold text-danger uppercase tracking-wide">Pengeluaran (Bulan Ini)</p>
                <p class="text-2xl font-bold text-danger-text mt-2">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
            </div>
            <div class="bg-info-light rounded-radius-lg p-5 border border-info/30 dark:border-info/30 shadow-sm flex flex-col justify-between">
                <p class="text-xs font-semibold text-info-text uppercase tracking-wide">Net Flow (Bulan Ini)</p>
                <p class="text-2xl font-bold text-info-text mt-2">Rp {{ number_format($totalIn - $totalOut, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden">
            <div class="p-4 border-b border-border bg-surface-muted/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <form method="GET" action="{{ route('bank-sampah.expenses.index') }}" class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <input type="date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}" class="rounded-radius-md border-border bg-surface text-sm shadow-sm focus:ring-primary focus:border-primary">
                        <span class="text-text-muted">-</span>
                        <input type="date" name="end_date" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}" class="rounded-radius-md border-border bg-surface text-sm shadow-sm focus:ring-primary focus:border-primary">
                    </div>
                    <button type="submit" class="bg-surface text-text border border-border px-4 py-2 rounded-radius-md text-sm font-semibold hover:bg-surface-muted transition-subtle">Filter</button>
                    @if(request('start_date'))
                        <a href="{{ route('bank-sampah.expenses.index') }}" class="text-sm text-danger font-medium hover:underline">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-surface">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Tanggal & Kode</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Keterangan</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-text-muted uppercase tracking-wider">Pencatat</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">Nominal</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-text-muted uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-surface-muted transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-text">{{ $expense->expense_date->format('d M Y') }}</div>
                                    <div class="text-xs font-mono text-text-muted mt-0.5">{{ $expense->expense_code }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-text line-clamp-2">{{ $expense->description }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-surface-muted flex items-center justify-center text-xs font-bold text-text-muted">
                                            {{ substr($expense->recordedBy->name, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-medium text-text">{{ $expense->recordedBy->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-danger">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('bank-sampah.expenses.show', $expense) }}" class="text-primary hover:underline transition-subtle">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-surface-muted rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <p class="text-text-muted text-sm">Belum ada catatan pengeluaran pada rentang tanggal ini.</p>
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
