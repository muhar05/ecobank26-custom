@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Ecobank026') }}</title>
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-50 transition-colors duration-300"
    x-data="{ sidebarOpen: false, darkMode: document.documentElement.classList.contains('dark') }"
    x-init="$watch('darkMode', v => { localStorage.theme = v ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', v) })">

<div class="flex h-screen overflow-hidden">
    {{-- Sidebar: desktop --}}
    <div class="hidden lg:flex lg:w-64 lg:flex-shrink-0 transition-colors duration-300">
        @include('layouts.partials.sidebar')
    </div>

    {{-- Sidebar: mobile overlay --}}
    <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 lg:hidden" @click="sidebarOpen = false">
        <div class="absolute inset-0 bg-black/50"></div>
    </div>
    <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 w-64 lg:hidden">
        @include('layouts.partials.sidebar')
    </div>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        @include('layouts.partials.topbar', ['title' => $title])

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 transition-colors duration-300">
            {{ $slot }}
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.0/dist/apexcharts.min.js"></script>
@stack('scripts')
</body>
</html>
