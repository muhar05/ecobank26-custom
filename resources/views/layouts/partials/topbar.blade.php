<header class="sticky top-0 z-30 bg-surface/85 backdrop-blur-md border-b border-border transition-colors duration-200">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6">
        {{-- Left: hamburger + desktop collapse toggle + title --}}
        <div class="flex items-center gap-2.5">
            {{-- Mobile drawer toggle --}}
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-radius-md text-text-muted hover:text-text hover:bg-surface-muted transition-subtle" aria-label="Toggle navigation drawer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            {{-- Desktop collapse toggle --}}
            <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:flex p-2 rounded-radius-md text-text-muted hover:text-text hover:bg-surface-muted transition-subtle" aria-label="Toggle sidebar width">
                <svg class="w-5 h-5 transition-transform duration-200" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
            <h2 id="topbar-page-title" class="text-heading-sm font-bold text-text truncate">{{ $title ?? 'Dashboard' }}</h2>
        </div>

        {{-- Right: toggle + user menu --}}
        <div class="flex items-center gap-3">
            <button @click="darkMode = !darkMode" class="p-2 rounded-radius-md border border-border bg-surface text-text-muted hover:text-text hover:bg-surface-muted transition-subtle" aria-label="Toggle dark mode">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>

            {{-- User dropdown --}}
            <div class="relative" x-data="{ userMenuOpen: false }">
                <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 cursor-pointer">
                    <div class="text-right hidden sm:block">
                        <p class="text-body-sm font-semibold text-text">{{ Auth::user()?->name ?? 'User' }}</p>
                        <p class="text-caption text-text-muted">
                            @php
                                $roleName = Auth::user()?->roles?->first()?->name;
                                echo match($roleName) {
                                    'admin_rw' => 'Admin RW',
                                    'bendahara_rw', 'bendahara' => 'Bendahara RW',
                                    'admin_rt' => 'Admin RT',
                                    'admin_bank_sampah' => 'Admin Bank Sampah',
                                    'warga' => 'Warga',
                                    default => ucfirst(str_replace('_', ' ', $roleName ?? 'User'))
                                };
                            @endphp
                        </p>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}
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
                    class="absolute right-0 mt-2 w-48 bg-surface border border-border rounded-radius-md shadow-popover overflow-hidden z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-body-sm text-text hover:bg-surface-muted transition-subtle">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2.5 text-body-sm text-text hover:bg-danger-light hover:text-danger-text transition-subtle">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
