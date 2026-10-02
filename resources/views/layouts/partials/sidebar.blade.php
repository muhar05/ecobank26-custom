<aside id="sidebar-aside" class="flex flex-col h-full w-64 bg-surface border-r border-border transition-all duration-200 overflow-hidden" :class="sidebarCollapsed ? 'lg:w-20 lg:min-w-[5rem]' : 'lg:w-64 lg:min-w-[16rem]'">
    {{-- Brand --}}
    <div class="px-5 py-5 border-b border-border-subtle flex items-center justify-between overflow-hidden">
        <div class="flex items-center gap-2.5 overflow-hidden" x-show="!sidebarCollapsed">
            <img src="{{ asset('images/logo.png') }}" alt="EcoBank026" class="w-8 h-8 rounded-radius-md object-contain shrink-0">
            <div class="min-w-0">
                <h1 class="text-heading-sm font-bold text-text truncate">ECOBANK026</h1>
                <p class="text-caption text-text-muted uppercase tracking-wider font-semibold">Bank Sampah</p>
            </div>
        </div>
        <div class="mx-auto" x-show="sidebarCollapsed" x-cloak>
            <img src="{{ asset('images/logo.png') }}" alt="EcoBank026" class="w-8 h-8 rounded-radius-md object-contain shadow-subtle shrink-0" title="EcoBank026">
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-6">

        {{-- Section: Main --}}
        <div>
            <p class="px-3 mb-2 text-caption font-bold uppercase tracking-wider text-text-muted/70" x-show="!sidebarCollapsed">Main</p>
            <div class="space-y-1">
                <x-sidebar-link href="/bank-sampah/dashboard" :active="request()->is('bank-sampah/dashboard')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></x-slot>
                    Dashboard
                </x-sidebar-link>
            </div>
        </div>

        {{-- Section: Bank Sampah Saya (warga only) --}}
        @can('view_own_savings')
            @role('warga')
            <div>
                <p class="px-3 mb-2 text-caption font-bold uppercase tracking-wider text-text-muted/70" x-show="!sidebarCollapsed">Bank Sampah Saya</p>
                <div class="space-y-1">
                    <x-sidebar-link href="/warga/savings" :active="request()->is('warga/savings') || request()->is('warga/savings/')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot>
                        Saldo Saya
                    </x-sidebar-link>
                    <x-sidebar-link href="/warga/savings/history" :active="request()->is('warga/savings/history*')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot>
                        Riwayat Tabungan
                    </x-sidebar-link>
                </div>
            </div>
            @endrole
        @endcan

        {{-- Section: Bank Sampah (admin_bank_sampah full menu) --}}
        @role('admin_bank_sampah')
        <div>
            <p class="px-3 mb-2 text-caption font-bold uppercase tracking-wider text-text-muted/70" x-show="!sidebarCollapsed">Operasional</p>
            <div class="space-y-1">
                <x-sidebar-link href="/bank-sampah/deposits" :active="request()->is('bank-sampah/deposits*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg></x-slot>
                    Setoran Sampah
                </x-sidebar-link>
                <x-sidebar-link href="/bank-sampah/sales" :active="request()->is('bank-sampah/sales*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></x-slot>
                    Penjualan
                </x-sidebar-link>
                <x-sidebar-link href="/bank-sampah/withdrawals" :active="request()->is('bank-sampah/withdrawals*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></x-slot>
                    Penarikan Saldo
                </x-sidebar-link>
                <x-sidebar-link href="/bank-sampah/expenses" :active="request()->is('bank-sampah/expenses*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></x-slot>
                    Pengeluaran Operasional
                </x-sidebar-link>
            </div>
        </div>

        @can('manage_waste_customers')
        <div>
            <p class="px-3 mb-2 text-caption font-bold uppercase tracking-wider text-text-muted/70" x-show="!sidebarCollapsed">Nasabah</p>
            <div class="space-y-1">
                <x-sidebar-link href="/bank-sampah/customers" :active="request()->is('bank-sampah/customers*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></x-slot>
                    Data Nasabah
                </x-sidebar-link>
            </div>
        </div>
        @endcan

        <div>
            <p class="px-3 mb-2 text-caption font-bold uppercase tracking-wider text-text-muted/70" x-show="!sidebarCollapsed">Laporan</p>
            <div class="space-y-1">
                <x-sidebar-link href="/bank-sampah/savings" :active="request()->is('bank-sampah/savings*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot>
                    Saldo Nasabah
                </x-sidebar-link>
                <x-sidebar-link href="/bank-sampah/cash-report" :active="request()->is('bank-sampah/cash-report*')">
                    <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot>
                    Kas Bank Sampah
                </x-sidebar-link>
            </div>
        </div>

        <div>
            <p class="px-3 mb-2 text-caption font-bold uppercase tracking-wider text-text-muted/70" x-show="!sidebarCollapsed">Lainnya</p>
            <div class="space-y-1">
                @php
                    $masterActive = request()->is('bank-sampah/waste-categories*')
                        || request()->is('bank-sampah/collectors*')
                        || request()->is('bank-sampah/waste-prices*');
                @endphp
                <div x-data="{ masterOpen: {{ $masterActive ? 'true' : 'false' }} }"
                     @eco:master-sync.window="masterOpen = $event.detail.open">
                    <button type="button" @click="masterOpen = !masterOpen" title="Master Data"
                        data-master-toggle
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-radius-md text-body-sm font-medium transition-subtle {{ $masterActive ? 'bg-primary text-white shadow-subtle' : 'text-text-muted hover:bg-surface-muted hover:text-text' }}">
                        <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                        </span>
                        <span class="truncate transition-opacity duration-200 flex-1 text-left" x-show="!sidebarCollapsed">Master Data</span>
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-200" :class="masterOpen && 'rotate-180'" x-show="!sidebarCollapsed" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="masterOpen"
                         x-transition.opacity.duration.150ms
                         @if(!$masterActive) style="display: none;" @endif
                         class="space-y-1 mt-1"
                         :class="!sidebarCollapsed && 'ml-4 pl-2 border-l border-border'">
                        <x-sidebar-link href="/bank-sampah/waste-categories" :active="request()->is('bank-sampah/waste-categories*')">
                            <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg></x-slot>
                            Kategori Sampah
                        </x-sidebar-link>
                        <x-sidebar-link href="/bank-sampah/collectors" :active="request()->is('bank-sampah/collectors*')">
                            <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></x-slot>
                            Agregator
                        </x-sidebar-link>
                        <x-sidebar-link href="/bank-sampah/waste-prices" :active="request()->is('bank-sampah/waste-prices*')">
                            <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot>
                            Harga Sampah
                        </x-sidebar-link>
                    </div>
                </div>
                <x-sidebar-link href="/bank-sampah/devices" :active="request()->is('bank-sampah/devices*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></x-slot>
                    Perangkat Saya
                </x-sidebar-link>
            </div>
        </div>
        @endrole

        {{-- Section: Bank Sampah (admin_rt view-only) --}}
        @role('admin_rt')
        @can('view_waste_bank')
        <div>
            <p class="px-3 mb-2 text-caption font-bold uppercase tracking-wider text-text-muted/70" x-show="!sidebarCollapsed">Bank Sampah</p>
            <div class="space-y-1">
                <x-sidebar-link href="/bank-sampah/dashboard" :active="request()->is('bank-sampah/dashboard')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg></x-slot>
                    Dashboard Bank Sampah
                </x-sidebar-link>
                <x-sidebar-link href="/bank-sampah/savings" :active="request()->is('bank-sampah/savings*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot>
                    Saldo Nasabah
                </x-sidebar-link>
            </div>
        </div>
        @endcan
        @endrole

    </nav>

    {{-- Bottom: Akun & Logout --}}
    <div class="px-3 py-4 border-t border-border-subtle">
        <p class="px-3 mb-2 text-caption font-bold uppercase tracking-wider text-text-muted/70" x-show="!sidebarCollapsed">Akun</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-radius-md text-body-sm font-medium text-text-muted hover:bg-danger-light hover:text-danger-text transition-subtle">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span class="truncate" x-show="!sidebarCollapsed">Keluar</span>
            </button>
        </form>
    </div>
</aside>