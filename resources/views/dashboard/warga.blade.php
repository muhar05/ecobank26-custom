<x-layouts.dashboard title="Dashboard Warga">
    <div class="space-y-8 pb-12 mx-auto">

        {{-- Header --}}
        <div class="bg-emerald-600 dark:bg-emerald-700 rounded-3xl p-8 text-white shadow-lg">
            <h2 class="text-3xl font-extrabold tracking-tight">Halo, {{ auth()->user()->name }}! 👋</h2>
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
            <a href="{{ route('warga.bills') }}" class="flex items-center p-6 bg-slate-900 dark:bg-slate-700 text-white rounded-2xl text-lg font-bold hover:bg-slate-800 dark:hover:bg-slate-600 transition">
                <span class="mr-4 text-3xl">📋</span> Lihat Tagihan
            </a>
            <a href="{{ route('warga.cash-report') }}" class="flex items-center p-6 bg-emerald-600 dark:bg-emerald-600 text-white rounded-2xl text-lg font-bold hover:bg-emerald-700 dark:hover:bg-emerald-500 transition">
                <span class="mr-4 text-3xl">📊</span> Lihat Laporan Kas
            </a>
            <a href="{{ route('warga.savings') }}" class="flex items-center p-6 bg-blue-600 dark:bg-blue-600 text-white rounded-2xl text-lg font-bold hover:bg-blue-700 dark:hover:bg-blue-500 transition">
                <span class="mr-4 text-3xl">💰</span> Lihat Tabungan
            </a>
            <a href="{{ route('warga.savings.history') }}" class="flex items-center p-6 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-2xl text-lg font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                <span class="mr-4 text-3xl">🕒</span> Riwayat Transaksi
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