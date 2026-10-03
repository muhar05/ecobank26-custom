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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-background text-text">

{{-- NAVBAR --}}
<nav class="bg-white/80 dark:bg-surface/80 backdrop-blur-md border-b border-border sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="/" class="flex items-center gap-3">
            @if(file_exists(public_path('images/logo.png')))
                <img src="/images/logo.png" alt="Logo Bank Sampah" class="h-8">
            @else
                <div class="w-8 h-8 rounded-radius-md bg-primary dark:bg-primary flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v8m0 0l4-4m-4 4l-4-4"/></svg>
                </div>
            @endif
            <div>
                <span class="text-lg font-bold text-primary">ECOBANK026</span>
                <span class="hidden sm:inline text-xs text-text-muted ml-2">Bank Sampah</span>
            </div>
        </a>
        <div class="flex items-center gap-2">
            <button @click="darkMode = !darkMode" class="p-2 rounded-radius-md border border-border bg-surface text-text-muted hover:bg-surface-muted transition-all duration-200" aria-label="Toggle dark mode">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>
            <a href="{{ route('login') }}" class="text-primary text-sm font-medium hover:text-primary transition px-3">Login</a>
        </div>
    </div>
</nav>

{{-- CONTENT --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 via-white to-green-50 dark:from-slate-950 dark:via-slate-900 dark:to-emerald-950"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">

        <div class="max-w-2xl mx-auto text-center mb-10">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-radius-sm text-xs font-semibold bg-primary-light text-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h12"/></svg>
                Cek Saldo Nasabah
            </span>
            <h1 class="mt-4 text-2xl sm:text-3xl font-extrabold text-text">Periksa Saldo Tabungan Anda</h1>
            <p class="mt-2 text-sm text-text-muted">Masukkan nomor HP dan kode nasabah untuk melihat saldo tanpa perlu login.</p>
        </div>

        <div class="max-w-2xl mx-auto">
            {{-- FORM --}}
            <div class="bg-surface/80 backdrop-blur rounded-radius-lg p-6 sm:p-8 border border-border-subtle shadow-sm">
                @if($errors->any())
                    <div class="mb-5 p-4 rounded-radius-lg bg-danger-light border border-danger/30 text-sm text-danger-text">
                        <p class="font-semibold">Periksa kembali data Anda.</p>
                        @foreach($errors->all() as $error)
                            <p class="mt-1">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('cek-saldo.check') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="phone" class="block text-sm font-medium text-text">Nomor HP</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="off"
                               placeholder="contoh: 081234567890"
                               class="mt-1 block w-full rounded-radius-lg border-border bg-surface text-sm px-4 py-2.5 text-text placeholder:text-text-muted focus:border-primary focus:ring-primary">
                    </div>

                    <div>
                        <label for="customer_code" class="block text-sm font-medium text-text">Kode Nasabah</label>
                        <input type="text" id="customer_code" name="customer_code" value="{{ old('customer_code') }}" required autocomplete="off"
                               placeholder="contoh: NSB-000001"
                               class="mt-1 block w-full rounded-radius-lg border-border bg-surface text-sm px-4 py-2.5 text-text placeholder:text-text-muted focus:border-primary focus:ring-primary">
                    </div>

                    <button type="submit"
                            class="w-full bg-primary dark:bg-primary text-white px-6 py-3 rounded-radius-lg text-sm font-semibold hover:bg-primary-hover dark:hover:bg-primary hover:shadow-lg transition-all duration-200 shadow-sm">
                        Cek Saldo
                    </button>
                </form>

                <p class="mt-4 text-center text-xs text-text-muted">
                    Kode nasabah dapat dilihat pada buku/kartu nasabah Anda.
                </p>
            </div>

            {{-- RESULT --}}
            @isset($customer)
                <div class="mt-8 space-y-6">
                    <div class="bg-surface/80 backdrop-blur rounded-radius-lg p-6 sm:p-8 border border-primary/30 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Nasabah</p>
                                <h2 class="mt-1 text-xl font-extrabold text-text">{{ $customer->name }}</h2>
                                <p class="mt-1 text-sm text-text-muted">{{ $customer->customer_code }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Saldo Saat Ini</p>
                                <p class="mt-1 text-2xl sm:text-3xl font-extrabold text-primary">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-surface/80 backdrop-blur rounded-radius-lg p-5 border border-border-subtle">
                            <p class="text-2xl font-extrabold text-text">{{ number_format($totalDeposits, 0, ',', '.') }}</p>
                            <p class="mt-1 text-sm font-medium text-text-muted">Total Setoran</p>
                            <p class="mt-1 text-xs text-text-muted">Rp {{ number_format($totalSetoran, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-surface/80 backdrop-blur rounded-radius-lg p-5 border border-border-subtle">
                            <p class="text-2xl font-extrabold text-text">{{ number_format($totalWithdrawals, 0, ',', '.') }}</p>
                            <p class="mt-1 text-sm font-medium text-text-muted">Total Penarikan</p>
                            <p class="mt-1 text-xs text-text-muted">Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-surface/80 backdrop-blur rounded-radius-lg p-5 border border-border-subtle">
                            <p class="text-2xl font-extrabold text-text">Rp {{ number_format($credit, 0, ',', '.') }}</p>
                            <p class="mt-1 text-sm font-medium text-text-muted">Total Masuk</p>
                            <p class="mt-1 text-xs text-text-muted">Akumulasi setoran</p>
                        </div>
                    </div>

                    @if($recentLedgers->isNotEmpty())
                        <div class="bg-surface/80 backdrop-blur rounded-radius-lg p-6 sm:p-8 border border-border-subtle">
                            <h3 class="text-base font-bold text-text">Riwayat Transaksi Terbaru</h3>
                            <ul class="mt-4 divide-y divide-border-subtle">
                                @foreach($recentLedgers as $ledger)
                                    <li class="py-3 flex items-center justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-text truncate">{{ $ledger->description }}</p>
                                            <p class="text-xs text-text-muted mt-0.5">{{ $ledger->created_at?->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <span class="text-sm font-bold whitespace-nowrap {{ $ledger->type === 'credit' ? 'text-primary' : 'text-danger' }}">
                                            {{ $ledger->type === 'credit' ? '+' : '-' }}Rp {{ number_format($ledger->amount, 0, ',', '.') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="text-center">
                        <a href="/" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary transition">
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
