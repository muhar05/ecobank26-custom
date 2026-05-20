<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — {{ config('app.name', 'Ecobank026') }}</title>
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
<body class="font-sans antialiased min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-50 via-slate-50 to-white dark:from-slate-950 dark:via-slate-950 dark:to-emerald-950 transition-colors duration-300">

<div class="w-full max-w-[420px] mx-4" x-data="{ show: false }" x-init="setTimeout(() => show = true, 80)">
    <div x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">

        {{-- Brand --}}
        <div class="text-center mb-8">
            @if(file_exists(public_path('images/logo.png')))
                <img src="/images/logo.png" alt="Logo" class="h-10 mx-auto mb-3">
            @endif
            <h1 class="text-xl font-bold text-emerald-800 dark:text-emerald-400">ECOBANK026</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Sistem Kas RT/RW dan Bank Sampah</p>
        </div>

        {{-- Card --}}
        <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 p-8 transition-colors duration-300">

            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-50">Masuk ke Akun</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 mb-6">Gunakan akun yang sudah diberikan oleh pengurus.</p>

            {{-- Session Status --}}
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-xl text-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="block w-full rounded-xl bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors duration-300">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="block w-full rounded-xl bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors duration-300">
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500">
                        <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">Ingat saya</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-emerald-700 dark:text-emerald-400 hover:underline">Lupa password?</a>
                    @endif
                </div>

                <button type="submit" class="w-full bg-emerald-600 dark:bg-emerald-500 text-white py-3 rounded-xl text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 hover:-translate-y-0.5 hover:shadow-lg transition-all duration-200">
                    Masuk
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <div class="mt-6 text-center space-y-2">
            <p class="text-xs text-slate-400 dark:text-slate-500">Akun dibuat oleh admin sesuai role pengguna.</p>
            <a href="/" class="inline-block text-sm text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 transition">← Kembali ke Beranda</a>
        </div>

    </div>
</div>

</body>
</html>
