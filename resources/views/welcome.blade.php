<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth"
    x-data="{ darkMode: localStorage.theme ? localStorage.theme === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches }"
    x-init="$watch('darkMode', v => { localStorage.theme = v ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', v) }); document.documentElement.classList.toggle('dark', darkMode)"
    :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Ecobank026') }} — Sistem Bank Sampah</title>
    <meta name="description" content="Kelola bank sampah secara digital: nasabah, setoran sampah, saldo tabungan, penarikan, penjualan, dan laporan dalam satu sistem.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-background text-text">

@php
    use App\Models\Deposit;
    use App\Models\DepositDetail;
    use App\Models\SavingsLedger;
    use App\Models\WasteCategory;
    use App\Models\WasteCustomer;

    $totalNasabah     = WasteCustomer::count();
    $totalSetoran     = Deposit::count();
    $totalSampahKg    = (float) DepositDetail::sum('weight');
    $totalSaldo       = (float) (SavingsLedger::where('type', 'credit')->sum('amount')
                                - SavingsLedger::where('type', 'debit')->sum('amount'));
    $kategoriSampah   = WasteCategory::take(8)->get(['name', 'unit']);
@endphp

{{-- NAVBAR --}}
<nav class="bg-surface/80 backdrop-blur-md border-b border-border sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="#beranda" class="flex items-center gap-3">
            @if(file_exists(public_path('images/logo.png')))
                <img src="/images/logo.png" alt="Logo Bank Sampah" class="h-8">
            @else
                <div class="w-8 h-8 rounded-radius-md bg-primary flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v8m0 0l4-4m-4 4l-4-4"/></svg>
                </div>
            @endif
            <div>
                <span class="text-lg font-bold text-primary">ECOBANK026</span>
                <span class="hidden sm:inline text-xs text-text-muted ml-2">Bank Sampah</span>
            </div>
        </a>

        <div class="hidden lg:flex items-center gap-8 text-sm font-medium text-text-muted">
            <a href="#beranda" class="hover:text-primary-hover transition">Beranda</a>
            <a href="#tentang" class="hover:text-primary-hover transition">Tentang</a>
            <a href="#cara-kerja" class="hover:text-primary-hover transition">Cara Kerja</a>
            <a href="#fitur" class="hover:text-primary-hover transition">Fitur</a>
        </div>

        <div class="flex items-center gap-2">
            <button @click="darkMode = !darkMode" class="p-2 rounded-radius-md border border-border bg-white dark:bg-surface-muted text-text-muted hover:bg-surface-muted dark:hover:bg-surface-muted transition-all duration-200" aria-label="Toggle dark mode">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>
            @auth
                <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex text-primary font-medium text-sm hover:text-primary transition px-3">Dashboard</a>
                <a href="{{ route('dashboard') }}" class="bg-primary text-white px-4 py-2 rounded-radius-md text-sm font-medium hover:bg-primary-hover hover:shadow-md transition-all duration-200">Mulai Sekarang</a>
            @else
                <a href="{{ route('login') }}" class="hidden sm:inline-flex text-primary font-medium text-sm hover:text-primary transition px-3">Login</a>
                <a href="{{ route('login') }}" class="bg-primary text-white px-4 py-2 rounded-radius-md text-sm font-medium hover:bg-primary-hover hover:shadow-md transition-all duration-200">Mulai Sekarang</a>
            @endauth
        </div>
    </div>
</nav>

