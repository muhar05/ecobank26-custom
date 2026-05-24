<x-layouts.dashboard title="Tabungan Bank Sampah">
    @if(!$member)
        <div class="min-h-[60vh] flex flex-col items-center justify-center p-6 text-center">
            <div class="w-24 h-24 bg-rose-50 dark:bg-rose-900/30 rounded-full flex items-center justify-center mb-6 ring-8 ring-rose-50/50 dark:ring-rose-900/20">
                <svg class="w-12 h-12 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-2">Akun Belum Terhubung</h2>
            <p class="text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-8 leading-relaxed">
                Akun Anda saat ini belum dikaitkan dengan data nasabah Bank Sampah manapun. 
                Silakan hubungi administrator untuk menghubungkan akun Anda agar dapat melihat tabungan.
            </p>
            <a href="{{ route('warga.dashboard') }}" class="inline-flex items-center justify-center bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 px-6 py-3 rounded-xl text-sm font-bold hover:bg-slate-800 dark:hover:bg-white transition shadow-sm">
                Kembali ke Dashboard
            </a>
        </div>
    @else
        <div class="space-y-6">
            {{-- Quick Actions & Greeting --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Halo, {{ auth()->user()->name }} 👋</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Berikut adalah ringkasan tabungan bank sampah Anda.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('warga.cash-report') }}" class="inline-flex items-center justify-center bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm w-full sm:w-auto">
                        Kas Warga
                    </a>
                    <a href="{{ route('warga.savings.history') }}" class="inline-flex items-center justify-center bg-emerald-600 dark:bg-emerald-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 transition shadow-sm w-full sm:w-auto">
                        Riwayat Lengkap
                    </a>
                </div>
            </div>

            {{-- Main Hero & Summary Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Hero Card --}}
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-3xl p-6 md:p-8 shadow-xl shadow-emerald-500/20 text-white relative overflow-hidden lg:col-span-1 flex flex-col justify-between min-h-[200px] border border-emerald-400/30">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-32 h-32 transform translate-x-8 -translate-y-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    </div>
                    
                    <div class="relative z-10">
                        <p class="text-emerald-50 font-medium text-sm md:text-base mb-1">Total Saldo Tersedia</p>
                        <h3 class="text-3xl md:text-4xl font-bold tracking-tight">Rp {{ number_format($balance, 0, ',', '.') }}</h3>
                    </div>
                    
                    <div class="relative z-10 mt-8 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-emerald-100 bg-emerald-900/20 px-3 py-1.5 rounded-lg backdrop-blur-sm w-fit border border-emerald-400/20">
                            <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                            Terakhir diupdate hari ini
                        </div>
                    </div>
                </div>

                {{-- Summary Stats --}}
                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col justify-center">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Pemasukan</p>
                        <p class="text-xl font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col justify-center">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400 mb-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Penarikan</p>
                        <p class="text-xl font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col justify-center">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Transaksi</p>
                        <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $totalTransactions ?? 0 }} <span class="text-sm font-normal text-slate-500">kali</span></p>
                    </div>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Transaksi Terakhir</h3>
                    <a href="{{ route('warga.savings.history') }}" class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">Lihat Semua</a>
                </div>
                
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentLedgers as $l)
                        <div class="p-4 sm:p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors duration-200">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 {{ $l->type === 'credit' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400' }}">
                                    @if($l->type === 'credit')
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $l->description }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $l->created_at->format('d M Y, H:i') }}</p>
                                        @if($l->reference_type)
                                            @php
                                                $refType = class_basename($l->reference_type);
                                                $refName = match($refType) {
                                                    'Deposit' => 'Setoran',
                                                    'Withdrawal' => 'Penarikan',
                                                    'WasteSale' => 'Penjualan',
                                                    default => $refType
                                                };
                                            @endphp
                                            <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                            <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ $refName }} #{{ $l->reference_id }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-base font-bold {{ $l->type === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-slate-100' }}">
                                    {{ $l->type === 'credit' ? '+' : '-' }}Rp {{ number_format($l->amount, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center">
                            <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1">Belum ada riwayat</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">Anda belum memiliki transaksi tabungan di Bank Sampah. Mari mulai menabung!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</x-layouts.dashboard>
