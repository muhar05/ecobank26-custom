<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ darkMode: localStorage.theme ? localStorage.theme === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches }"
    x-init="$watch('darkMode', v => { localStorage.theme = v ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', v) }); document.documentElement.classList.toggle('dark', darkMode)"
    :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cek Saldo Nasabah — {{ config('app.name', 'Ecobank026') }} Bank Sampah</title>
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-50">

{{-- NAVBAR --}}
<nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-700 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="/" class="flex items-center gap-3">
            @if(file_exists(public_path('images/logo.png')))
                <img src="/images/logo.png" alt="Logo Bank Sampah" class="h-8">
            @else
                <div class="w-8 h-8 rounded-lg bg-emerald-700 dark:bg-emerald-500 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v8m0 0l4-4m-4 4l-4-4"/></svg>
                </div>
            @endif
            <div>
                <span class="text-lg font-bold text-emerald-800 dark:text-emerald-400">ECOBANK026</span>
                <span class="hidden sm:inline text-xs text-slate-500 dark:text-slate-400 ml-2">Bank Sampah</span>
            </div>
        </a>
        <div class="flex items-center gap-2">
            <button @click="darkMode = !darkMode" class="p-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all duration-200" aria-label="Toggle dark mode">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>
            <a href="{{ route('login') }}" class="text-emerald-700 dark:text-emerald-400 text-sm font-medium hover:text-emerald-800 transition px-3">Login</a>
        </div>
    </div>
</nav>

{{-- CONTENT --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 via-white to-green-50 dark:from-slate-950 dark:via-slate-900 dark:to-emerald-950"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">

        <div class="max-w-2xl mx-auto text-center mb-10">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h12"/></svg>
                Cek Saldo Nasabah
            </span>
            <h1 class="mt-4 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-50">Periksa Saldo Tabungan Anda</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Masukkan nomor HP dan kode nasabah untuk melihat saldo tanpa perlu login.</p>
        </div>

        <div class="max-w-2xl mx-auto">
            {{-- FORM --}}
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur rounded-2xl p-6 sm:p-8 border border-slate-100 dark:border-slate-700 shadow-sm">
                @if($errors->any())
                    <div class="mb-5 p-4 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-sm text-rose-700 dark:text-rose-300">
                        <p class="font-semibold">Periksa kembali data Anda.</p>
                        @foreach($errors->all() as $error)
                            <p class="mt-1">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('cek-saldo.check') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nomor HP</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="off"
                               placeholder="contoh: 081234567890"
                               class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm px-4 py-2.5 text-slate-900 dark:text-slate-50 placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label for="customer_code" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Kode Nasabah</label>
                        <input type="text" id="customer_code" name="customer_code" value="{{ old('customer_code') }}" required autocomplete="off"
                               placeholder="contoh: NSB-000001"
                               class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm px-4 py-2.5 text-slate-900 dark:text-slate-50 placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <button type="submit"
                            class="w-full bg-emerald-700 dark:bg-emerald-500 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-emerald-800 dark:hover:bg-emerald-400 hover:shadow-lg transition-all duration-200 shadow-sm">
                        Cek Saldo
                    </button>
                </form>

                <p class="mt-4 text-center text-xs text-slate-400 dark:text-slate-500">
                    Kode nasabah dapat dilihat pada buku/kartu nasabah Anda.
                </p>
            </div>

            {{-- RESULT --}}
            @isset($customer)
                <div class="mt-8 space-y-6">
                    <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur rounded-2xl p-6 sm:p-8 border border-emerald-100 dark:border-emerald-800 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nasabah</p>
                                <h2 class="mt-1 text-xl font-extrabold text-slate-900 dark:text-slate-50">{{ $customer->name }}</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $customer->customer_code }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Saldo Saat Ini</p>
                                <p class="mt-1 text-2xl sm:text-3xl font-extrabold text-emerald-700 dark:text-emerald-400">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur rounded-2xl p-5 border border-slate-100 dark:border-slate-700">
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">{{ number_format($totalDeposits, 0, ',', '.') }}</p>
                            <p class="mt-1 text-sm font-medium text-slate-600 dark:text-slate-300">Total Setoran</p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Rp {{ number_format($totalSetoran, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur rounded-2xl p-5 border border-slate-100 dark:border-slate-700">
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">{{ number_format($totalWithdrawals, 0, ',', '.') }}</p>
                            <p class="mt-1 text-sm font-medium text-slate-600 dark:text-slate-300">Total Penarikan</p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur rounded-2xl p-5 border border-slate-100 dark:border-slate-700">
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">Rp {{ number_format($credit, 0, ',', '.') }}</p>
                            <p class="mt-1 text-sm font-medium text-slate-600 dark:text-slate-300">Total Masuk</p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Akumulasi setoran</p>
                        </div>
                    </div>

                    @if($recentLedgers->isNotEmpty())
                        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur rounded-2xl p-6 sm:p-8 border border-slate-100 dark:border-slate-700">
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Riwayat Transaksi Terbaru</h3>
                            <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($recentLedgers as $ledger)
                                    <li class="py-3 flex items-center justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $ledger->description }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $ledger->created_at?->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <span class="text-sm font-bold whitespace-nowrap {{ $ledger->type === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $ledger->type === 'credit' ? '+' : '-' }}Rp {{ number_format($ledger->amount, 0, ',', '.') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="text-center">
                        <a href="/" class="inline-flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            @endisset
        </div>
    </div>
</section>

</body>
</html>
