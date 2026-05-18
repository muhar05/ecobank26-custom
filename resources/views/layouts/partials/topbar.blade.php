<header class="sticky top-0 z-30 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-700 transition-colors duration-300">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6">
        {{-- Left: hamburger + title --}}
        <div class="flex items-center gap-4">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ $title ?? 'Dashboard' }}</h2>
        </div>

        {{-- Right: toggle + user menu --}}
        <div class="flex items-center gap-3">
            <button @click="darkMode = !darkMode" class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition" aria-label="Toggle dark mode">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>

            {{-- User dropdown --}}
            <div class="relative" x-data="{ userMenuOpen: false }">
                <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 cursor-pointer">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ Auth::user()->roles->first()?->name ?? 'User' }}</p>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center">
                        <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    </div>
                </button>

                {{-- Dropdown panel --}}
                <div x-show="userMenuOpen" @click.away="userMenuOpen = false"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-lg overflow-hidden z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-3 text-sm text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800 transition">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-sm text-slate-700 dark:text-slate-200 hover:bg-red-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
