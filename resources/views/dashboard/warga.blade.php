<x-layouts.dashboard title="Dashboard Warga">
    <div class="space-y-8 pb-12 mx-auto">

        {{-- Header --}}
        <div class="bg-emerald-600 rounded-3xl p-8 text-white shadow-lg">
            <h2 class="text-3xl font-extrabold tracking-tight">Halo, {{ auth()->user()->name }}! 👋</h2>
            <p class="text-base text-emerald-50 mt-2 opacity-90">Selamat datang di dashboard warga RT/RW 026. Berikut ringkasan informasi Anda.</p>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm">
                <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Kas Lingkungan</p>
                <p class="text-3xl font-extrabold text-slate-900">Rp {{ number_format($balance, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm">
                <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Total Tagihan Saya</p>
                <p class="text-3xl font-extrabold text-rose-600">Rp {{ number_format($totalKkTunggakan ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm">
                <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Tabungan Sampah</p>
                <p class="text-3xl font-extrabold text-blue-600">Rp {{ number_format($savingsBalance ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('warga.bills') }}" class="flex items-center p-6 bg-slate-900 text-white rounded-2xl text-lg font-bold hover:bg-slate-800 transition">
                <span class="mr-4 text-3xl">📋</span> Lihat Tagihan
            </a>
            <a href="{{ route('warga.cash-report') }}" class="flex items-center p-6 bg-emerald-600 text-white rounded-2xl text-lg font-bold hover:bg-emerald-700 transition">
                <span class="mr-4 text-3xl">📊</span> Lihat Laporan Kas
            </a>
            <a href="{{ route('warga.savings') }}" class="flex items-center p-6 bg-blue-600 text-white rounded-2xl text-lg font-bold hover:bg-blue-700 transition">
                <span class="mr-4 text-3xl">💰</span> Lihat Tabungan
            </a>
            <a href="{{ route('warga.savings.history') }}" class="flex items-center p-6 bg-slate-100 text-slate-900 rounded-2xl text-lg font-bold hover:bg-slate-200 transition">
                <span class="mr-4 text-3xl">🕒</span> Riwayat Transaksi
            </a>
        </div>

        {{-- Recent Activity Section --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100">
                <h3 class="text-xl font-bold text-slate-900">Tagihan & Aktivitas Terbaru</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentBills->take(3) as $b)
                    <div class="px-8 py-6 flex justify-between items-center">
                        <div>
                            <p class="text-base font-bold text-slate-900">{{ $b->fundCategory->name }}</p>
                            <p class="text-sm text-slate-500 mt-1">{{ $b->due_date ? $b->due_date->format('d M Y') : 'Tanpa tempo' }}</p>
                        </div>
                        <span class="text-base font-extrabold {{ $b->status === 'paid' ? 'text-emerald-600' : 'text-rose-600' }}">
                            Rp {{ number_format($b->outstanding_balance, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="p-8 text-center text-base text-slate-500">Data belum tersedia saat ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.dashboard>
