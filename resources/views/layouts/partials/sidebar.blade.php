<aside class="flex flex-col w-64 min-w-[16rem] h-full bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 transition-colors duration-300">
    {{-- Brand --}}
    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
        <h1 class="text-lg font-bold text-emerald-800 dark:text-emerald-400">ECOBANK026</h1>
        <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kas RT/RW & Bank Sampah</p>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-4 py-4 overflow-y-auto space-y-6">

        {{-- Section: Main --}}
        <div>
            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Main</p>
            <div class="space-y-1">
                @role('admin_rt')
                    <x-sidebar-link href="/dashboard" :active="request()->is('dashboard')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></x-slot>
                        Dashboard
                    </x-sidebar-link>
                @endrole
                @role('bendahara')
                    <x-sidebar-link href="/bendahara/dashboard" :active="request()->is('bendahara/dashboard')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></x-slot>
                        Dashboard
                    </x-sidebar-link>
                @endrole
                @role('admin_bank_sampah')
                    <x-sidebar-link href="/bank-sampah/dashboard" :active="request()->is('bank-sampah/dashboard')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></x-slot>
                        Dashboard
                    </x-sidebar-link>
                @endrole
                @role('warga')
                    <x-sidebar-link href="/warga/dashboard" :active="request()->is('warga/dashboard')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></x-slot>
                        Dashboard
                    </x-sidebar-link>
                @endrole
            </div>
        </div>

        {{-- Section: Kas RT/RW --}}
        @if(auth()->user()?->can('manage_fund_categories') || auth()->user()?->can('manage_contributions') || auth()->user()?->can('manage_expenses') || auth()->user()?->can('view_cash_reports'))
        <div>
            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kas RT/RW</p>
            <div class="space-y-1">
                @can('manage_fund_categories')
                    <x-sidebar-link href="/community-cash/categories" :active="request()->is('community-cash/categories*')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg></x-slot>
                        Kategori Dana
                    </x-sidebar-link>
                @endcan
                @can('manage_contributions')
                    <x-sidebar-link href="/community-cash/contributions" :active="request()->is('community-cash/contributions*')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot>
                        Pemasukan Warga
                    </x-sidebar-link>
                    <x-sidebar-link href="/iuran/tagihan" :active="request()->is('iuran/tagihan')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot>
                        Tagihan Iuran
                    </x-sidebar-link>
                    <x-sidebar-link href="/iuran/tunggakan" :active="request()->is('iuran/tunggakan*')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></x-slot>
                        Laporan Tunggakan
                    </x-sidebar-link>
                    <x-sidebar-link href="/iuran/laporan-tahunan" :active="request()->is('iuran/laporan-tahunan*')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot>
                        Laporan Tahunan Iuran
                    </x-sidebar-link>
                @endcan
                @can('manage_expenses')
                    <x-sidebar-link href="/community-cash/expenses" :active="request()->is('community-cash/expenses*')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></x-slot>
                        Pengeluaran Dana
                    </x-sidebar-link>
                @endcan
                @can('view_cash_reports')
                    <x-sidebar-link href="/community-cash/report" :active="request()->is('community-cash/report*')">
                        <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot>
                        Buku Kas Umum
                    </x-sidebar-link>
                @endcan
                @can('view_public_cash_report')
                    @role('admin_rt')
                        <x-sidebar-link href="/warga/cash-report" :active="request()->is('warga/cash-report*')">
                            <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></x-slot>
                            Laporan Kas Warga
                        </x-sidebar-link>
                    @endrole
                @endcan
            </div>
        </div>
        @endif

        {{-- Section: Data Master --}}
        @can('manage_members')
        <div>
            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Data Master</p>
            <div class="space-y-1">
                <x-sidebar-link href="/rts" :active="request()->is('rts*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></x-slot>
                    Data RT
                </x-sidebar-link>
                <x-sidebar-link href="/kks" :active="request()->is('kks*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></x-slot>
                    Data KK
                </x-sidebar-link>
                <x-sidebar-link href="/members" :active="request()->is('members*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></x-slot>
                    Data Warga
                </x-sidebar-link>
            </div>
        </div>
        @endcan

        {{-- Section: Transparansi (warga only) --}}
        @role('warga')
        <div>
            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Transparansi</p>
            <div class="space-y-1">
                <x-sidebar-link href="/warga/cash-report" :active="request()->is('warga/cash-report*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></x-slot>
                    Laporan Kas Warga
                </x-sidebar-link>
                <x-sidebar-link href="/warga/tagihan" :active="request()->is('warga/tagihan*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot>
                    Tagihan Saya
                </x-sidebar-link>
            </div>
        </div>
        @endrole

        {{-- Section: Bank Sampah Saya (warga only) --}}
        @can('view_own_savings')
            @role('warga')
            <div>
                <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Bank Sampah Saya</p>
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
            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Data Master</p>
            <div class="space-y-1">
                <x-sidebar-link href="/bank-sampah/waste-categories" :active="request()->is('bank-sampah/waste-categories*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg></x-slot>
                    Kategori Sampah
                </x-sidebar-link>
                <x-sidebar-link href="/bank-sampah/collectors" :active="request()->is('bank-sampah/collectors*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></x-slot>
                    Pengepul
                </x-sidebar-link>
                <x-sidebar-link href="/bank-sampah/waste-prices" :active="request()->is('bank-sampah/waste-prices*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot>
                    Harga Sampah
                </x-sidebar-link>
            </div>
        </div>

        <div>
            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Transaksi</p>
            <div class="space-y-1">
                <x-sidebar-link href="/bank-sampah/deposits" :active="request()->is('bank-sampah/deposits*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg></x-slot>
                    Setoran Sampah
                </x-sidebar-link>
                <x-sidebar-link href="/bank-sampah/withdrawals" :active="request()->is('bank-sampah/withdrawals*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></x-slot>
                    Penarikan Saldo
                </x-sidebar-link>
                <x-sidebar-link href="/bank-sampah/sales" :active="request()->is('bank-sampah/sales*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></x-slot>
                    Penjualan
                </x-sidebar-link>
            </div>
        </div>

        <div>
            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Laporan</p>
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
        @endrole

        {{-- Section: Bank Sampah (admin_rt view-only) --}}
        @role('admin_rt')
        @can('view_waste_bank')
        <div>
            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Bank Sampah</p>
            <div class="space-y-1">
                <x-sidebar-link href="/bank-sampah/dashboard" :active="request()->is('bank-sampah/dashboard')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg></x-slot>
                    Dashboard Bank Sampah
                </x-sidebar-link>
                <x-sidebar-link href="/bank-sampah/savings" :active="request()->is('bank-sampah/savings*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot>
                    Saldo Nasabah
                </x-sidebar-link>
                @can('view_waste_reports')
                <x-sidebar-link href="/bank-sampah/cash-report" :active="request()->is('bank-sampah/cash-report*')">
                    <x-slot name="icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot>
                    Kas Bank Sampah
                </x-sidebar-link>
                @endcan
            </div>
        </div>
        @endcan
        @endrole

    </nav>

    {{-- Bottom: Akun --}}
    <div class="px-3 py-4 border-t border-slate-100 dark:border-slate-800">
        <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Akun</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-red-50 dark:hover:bg-slate-800 hover:text-red-600 dark:hover:text-red-400 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Keluar
            </button>
        </form>
    </div>
</aside>
