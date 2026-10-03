<x-layouts.dashboard title="Tabungan Bank Sampah">
    @if(!$hasAccount)
        <div class="min-h-[60vh] flex flex-col items-center justify-center p-6 text-center">
            <div class="w-24 h-24 bg-danger-light rounded-full flex items-center justify-center mb-6 ring-8 ring-danger/20">
                <svg class="w-12 h-12 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-3xl font-extrabold text-text mb-4">Akun Belum Terhubung</h2>
            <p class="text-lg text-text-muted max-w-lg mx-auto mb-10 leading-relaxed">
                Akun Anda belum dikaitkan dengan data nasabah Bank Sampah. Mohon hubungi administrator untuk menghubungkan akun agar dapat melihat tabungan Anda.
            </p>
            <a href="{{ route('warga.dashboard') }}" class="inline-flex items-center justify-center bg-surface text-text border border-border px-8 py-4 rounded-radius-lg text-base font-bold hover:bg-surface-muted transition shadow-subtle">
                Kembali ke Beranda
            </a>
        </div>
    @else
        <div class="space-y-8 mx-auto">
            {{-- Header --}}
            <div>
                <h2 class="text-3xl font-extrabold text-text">Tabungan Bank Sampah</h2>
                <p class="text-base text-text-muted mt-2">Lihat saldo dan riwayat transaksi tabungan Anda dengan mudah.</p>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-primary rounded-radius-lg p-8 shadow-lg text-white md:col-span-3">
                    <p class="text-sm font-bold text-white/80 uppercase tracking-wider mb-2">Saldo Saat Ini</p>
                    <p class="text-5xl font-extrabold">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                </div>

                <div class="bg-primary-light rounded-radius-lg p-8 border border-primary/30">
                    <p class="text-sm font-bold text-primary uppercase tracking-wider mb-2">Total Setoran</p>
                    <p class="text-2xl font-extrabold text-primary">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
                </div>

                <div class="bg-danger-light rounded-radius-lg p-8 border border-danger/30">
                    <p class="text-sm font-bold text-danger-text uppercase tracking-wider mb-2">Total Penarikan</p>
                    <p class="text-2xl font-extrabold text-danger-text">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
                </div>

                <div class="bg-surface-muted rounded-radius-lg p-8 border border-border">
                    <p class="text-sm font-bold text-text-muted uppercase tracking-wider mb-2">Jumlah Transaksi</p>
                    <p class="text-2xl font-extrabold text-text">{{ $totalTransactions ?? 0 }} <span class="text-lg font-normal text-text-muted">Kali</span></p>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden">
                <div class="px-6 py-6 border-b border-border-subtle flex justify-between items-center">
                    <h3 class="text-xl font-bold text-text">Riwayat Transaksi</h3>
                    <a href="{{ route('warga.savings.history') }}" class="text-base font-bold text-primary hover:text-primary-hover transition">Lihat Semua</a>
                </div>
                
                <div class="divide-y divide-border-subtle">
                    @forelse($recentLedgers as $l)
                        <div class="p-6 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-radius-lg flex items-center justify-center shrink-0 {{ $l->type === 'credit' ? 'bg-primary-light text-primary' : 'bg-danger-light text-danger-text' }}">
                                    <span class="text-sm font-bold">{{ $l->type === 'credit' ? 'Masuk' : 'Keluar' }}</span>
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
                        <div class="p-12 text-center text-base text-text-muted">
                            Belum ada transaksi tabungan pada periode ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</x-layouts.dashboard>