<x-layouts.dashboard title="Riwayat Tabungan">
    @if(!$hasAccount)
        <div class="min-h-[60vh] flex flex-col items-center justify-center p-6 text-center">
            <div class="w-24 h-24 bg-rose-50 dark:bg-rose-950/50 rounded-full flex items-center justify-center mb-6 ring-8 ring-rose-50/50 dark:ring-rose-950/30">
                <svg class="w-12 h-12 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-slate-50 mb-4">Akun Belum Terhubung</h2>
            <p class="text-lg text-slate-600 dark:text-slate-400 max-w-lg mx-auto mb-10 leading-relaxed">
                Akun Anda belum dikaitkan dengan data nasabah Bank Sampah. Mohon hubungi administrator untuk menghubungkan akun agar dapat melihat riwayat tabungan Anda.
            </p>
            <a href="{{ route('warga.dashboard') }}" class="inline-flex items-center justify-center bg-slate-900 dark:bg-slate-700 text-white px-8 py-4 rounded-2xl text-base font-bold hover:bg-slate-800 dark:hover:bg-slate-600 transition shadow-sm">
                Kembali ke Beranda
            </a>
        </div>
    @else
        <div class="space-y-8 mx-auto">
            {{-- Header --}}
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 dark:text-slate-100">Riwayat Tabungan</h2>
                <p class="text-base text-slate-600 dark:text-slate-400 mt-2">Daftar lengkap setoran dan penarikan tabungan bank sampah Anda.</p>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-emerald-600 dark:bg-emerald-700 rounded-3xl p-8 shadow-lg text-white">
                    <p class="text-sm font-bold text-emerald-100 uppercase tracking-wider mb-2">Saldo Saat Ini</p>
                    <p class="text-5xl font-extrabold">Rp {{ number_format($balance ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="bg-slate-100 dark:bg-slate-800 rounded-3xl p-8 border border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-400 uppercase tracking-wider mb-2">Total Transaksi</p>
                    <p class="text-4xl font-extrabold text-slate-900 dark:text-slate-100">{{ $totalTransactions ?? 0 }} <span class="text-xl font-normal text-slate-500 dark:text-slate-400">Kali</span></p>
                </div>
            </div>

            {{-- Filter Toolbar --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 p-8">
                <form method="GET" action="{{ route('warga.savings.history') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Jenis Transaksi</label>
                        <select name="type" class="w-full rounded-2xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 h-14 px-4 text-base font-medium focus:ring-emerald-500">
                            <option value="">Semua</option>
                            <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Setoran</option>
                            <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Penarikan</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-2xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 h-14 px-4 text-base font-medium focus:ring-emerald-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-2xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 h-14 px-4 text-base font-medium focus:ring-emerald-500">
                    </div>

                    <button type="submit" class="w-full h-14 bg-emerald-600 dark:bg-emerald-700 text-white rounded-2xl text-base font-bold hover:bg-emerald-700 dark:hover:bg-emerald-600 transition shadow-sm">
                        Tampilkan Riwayat
                    </button>
                </form>
            </div>

            {{-- Transactions List --}}
            <div class="space-y-4">
                @forelse($ledgers as $l)
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 {{ $l->type === 'credit' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-400' }}">
                                <span class="text-xs font-bold uppercase">{{ $l->type === 'credit' ? 'Masuk' : 'Keluar' }}</span>
                            </div>
                            <div>
                                <p class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $l->description }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $l->created_at->format('d/m/Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-extrabold {{ $l->type === 'credit' ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                {{ $l->type === 'credit' ? '+' : '-' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="bg-slate-50 dark:bg-slate-900/50 p-12 rounded-3xl text-center border-2 border-dashed border-slate-200 dark:border-slate-800">
                        <p class="text-lg font-bold text-slate-600 dark:text-slate-400">Belum ada riwayat tabungan pada periode ini.</p>
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