<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ darkMode: localStorage.theme ? localStorage.theme === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches }"
    x-init="$watch('darkMode', v => { localStorage.theme = v ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', v) }); document.documentElement.classList.toggle('dark', darkMode)"
    :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Ecobank026') }} — Kas RT/RW & Bank Sampah</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        .animate-float { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-50">

{{-- NAVBAR --}}
<nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-700 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
            @if(file_exists(public_path('images/logo.png')))
                <img src="/images/logo.png" alt="Logo" class="h-8">
            @endif
            <div>
                <span class="text-lg font-bold text-emerald-800 dark:text-emerald-400">ECOBANK026</span>
                <span class="hidden sm:inline text-xs text-slate-500 dark:text-slate-400 ml-2">Kas RT/RW & Bank Sampah</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button @click="darkMode = !darkMode" class="p-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all duration-200" aria-label="Toggle dark mode">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>
            @auth
                <a href="{{ route('dashboard') }}" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 hover:shadow-md transition-all duration-200">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 hover:shadow-md transition-all duration-200">Masuk</a>
            @endauth
        </div>
    </div>
</nav>

{{-- HERO --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 via-white to-green-50 dark:from-slate-950 dark:via-slate-900 dark:to-emerald-950"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {{-- Left content with fade-up --}}
            <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)" class="space-y-6">
                <div x-show="show" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">
                        Sistem Informasi Kas Warga
                    </span>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 dark:text-slate-50 leading-tight">
                        Kelola kas RT/RW dan bank sampah dalam satu sistem
                        <span class="text-emerald-700 dark:text-emerald-400">transparan</span>
                    </h1>
                    <p class="text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-lg">
                        Catat pemasukan warga, pengeluaran dana, buku kas, serta setoran bank sampah dengan data yang rapi dan mudah dipantau.
                    </p>
                </div>
                <div x-show="show" x-transition:enter="transition ease-out duration-700 delay-300" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col sm:flex-row gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-center bg-emerald-700 dark:bg-emerald-500 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-emerald-800 dark:hover:bg-emerald-400 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 shadow-sm">Masuk ke Aplikasi</a>
                    @else
                        <a href="{{ route('login') }}" class="text-center bg-emerald-700 dark:bg-emerald-500 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-emerald-800 dark:hover:bg-emerald-400 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 shadow-sm">Masuk ke Aplikasi</a>
                    @endauth
                    <a href="{{ auth()->check() ? url('/warga/cash-report') : route('login') }}" class="text-center bg-white dark:bg-slate-800 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-slate-600 px-6 py-3 rounded-xl text-sm font-semibold hover:bg-emerald-50 dark:hover:bg-slate-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">Lihat Laporan Warga</a>
                </div>
                <div x-show="show" x-transition:enter="transition ease-out duration-700 delay-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex flex-wrap gap-4 pt-2">
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Data rapi
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Laporan transparan
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Cocok untuk RT/RW
                    </span>
                </div>
            </div>

            {{-- Right: Dashboard preview mockup with float --}}
            <div class="hidden lg:block animate-float">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-emerald-50 dark:bg-emerald-950 rounded-xl p-3 border border-emerald-100 dark:border-emerald-800">
                            <p class="text-[10px] font-medium text-emerald-600 dark:text-emerald-400 uppercase">Kas Warga</p>
                            <p class="text-lg font-bold text-emerald-800 dark:text-emerald-300 mt-0.5">Rp 3,5jt</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-950 rounded-xl p-3 border border-green-100 dark:border-green-800">
                            <p class="text-[10px] font-medium text-green-600 dark:text-green-400 uppercase">Dana Sampah</p>
                            <p class="text-lg font-bold text-green-800 dark:text-green-300 mt-0.5">Rp 1,2jt</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-950 rounded-xl p-3 border border-red-100 dark:border-red-800">
                            <p class="text-[10px] font-medium text-red-600 dark:text-red-400 uppercase">Pengeluaran</p>
                            <p class="text-lg font-bold text-red-800 dark:text-red-300 mt-0.5">Rp 850rb</p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="bg-slate-50 dark:bg-slate-800 px-4 py-2">
                            <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Buku Kas Terbaru</p>
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-slate-700 text-xs">
                            <div class="px-4 py-2.5 flex justify-between"><span class="text-slate-700 dark:text-slate-300">Iuran Mei — Budi</span><span class="text-emerald-700 dark:text-emerald-400 font-medium">+Rp 50.000</span></div>
                            <div class="px-4 py-2.5 flex justify-between"><span class="text-slate-700 dark:text-slate-300">Iuran Mei — Siti</span><span class="text-emerald-700 dark:text-emerald-400 font-medium">+Rp 50.000</span></div>
                            <div class="px-4 py-2.5 flex justify-between"><span class="text-slate-700 dark:text-slate-300">Beli sapu kebersihan</span><span class="text-red-600 dark:text-red-400 font-medium">-Rp 75.000</span></div>
                            <div class="px-4 py-2.5 flex justify-between"><span class="text-slate-700 dark:text-slate-300">Santunan keluarga</span><span class="text-red-600 dark:text-red-400 font-medium">-Rp 200.000</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FEATURES --}}
