<x-layouts.dashboard title="Dashboard Warga">
    <div class="space-y-8 pb-12 mx-auto">

        {{-- Header --}}
        <div class="bg-emerald-600 dark:bg-emerald-700 rounded-3xl p-8 text-white shadow-lg">
            <h2 class="text-3xl font-extrabold tracking-tight">Halo, {{ auth()->user()->name }}!
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 inline-block align-text-bottom">
                    <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>
                </svg>
            </h2>
            <p class="text-base text-emerald-50 mt-2 opacity-90">Selamat datang di dashboard warga RT/RW 026. Berikut ringkasan informasi Anda.</p>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-200 dark:border-slate-700 shadow-sm">
                <p class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Kas Lingkungan</p>
                <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-50">Rp {{ number_format($balance, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-200 dark:border-slate-700 shadow-sm">
                <p class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Total Tagihan Saya</p>
                <p class="text-3xl font-extrabold text-rose-600 dark:text-rose-400">Rp {{ number_format($totalKkTunggakan ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-200 dark:border-slate-700 shadow-sm">
                <p class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Tabungan Sampah</p>
                <p class="text-3xl font-extrabold text-blue-600 dark:text-blue-400">Rp {{ number_format($savingsBalance ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('warga.bills') }}" class="flex items-center gap-x-4 p-6 bg-slate-900 dark:bg-slate-700 text-white rounded-2xl text-lg font-bold hover:bg-slate-800 dark:hover:bg-slate-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 flex-shrink-0">
                    <path d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                </svg>
                <span>Lihat Tagihan</span>
            </a>
            <a href="{{ route('warga.cash-report') }}" class="flex items-center gap-x-4 p-6 bg-emerald-600 dark:bg-emerald-600 text-white rounded-2xl text-lg font-bold hover:bg-emerald-700 dark:hover:bg-emerald-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 flex-shrink-0">
                    <path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                <span>Lihat Laporan Kas</span>
            </a>
            <a href="{{ route('warga.savings') }}" class="flex items-center gap-x-4 p-6 bg-blue-600 dark:bg-blue-600 text-white rounded-2xl text-lg font-bold hover:bg-blue-700 dark:hover:bg-blue-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 flex-shrink-0">
                    <path d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.575c0 .908.784 1.564 1.686 1.439.996-.138 1.814-.565 2.064-1.514a3.743 3.743 0 00.25-1.218V4.5a1.5 1.5 0 00-1.5-1.5H3.75a1.5 1.5 0 00-1.5 1.5z"/>
                </svg>
                <span>Lihat Tabungan</span>
            </a>
            <a href="{{ route('warga.savings.history') }}" class="flex items-center gap-x-4 p-6 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-2xl text-lg font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 flex-shrink-0">
                    <path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Riwayat Transaksi</span>
            </a>
        </div>

        {{-- Recent Activity Section --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50">Tagihan & Aktivitas Terbaru</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($recentBills->take(3) as $b)
                    <div class="px-8 py-6 flex justify-between items-center">
                        <div>
                            <p class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $b->fundCategory->name }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $b->due_date ? $b->due_date->format('d M Y') : 'Tanpa tempo' }}</p>
                        </div>
                        <span class="text-base font-extrabold {{ $b->status === 'paid' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            Rp {{ number_format($b->outstanding_balance, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="p-8 text-center text-base text-slate-500 dark:text-slate-400">Data belum tersedia saat ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.dashboard>