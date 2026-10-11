<x-layouts.dashboard title="Riwayat Tabungan">
    @if(!$hasAccount)
        <div class="min-h-[60vh] flex flex-col items-center justify-center p-6 text-center">
            <div class="w-24 h-24 bg-danger-light rounded-full flex items-center justify-center mb-6 ring-8 ring-danger/20">
                <svg class="w-12 h-12 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-3xl font-extrabold text-text mb-4">Akun Belum Terhubung</h2>
            <p class="text-lg text-text-muted max-w-lg mx-auto mb-10 leading-relaxed">
                Akun Anda belum dikaitkan dengan data nasabah Bank Sampah. Mohon hubungi administrator untuk menghubungkan akun agar dapat melihat riwayat tabungan Anda.
            </p>
            <a href="{{ route('warga.dashboard') }}" class="inline-flex items-center justify-center bg-surface text-text border border-border px-8 py-4 rounded-radius-lg text-base font-bold hover:bg-surface-muted transition shadow-subtle">
                Kembali ke Beranda
            </a>
        </div>
    @else
        <div class="space-y-8 mx-auto">
            {{-- Header --}}
            <div>
                <h2 class="text-3xl font-extrabold text-text">Riwayat Tabungan</h2>
                <p class="text-base text-text-muted mt-2">Daftar lengkap setoran dan penarikan tabungan bank sampah Anda.</p>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-primary rounded-radius-lg p-8 shadow-lg text-white">
                    <p class="text-sm font-bold text-white/80 uppercase tracking-wider mb-2">Saldo Saat Ini</p>
                    <p class="text-5xl font-extrabold">Rp {{ number_format($balance ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="bg-surface-muted rounded-radius-lg p-8 border border-border">
                    <p class="text-sm font-bold text-text-muted uppercase tracking-wider mb-2">Total Transaksi</p>
                    <p class="text-4xl font-extrabold text-text">{{ $totalTransactions ?? 0 }} <span class="text-xl font-normal text-text-muted">Kali</span></p>
                </div>
            </div>

            {{-- Filter Toolbar --}}
            <div class="bg-surface rounded-radius-lg shadow-sm border border-border p-8">
                <form method="GET" action="{{ route('warga.savings.history') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
                    <div>
                        <label class="block text-sm font-bold text-text mb-2">Jenis Transaksi</label>
                        <select name="type" class="w-full rounded-radius-lg border-border bg-surface-muted text-text h-14 px-4 text-base font-medium focus:ring-primary">
                            <option value="">Semua</option>
                            <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Setoran</option>
                            <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Penarikan</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-text mb-2">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-radius-lg border-border bg-surface-muted text-text h-14 px-4 text-base font-medium focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-text mb-2">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-radius-lg border-border bg-surface-muted text-text h-14 px-4 text-base font-medium focus:ring-primary">
                    </div>

                    <button type="submit" class="w-full h-14 bg-primary text-white rounded-radius-lg text-base font-bold hover:bg-primary dark:hover:bg-primary transition shadow-sm">
                        Tampilkan Riwayat
                    </button>
                </form>
            </div>

            {{-- Transactions List --}}
            <div id="table-section" class="space-y-4 scroll-mt-20">
                @forelse($ledgers as $l)
                    <div class="bg-surface rounded-radius-lg p-6 border border-border shadow-sm flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-radius-lg flex items-center justify-center shrink-0 {{ $l->type === 'credit' ? 'bg-primary-light text-primary' : 'bg-danger-light text-danger-text' }}">
                                <span class="text-xs font-bold uppercase">{{ $l->type === 'credit' ? 'Masuk' : 'Keluar' }}</span>
                            </div>
                            <div>
                                <p class="text-base font-bold text-text">{{ $l->description }}</p>
                                <p class="text-sm text-text-muted mt-1">{{ $l->created_at->format('d/m/Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-extrabold {{ $l->type === 'credit' ? 'text-primary' : 'text-danger-text' }}">
                                {{ $l->type === 'credit' ? '+' : '-' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="bg-surface-muted/40 p-12 rounded-radius-lg text-center border-2 border-dashed border-border">
                        <p class="text-lg font-bold text-text-muted">Belum ada riwayat tabungan pada periode ini.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($ledgers->hasPages())
                <div class="pt-4">
                    {{ $ledgers->links() }}
                </div>
            @endif
        </div>
    @endif
</x-layouts.dashboard>