{{-- HERO --}}
<section id="beranda" class="relative overflow-hidden scroll-mt-16">
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 via-white to-green-50 dark:from-slate-950 dark:via-slate-900 dark:to-emerald-950"></div>
    <div class="absolute right-0 top-0 w-96 h-96 bg-emerald-400/20 rounded-full blur-3xl -translate-y-1/3 translate-x-1/3"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-28">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {{-- Left content --}}
            <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)" class="space-y-6">
                <div x-show="show" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-primary-light text-primary">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                        Sistem Manajemen Bank Sampah
                    </span>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-text leading-tight">
                        Kelola Bank Sampah Lebih Mudah
                        <span class="text-primary">dan Terorganisir</span>
                    </h1>
                    <p class="text-base text-text-muted leading-relaxed max-w-lg">
                        Kelola nasabah, catat setoran sampah, pantau saldo tabungan, penarikan, penjualan, hingga laporan transaksi — semuanya dalam satu sistem digital yang rapi dan transparan.
                    </p>
                </div>
                <div x-show="show" x-transition:enter="transition ease-out duration-700 delay-300" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('login') }}" class="text-center bg-primary text-white px-6 py-3 rounded-radius-lg text-sm font-semibold hover:bg-primary-hover hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 shadow-sm">Login</a>
                    <a href="{{ route('cek-saldo.index') }}" class="text-center bg-white dark:bg-surface-muted text-primary border border-primary/30 px-6 py-3 rounded-radius-lg text-sm font-semibold hover:bg-primary-light dark:hover:bg-surface-muted hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">Cek Saldo</a>
                    <a href="#tentang" class="text-center bg-white dark:bg-surface-muted text-primary border border-primary/30 px-6 py-3 rounded-radius-lg text-sm font-semibold hover:bg-primary-light dark:hover:bg-surface-muted hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">Pelajari Sistem</a>
                </div>
                <div x-show="show" x-transition:enter="transition ease-out duration-700 delay-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex flex-wrap gap-4 pt-2">
                    <span class="flex items-center gap-1.5 text-xs text-text-muted">
                        <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Pencatatan transaksi terpusat
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-text-muted">
                        <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Saldo nasabah terpantau
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-text-muted">
                        <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Ramah lingkungan
                    </span>
                </div>
            </div>

            {{-- Right visual: stat cards --}}
            <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 300)" class="relative">
                <div x-show="show" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="grid grid-cols-2 gap-4">
                    <div class="bg-surface/80 backdrop-blur rounded-radius-lg p-5 border border-border-subtle shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Total Nasabah</p>
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <p class="mt-2 text-3xl font-extrabold text-text dark:text-white">{{ number_format($totalNasabah, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-surface/80 backdrop-blur rounded-radius-lg p-5 border border-border-subtle shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Total Setoran</p>
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v8m0 0l4-4m-4 4l-4-4"/></svg>
                        </div>
                        <p class="mt-2 text-3xl font-extrabold text-text dark:text-white">{{ number_format($totalSetoran, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-surface/80 backdrop-blur rounded-radius-lg p-5 border border-border-subtle shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Sampah Terkumpul</p>
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <p class="mt-2 text-3xl font-extrabold text-text dark:text-white">{{ number_format($totalSampahKg, 0, ',', '.') }} <span class="text-sm font-semibold text-text-muted">kg</span></p>
                    </div>
                    <div class="bg-surface/80 backdrop-blur rounded-radius-lg p-5 border border-border-subtle shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-text-muted uppercase tracking-wider">Total Saldo Nasabah</p>
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="mt-2 text-3xl font-extrabold text-text dark:text-white">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- STATISTIK --}}
<section id="tentang" class="bg-surface border-t border-border scroll-mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-text">Ringkasan Bank Sampah</h2>
            <p class="mt-2 text-sm text-text-muted">Kondisi terkini aktivitas bank sampah secara langsung dari sistem</p>
        </div>
        <div x-data="{ show: false }" x-intersect.once="show = true" class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle">
                <p class="text-3xl font-extrabold text-primary">{{ number_format($totalNasabah, 0, ',', '.') }}</p>
                <p class="mt-1 text-sm font-medium text-text-muted">Total Nasabah</p>
                <p class="mt-1 text-xs text-text-muted dark:text-text-muted">Profil nasabah terdaftar</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle">
                <p class="text-3xl font-extrabold text-primary">{{ number_format($totalSetoran, 0, ',', '.') }}</p>
                <p class="mt-1 text-sm font-medium text-text-muted">Total Setoran</p>
                <p class="mt-1 text-xs text-text-muted dark:text-text-muted">Transaksi setoran sampah</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle">
                <p class="text-3xl font-extrabold text-primary">{{ number_format($totalSampahKg, 0, ',', '.') }} <span class="text-base font-semibold">kg</span></p>
                <p class="mt-1 text-sm font-medium text-text-muted">Sampah Terkumpul</p>
                <p class="mt-1 text-xs text-text-muted dark:text-text-muted">Total berat sampah terkumpul</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle">
                <p class="text-3xl font-extrabold text-primary">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
                <p class="mt-1 text-sm font-medium text-text-muted">Total Saldo Nasabah</p>
                <p class="mt-1 text-xs text-text-muted dark:text-text-muted">Akumulasi saldo tabungan</p>
            </div>
        </div>
    </div>
</section>

{{-- FITUR --}}
<section id="fitur" class="bg-surface border-t border-border scroll-mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-text">Fitur Utama</h2>
            <p class="mt-2 text-sm text-text-muted">Semua kebutuhan pengelolaan bank sampah dalam satu sistem</p>
        </div>
        <div x-data="{ show: false }" x-intersect.once="show = true" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-radius-lg bg-primary-light flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-text dark:text-text">Manajemen Nasabah</h3>
                <p class="text-xs text-text-muted mt-2 leading-relaxed">Kelola profil, kontak, dan status nasabah bank sampah secara terpusat.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-radius-lg bg-primary-light flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v8m0 0l4-4m-4 4l-4-4"/></svg>
                </div>
                <h3 class="text-sm font-bold text-text dark:text-text">Setoran Sampah</h3>
                <p class="text-xs text-text-muted mt-2 leading-relaxed">Catat setoran per kategori sampah dengan berat dan harga otomatis.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-radius-lg bg-primary-light flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h12"/></svg>
                </div>
                <h3 class="text-sm font-bold text-text dark:text-text">Tabungan Nasabah</h3>
                <p class="text-xs text-text-muted mt-2 leading-relaxed">Setiap setoran otomatis menambah saldo tabungan nasabah.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-radius-lg bg-primary-light flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-text dark:text-text">Penarikan Saldo</h3>
                <p class="text-xs text-text-muted mt-2 leading-relaxed">Nasabah dapat menarik saldo tabungan yang telah terkumpul.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-400" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-radius-lg bg-primary-light flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-text dark:text-text">Penjualan Sampah</h3>
                <p class="text-xs text-text-muted mt-2 leading-relaxed">Kelola penjualan sampah ke pengepul dengan hitung margin otomatis.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-500" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-radius-lg bg-primary-light flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2zm0 0h10M7 4v1h10V4M9 17h6"/></svg>
                </div>
                <h3 class="text-sm font-bold text-text dark:text-text">Kategori & Harga</h3>
                <p class="text-xs text-text-muted mt-2 leading-relaxed">Kelola kategori sampah dan harga nasabah & pengepul (dual pricing).</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-600" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-radius-lg bg-primary-light flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055zM20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-text dark:text-text">Kas Bank Sampah</h3>
                <p class="text-xs text-text-muted mt-2 leading-relaxed">Pantau kas operasional dan pengeluaran bank sampah secara real-time.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-surface-muted rounded-radius-lg p-6 border border-border-subtle hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-radius-lg bg-primary-light flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-text dark:text-text">Laporan & Audit</h3>
                <p class="text-xs text-text-muted mt-2 leading-relaxed">Laporan & export, plus monitoring kesehatan data bank sampah.</p>
            </div>
        </div>
    </div>
</section>

{{-- CARA KERJA --}}
<section id="cara-kerja" class="bg-primary-light border-t border-primary/30 scroll-mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-text">Cara Kerja</h2>
            <p class="mt-2 text-sm text-text-muted">Alur sederhana pengelolaan bank sampah yang terdigitalisasi</p>
        </div>
        <div x-data="{ show: false }" x-intersect.once="show = true" class="grid grid-cols-1 md:grid-cols-5 gap-8">
            <div x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="text-center">
                <div class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center mx-auto text-lg font-bold shadow-lg">1</div>
                <h3 class="mt-4 text-sm font-bold text-text dark:text-text">Nasabah</h3>
                <p class="mt-2 text-xs text-text-muted">Nasabah membawa sampah ke bank sampah untuk disetor.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-150" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="text-center">
                <div class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center mx-auto text-lg font-bold shadow-lg">2</div>
                <h3 class="mt-4 text-sm font-bold text-text dark:text-text">Setor Sampah</h3>
                <p class="mt-2 text-xs text-text-muted">Petugas mencatat setoran ke dalam sistem per kategori.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-300" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="text-center">
                <div class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center mx-auto text-lg font-bold shadow-lg">3</div>
                <h3 class="mt-4 text-sm font-bold text-text dark:text-text">Sampah Ditimbang</h3>
                <p class="mt-2 text-xs text-text-muted">Berat dihitung dengan harga otomatis sesuai kategori.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-450" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="text-center">
                <div class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center mx-auto text-lg font-bold shadow-lg">4</div>
                <h3 class="mt-4 text-sm font-bold text-text dark:text-text">Saldo Bertambah</h3>
                <p class="mt-2 text-xs text-text-muted">Nilai setoran otomatis menambah saldo tabungan nasabah.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-600" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="text-center">
                <div class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center mx-auto text-lg font-bold shadow-lg">5</div>
                <h3 class="mt-4 text-sm font-bold text-text dark:text-text">Saldo Dapat Ditarik</h3>
                <p class="mt-2 text-xs text-text-muted">Saldo dapat ditarik nasabah setelah memenuhi ketentuan.</p>
            </div>
        </div>
    </div>
</section>

{{-- KATEGORI SAMPAH --}}
<section class="bg-surface border-t border-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-text">Kategori Sampah</h2>
            <p class="mt-2 text-sm text-text-muted">Jenis sampah yang dikelola dalam sistem bank sampah</p>
        </div>
        <div x-data="{ show: false }" x-intersect.once="show = true" class="flex flex-wrap justify-center gap-4">
            @forelse($kategoriSampah as $kat)
                <div x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" class="bg-primary-light border border-primary/30 rounded-radius-lg px-6 py-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-radius-md bg-primary-light flex items-center justify-center">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-text dark:text-text">{{ $kat->name }}</p>
                        <p class="text-xs text-text-muted">Satuan: {{ $kat->unit ?? 'kg' }}</p>
                    </div>
                </div>
            @empty
                <div x-show="show" class="text-center text-sm text-text-muted">
                    Belum ada kategori sampah yang terdaftar.
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- BENEFIT --}}
<section class="bg-primary-light border-t border-primary/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-text">Mengapa Bank Sampah Digital?</h2>
                <p class="mt-3 text-sm text-text-muted leading-relaxed">Digitalisasi pengelolaan bank sampah membuat seluruh proses lebih rapi, transparan, dan mudah dipantau.</p>
                <div class="mt-8">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-block bg-primary text-white px-6 py-3 rounded-radius-lg text-sm font-semibold hover:bg-primary-hover hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 shadow-sm">Masuk ke Aplikasi</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-block bg-primary text-white px-6 py-3 rounded-radius-lg text-sm font-semibold hover:bg-primary-hover hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 shadow-sm">Login Sekarang</a>
                    @endauth
                </div>
            </div>
            <div x-data="{ show: false }" x-intersect.once="show = true" class="space-y-4">
                @php
                    $benefits = [
                        ['title' => 'Pencatatan transaksi terpusat', 'desc' => 'Seluruh setoran, penarikan, dan penjualan tercatat rapi dalam satu sistem.', 'icon' => 'M3 6h18M3 12h18M3 18h12'],
                        ['title' => 'Saldo nasabah mudah dipantau', 'desc' => 'Setiap nasabah memiliki saldo tabungan yang transparan dan terupdate.', 'icon' => 'M3 6h18M3 12h18M3 18h12'],
                        ['title' => 'Pengelolaan harga sampah', 'desc' => 'Harga nasabah dan pengepul dikelola dengan dual pricing yang akurat.', 'icon' => 'M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                        ['title' => 'Laporan transaksi', 'desc' => 'Laporan setoran, penjualan, dan saldo dapat diekspor kapan saja.', 'icon' => 'M9 12l2 2 4-4'],
                        ['title' => 'Monitoring kondisi bank sampah', 'desc' => 'Audit kesehatan data untuk menjaga konsistensi dan integritas transaksi.', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ];
                @endphp
                @foreach($benefits as $b)
                    <div x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-6" x-transition:enter-end="opacity-100 translate-x-0" class="flex items-start gap-4 bg-surface rounded-radius-lg p-5 border border-primary/30">
                        <div class="w-10 h-10 rounded-radius-lg bg-primary-light flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $b['icon'] }}"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-text dark:text-text">{{ $b['title'] }}</h3>
                            <p class="mt-1 text-xs text-text-muted leading-relaxed">{{ $b['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- FINAL CTA --}}
<section class="bg-primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-white">Mulai Kelola Bank Sampah Secara Digital</h2>
        <p class="mt-3 text-sm text-white/80">Sistem yang rapi, transparan, dan ramah lingkungan untuk pengelolaan bank sampah Anda.</p>
        <div class="mt-8">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-block bg-white text-primary px-8 py-3 rounded-radius-lg text-sm font-bold hover:bg-primary-light hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 shadow-lg">Masuk ke Aplikasi</a>
            @else
                <a href="{{ route('login') }}" class="inline-block bg-white text-primary px-8 py-3 rounded-radius-lg text-sm font-bold hover:bg-primary-light hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 shadow-lg">Mulai Sekarang</a>
            @endauth
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer class="bg-background border-t border-border py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-2">
                @if(file_exists(public_path('images/logo.png')))
                    <img src="/images/logo.png" alt="Logo Bank Sampah" class="h-6">
                @endif
                <span class="text-sm font-bold text-primary">ECOBANK026</span>
                <span class="text-xs text-text-muted dark:text-text-muted">Sistem Bank Sampah</span>
            </div>
            <nav class="flex flex-wrap gap-4 text-xs font-medium text-text-muted">
                <a href="#beranda" class="hover:text-primary-hover transition">Beranda</a>
                <a href="#tentang" class="hover:text-primary-hover transition">Tentang</a>
                <a href="#cara-kerja" class="hover:text-primary-hover transition">Cara Kerja</a>
                <a href="#fitur" class="hover:text-primary-hover transition">Fitur</a>
            </nav>
        </div>
        <div class="mt-6 pt-4 border-t border-border text-center">
            <p class="text-xs text-text-muted dark:text-text-muted">&copy; {{ date('Y') }} {{ config('app.name', 'Ecobank026') }} — Sistem Manajemen Bank Sampah</p>
        </div>
    </div>
</footer>

</body>
</html>
