<x-layouts.dashboard title="Tabungan Bank Sampah">
    @if(!$member)
        <div class="min-h-[60vh] flex flex-col items-center justify-center p-6 text-center">
            <div class="w-24 h-24 bg-rose-50 rounded-full flex items-center justify-center mb-6 ring-8 ring-rose-50/50">
                <svg class="w-12 h-12 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-3xl font-extrabold text-slate-900 mb-4">Akun Belum Terhubung</h2>
            <p class="text-lg text-slate-600 max-w-lg mx-auto mb-10 leading-relaxed">
                Akun Anda belum dikaitkan dengan data nasabah Bank Sampah. Mohon hubungi administrator untuk menghubungkan akun agar dapat melihat tabungan Anda.
            </p>
            <a href="{{ route('warga.dashboard') }}" class="inline-flex items-center justify-center bg-slate-900 text-white px-8 py-4 rounded-2xl text-base font-bold hover:bg-slate-800 transition">
                Kembali ke Beranda
            </a>
        </div>
    @else
        <div class="space-y-8 mx-auto">
            {{-- Header --}}
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900">Tabungan Bank Sampah</h2>
                <p class="text-base text-slate-600 mt-2">Lihat saldo dan riwayat transaksi tabungan Anda dengan mudah.</p>
            </div>

            {{-- Summary Cards (Large & Clear) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-emerald-600 rounded-3xl p-8 shadow-lg text-white md:col-span-3">
                    <p class="text-sm font-bold text-emerald-100 uppercase tracking-wider mb-2">Saldo Saat Ini</p>
                    <p class="text-5xl font-extrabold">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                </div>

                <div class="bg-emerald-50 rounded-3xl p-8 border border-emerald-100">
                    <p class="text-sm font-bold text-emerald-800 uppercase tracking-wider mb-2">Total Setoran</p>
                    <p class="text-2xl font-extrabold text-emerald-900">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
                </div>

                <div class="bg-rose-50 rounded-3xl p-8 border border-rose-100">
                    <p class="text-sm font-bold text-rose-800 uppercase tracking-wider mb-2">Total Penarikan</p>
                    <p class="text-2xl font-extrabold text-rose-900">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
                </div>

                <div class="bg-slate-100 rounded-3xl p-8 border border-slate-200">
                    <p class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">Jumlah Transaksi</p>
                    <p class="text-2xl font-extrabold text-slate-900">{{ $totalTransactions ?? 0 }} <span class="text-lg font-normal">Kali</span></p>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-slate-900">Riwayat Transaksi</h3>
                    <a href="{{ route('warga.savings.history') }}" class="text-base font-bold text-emerald-600 hover:text-emerald-700 transition">Lihat Semua</a>
                </div>
                
                <div class="divide-y divide-slate-100">
                    @forelse($recentLedgers as $l)
                        <div class="p-6 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 {{ $l->type === 'credit' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    <span class="text-base font-bold">{{ $l->type === 'credit' ? 'Masuk' : 'Keluar' }}</span>
                                </div>
                                <div>
                                    <p class="text-base font-bold text-slate-900">{{ $l->description }}</p>
                                    <p class="text-sm text-slate-500 mt-1">{{ $l->created_at->format('d/m/Y, H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-extrabold {{ $l->type === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $l->type === 'credit' ? '+' : '-' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-base text-slate-500">
                            Belum ada transaksi tabungan pada periode ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</x-layouts.dashboard>
