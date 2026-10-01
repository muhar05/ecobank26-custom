@props(['title' => 'Dashboard'])

@if(request()->header('X-Eco-Nav'))
    <div id="eco-spa-fragment"
         data-title="{{ $title ?? 'Dashboard' }}"
         data-document-title="{{ ($title ?? 'Dashboard') . ' · ' . config('app.name', 'EcoBank026') }}">
        <div id="app-content-slot" x-data>
            {{ $slot }}
        </div>
        @stack('scripts')
    </div>
@else
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'Dashboard') . ' · ' . config('app.name', 'EcoBank026') }}</title>
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
<body class="font-sans antialiased bg-background text-text transition-colors duration-200"
    x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', darkMode: document.documentElement.classList.contains('dark') }"
    x-init="$watch('darkMode', v => { localStorage.theme = v ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', v) }); $watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))"
    @close-sidebar.window="sidebarOpen = false">

{{-- SPA Navigation Topbar Progress Indicator --}}
<div id="eco-nav-progress" class="fixed top-0 left-0 right-0 h-0.5 bg-primary z-50 transition-all duration-300 opacity-0 pointer-events-none" style="width: 0%;"></div>

<div class="flex h-screen overflow-hidden">
    {{-- Sidebar: desktop (collapsible 256px / 80px, state persists via localStorage) --}}
    <div class="hidden lg:flex transition-all duration-200 flex-shrink-0"
         :class="sidebarCollapsed ? 'lg:w-20 lg:min-w-[5rem]' : 'lg:w-64 lg:min-w-[16rem]'">
        @include('layouts.partials.sidebar')
    </div>

    {{-- Sidebar: mobile overlay --}}
    <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 lg:hidden" @click="sidebarOpen = false" x-cloak>
        <div class="absolute inset-0 bg-black/50"></div>
    </div>
    <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 w-64 lg:hidden" x-cloak>
        @include('layouts.partials.sidebar')
    </div>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        @include('layouts.partials.topbar', ['title' => $title])

        <main id="app-content" class="flex-1 overflow-y-auto p-4 sm:p-6">
            <div id="app-content-slot" x-data>
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

<x-ui.toast />

<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.0/dist/apexcharts.min.js"></script>
@stack('scripts')
</body>
</html>
@endif
