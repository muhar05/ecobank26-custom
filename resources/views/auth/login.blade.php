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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center bg-background transition-colors duration-300">

<div class="w-full max-w-[420px] mx-4" x-data="{ show: false }" x-init="setTimeout(() => show = true, 80)">
    <div x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">

        {{-- Brand --}}
        <div class="text-center mb-8">
            @if(file_exists(public_path('images/logo.png')))
                <img src="/images/logo.png" alt="Logo" class="h-10 mx-auto mb-3">
            @endif
            <h1 class="text-xl font-bold text-primary">ECOBANK026</h1>
            <p class="text-sm text-text-muted mt-1">Sistem Kas RT/RW dan Bank Sampah</p>
        </div>

        {{-- Card --}}
        <div class="bg-surface rounded-radius-lg shadow-popover border border-border p-8 transition-colors duration-300">

            <h2 class="text-lg font-bold text-text">Masuk ke Akun</h2>
            <p class="text-sm text-text-muted mt-1 mb-6">Gunakan akun yang sudah diberikan oleh pengurus.</p>

            {{-- Session Status --}}
            @if (session('status'))
                <div class="mb-4 p-3 bg-success-light text-success-text rounded-radius-md text-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="login" class="block text-sm font-medium text-text mb-1">No. Telepon</label>
                    <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="08123456789" class="block w-full rounded-radius-md bg-surface border-border text-text shadow-subtle text-base focus:border-primary focus:ring-primary transition-subtle">
                    <p class="mt-1 text-sm text-text-muted">Contoh: <span class="font-medium">08123456789</span></p>
                    @error('login') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-text mb-1">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="block w-full rounded-radius-md bg-surface border-border text-text shadow-subtle text-base focus:border-primary focus:ring-primary transition-subtle">
                    @error('password') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-border text-primary focus:ring-primary">
                        <span class="ml-2 text-sm text-text-muted">Ingat saya</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">Lupa password?</a>
                    @endif
                </div>

                <button type="submit" class="w-full bg-primary text-white py-3 rounded-radius-md text-base font-semibold hover:bg-primary-hover transition-subtle shadow-subtle">
                    Masuk
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <div class="mt-6 text-center space-y-2">
            <p class="text-sm text-text-muted">Akun dibuat oleh admin sesuai role pengguna.</p>
            <a href="/" class="inline-block text-sm text-text-muted hover:text-primary transition-subtle">← Kembali ke Beranda</a>
        </div>

    </div>
</div>

</body>
</html>
