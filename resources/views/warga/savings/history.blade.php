<x-layouts.dashboard title="Riwayat Tabungan">
    @if(!$member)
        <div class="min-h-[60vh] flex flex-col items-center justify-center p-6 text-center">
            <div class="w-24 h-24 bg-rose-50 rounded-full flex items-center justify-center mb-6 ring-8 ring-rose-50/50">
                <svg class="w-12 h-12 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-3xl font-extrabold text-slate-900 mb-4">Akun Belum Terhubung</h2>
            <p class="text-lg text-slate-600 max-w-lg mx-auto mb-10 leading-relaxed">
                Akun Anda belum dikaitkan dengan data nasabah Bank Sampah. Mohon hubungi administrator untuk menghubungkan akun agar dapat melihat riwayat tabungan Anda.
            </p>
            <a href="{{ route('warga.dashboard') }}" class="inline-flex items-center justify-center bg-slate-900 text-white px-8 py-4 rounded-2xl text-base font-bold hover:bg-slate-800 transition">
                Kembali ke Beranda
            </a>
        </div>
    @else
        <div class="space-y-8 mx-auto">
            {{-- Header --}}
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900">Riwayat Tabungan</h2>
                <p class="text-base text-slate-600 mt-2">Daftar lengkap setoran dan penarikan tabungan bank sampah Anda.</p>
            </div>

            {{-- Summary Cards (Large & Clear) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-emerald-600 rounded-3xl p-8 shadow-lg text-white">
                    <p class="text-sm font-bold text-emerald-100 uppercase tracking-wider mb-2">Saldo Saat Ini</p>
                    <p class="text-5xl font-extrabold">Rp {{ number_format($balance ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="bg-slate-100 rounded-3xl p-8 border border-slate-200">
                    <p class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">Total Transaksi</p>
                    <p class="text-4xl font-extrabold text-slate-900">{{ $totalTransactions ?? 0 }} <span class="text-xl font-normal">Kali</span></p>
                </div>
            </div>

            {{-- Filter Toolbar --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
                <form method="GET" action="{{ route('warga.savings.history') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Transaksi</label>
                        <select name="type" class="w-full rounded-2xl border-slate-300 bg-slate-50 text-slate-900 h-14 px-4 text-base font-medium">
                            <option value="">Semua</option>
                            <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Setoran</option>
                            <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Penarikan</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-2xl border-slate-300 bg-slate-50 text-slate-900 h-14 px-4 text-base font-medium">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-2xl border-slate-300 bg-slate-50 text-slate-900 h-14 px-4 text-base font-medium">
                    </div>

                    <button type="submit" class="h-14 bg-emerald-600 text-white rounded-2xl text-base font-bold hover:bg-emerald-700 transition">
                        Tampilkan Riwayat
                    </button>
                </form>
            </div>

            {{-- Transactions List (Card-based) --}}
            <div class="space-y-4">
                @forelse($ledgers as $l)
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 {{ $l->type === 'credit' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                <span class="text-xs font-bold uppercase">{{ $l->type === 'credit' ? 'Masuk' : 'Keluar' }}</span>
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
                    <div class="bg-slate-50 p-12 rounded-3xl text-center border-2 border-dashed border-slate-200">
                        <p class="text-lg font-bold text-slate-600">Belum ada riwayat tabungan pada periode ini.</p>
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