<section class="bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-slate-50">Fitur Utama</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Semua yang dibutuhkan untuk mengelola keuangan warga</p>
        </div>
        <div x-data="{ show: false }" x-intersect.once="show = true" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-slate-50 dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Kas RT/RW</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">Catat iuran warga, dana sosial, dana kematian, dan dana kebersihan.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-slate-50 dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Buku Kas Umum</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">Pantau uang masuk, uang keluar, dan saldo berjalan per kategori dana.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-slate-50 dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Transparansi Warga</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">Warga dapat melihat laporan kas secara read-only.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-slate-50 dark:bg-slate-800 rounded-2xl p-6 border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Bank Sampah</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">Kelola setoran sampah, saldo nasabah, dan transaksi bank sampah.</p>
            </div>
        </div>
    </div>
</section>

{{-- WORKFLOW --}}
<section class="bg-emerald-50 dark:bg-slate-800 border-t border-emerald-100 dark:border-slate-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-slate-50">Cara Kerja</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Tiga langkah sederhana untuk kas warga yang tertib</p>
        </div>
        <div x-data="{ show: false }" x-intersect.once="show = true" class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-700 dark:bg-emerald-500 text-white flex items-center justify-center mx-auto text-lg font-bold shadow-lg">1</div>
                <h3 class="mt-4 text-sm font-bold text-slate-800 dark:text-slate-100">Catat Pemasukan</h3>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Admin atau bendahara mencatat iuran warga ke kategori dana yang sesuai.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-150" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-700 dark:bg-emerald-500 text-white flex items-center justify-center mx-auto text-lg font-bold shadow-lg">2</div>
                <h3 class="mt-4 text-sm font-bold text-slate-800 dark:text-slate-100">Catat Pengeluaran</h3>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Setiap pengeluaran dicatat dengan keterangan dan kategori dana.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-300" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0" class="text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-700 dark:bg-emerald-500 text-white flex items-center justify-center mx-auto text-lg font-bold shadow-lg">3</div>
                <h3 class="mt-4 text-sm font-bold text-slate-800 dark:text-slate-100">Warga Melihat Laporan</h3>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Warga dapat mengakses laporan kas secara transparan dan real-time.</p>
            </div>
        </div>
    </div>
</section>

{{-- ROLES --}}
<section class="bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-slate-50">Peran Pengguna</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Setiap peran memiliki akses yang sesuai</p>
        </div>
        <div x-data="{ show: false }" x-intersect.once="show = true" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Admin RT</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Akses penuh ke semua modul kas dan bank sampah.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Bendahara</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola pemasukan, pengeluaran, dan buku kas warga.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Admin Bank Sampah</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola operasional bank sampah secara terpisah.</p>
            </div>
            <div x-show="show" x-transition:enter="transition ease-out duration-500 delay-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Warga</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lihat laporan kas RT/RW secara transparan.</p>
            </div>
        </div>
    </div>
</section>

{{-- FINAL CTA --}}
<section class="bg-emerald-950 dark:bg-emerald-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-white">Mulai kelola kas warga dengan lebih tertib</h2>
        <p class="mt-3 text-sm text-emerald-200">Sistem yang rapi, transparan, dan mudah digunakan.</p>
        <div class="mt-8">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-block bg-white text-emerald-800 px-8 py-3 rounded-xl text-sm font-bold hover:bg-emerald-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 shadow-lg">Masuk Sekarang</a>
            @else
                <a href="{{ route('login') }}" class="inline-block bg-white text-emerald-800 px-8 py-3 rounded-xl text-sm font-bold hover:bg-emerald-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 shadow-lg">Masuk Sekarang</a>
            @endauth
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer class="bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-xs text-slate-400 dark:text-slate-500">&copy; {{ date('Y') }} Ecobank026 — Sistem Kas RT/RW dan Bank Sampah</p>
    </div>
</footer>

</body>
</html>
