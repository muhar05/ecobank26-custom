<x-layouts.dashboard title="Detail Nasabah — {{ $customer->name }}">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header --}}
        <x-ui.page-header title="{{ $customer->name }}" description="Profil nasabah, ringkasan saldo tabungan, dan aksi transaksi langsung">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.customers.index') }}" class="hover:text-text transition-subtle">Nasabah</a>
                <span>&rarr;</span>
                <span class="text-text">{{ $customer->customer_code }}</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.customers.index') }}'">
                    Kembali ke Daftar
                </x-ui.button>
                <x-ui.button variant="secondary" size="sm" onclick="window.location='{{ route('bank-sampah.customers.edit', $customer) }}'">
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Profil
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Flash Feedback --}}
        @if(session('success'))
            <div class="p-4 bg-success-light border border-success/30 text-success-text rounded-radius-md flex items-center gap-3 shadow-subtle">
                <svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <div class="text-body-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-danger-light border border-danger/30 text-danger-text rounded-radius-md flex items-center gap-3 shadow-subtle">
                <svg class="w-5 h-5 text-danger shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-body-sm font-medium">{{ session('error') }}</div>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- 1. IDENTITY & CONNECT-THE-DOT ACTION BANNER               --}}
        {{-- ========================================================= --}}
        <x-ui.card variant="bordered" class="p-5 sm:p-7">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-heading-lg shadow-subtle shrink-0">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-heading-lg font-bold text-text">{{ $customer->name }}</h2>
                            @if($customer->status === 'active')
                                <x-ui.badge variant="success" size="sm">Aktif</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral" size="sm">Nonaktif</x-ui.badge>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2.5 mt-1">
                            <span class="font-mono text-body-sm font-semibold text-text-muted">
                                {{ $customer->customer_code }}
                            </span>
                            <span class="text-caption text-text-muted">&bull;</span>
                            @if($customer->user_id)
                                <span class="text-caption font-semibold text-primary">
                                    Terhubung ke Portal Warga
                                </span>
                            @else
                                <span class="text-caption text-text-muted">
                                    Nasabah Langsung (Tanpa Login)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action-First Buttons: Connect The Dots --}}
                <div class="flex flex-wrap items-center gap-2.5">
                    @can('manage_deposits')
                        <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.deposits.create', ['customer_id' => $customer->id]) }}'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Catat Setoran
                        </x-ui.button>
                    @endcan

                    @can('manage_withdrawals')
                        <x-ui.button variant="secondary" size="md" onclick="window.location='{{ route('bank-sampah.withdrawals.create') }}'">
                            Tarik Saldo
                        </x-ui.button>
                    @endcan

                    @can('view_waste_reports')
                        <x-ui.button variant="outline" size="md" onclick="window.location='{{ route('bank-sampah.savings') }}'">
                            Buku Tabungan &rarr;
                        </x-ui.button>
                    @endcan
                </div>
            </div>
        </x-ui.card>

        {{-- ========================================================= --}}
        {{-- 2. FINANCIAL & TRANSACTION OVERVIEW (3 CARDS)             --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
            {{-- Card 1: Saldo Tabungan --}}
            <div class="bg-surface border border-border rounded-radius-lg p-5 shadow-subtle flex flex-col justify-between space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-caption font-bold text-text-muted uppercase tracking-wider">Saldo Tabungan Aktif</span>
                    <div class="w-8 h-8 rounded-radius-sm bg-primary-light text-primary flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="font-mono text-heading-lg font-bold text-primary tracking-tight">
                        Rp {{ number_format($balance, 0, ',', '.') }}
                    </p>
                    <p class="text-caption text-text-muted mt-0.5">Sisa saldo tabungan nasabah</p>
                </div>
            </div>

            {{-- Card 2: Frekuensi Setoran --}}
            <div class="bg-surface border border-border rounded-radius-lg p-5 shadow-subtle flex flex-col justify-between space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-caption font-bold text-text-muted uppercase tracking-wider">Frekuensi Setoran</span>
                    <div class="w-8 h-8 rounded-radius-sm bg-success-light text-success-text flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="font-mono text-heading-lg font-bold text-text tracking-tight">
                        {{ $depositsCount }} <span class="text-body-sm font-normal text-text-muted">Kali</span>
                    </p>
                    <p class="text-caption text-text-muted mt-0.5">Penimbangan sampah tercatat</p>
                </div>
            </div>

            {{-- Card 3: Frekuensi Penarikan --}}
            <div class="bg-surface border border-border rounded-radius-lg p-5 shadow-subtle flex flex-col justify-between space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-caption font-bold text-text-muted uppercase tracking-wider">Frekuensi Penarikan</span>
                    <div class="w-8 h-8 rounded-radius-sm bg-surface-muted text-text flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="font-mono text-heading-lg font-bold text-text tracking-tight">
                        {{ $withdrawalsCount }} <span class="text-body-sm font-normal text-text-muted">Kali</span>
                    </p>
                    <p class="text-caption text-text-muted mt-0.5">Pencairan tabungan tunai</p>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 3. PROFILE DETAILS & PORTAL ACCOUNT CONNECTION (2 COLS)   --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Col Left: Data Profil & Kontak --}}
            <x-ui.card variant="bordered" class="p-5 sm:p-6 space-y-4">
                <div class="border-b border-border-subtle pb-3 flex items-center justify-between">
                    <h3 class="text-heading-sm font-bold text-text">Informasi Kontak & Alamat</h3>
                    <a href="{{ route('bank-sampah.customers.edit', $customer) }}" class="text-caption font-semibold text-primary hover:underline">
                        Ubah Data &rarr;
                    </a>
                </div>

                <div class="space-y-3.5 text-body-sm">
                    <div>
                        <span class="text-caption font-bold text-text-muted uppercase tracking-wider block">Nomor Telepon</span>
                        @if($customer->phone)
                            <span class="font-mono text-body font-semibold text-text block mt-0.5">
                                {{ $customer->phone }}
                            </span>
                        @else
                            <span class="text-text-muted italic block mt-0.5">Tidak ada nomor telepon</span>
                        @endif
                    </div>

                    <div>
                        <span class="text-caption font-bold text-text-muted uppercase tracking-wider block">Alamat Lengkap</span>
                        <span class="text-text block mt-0.5">
                            {{ $customer->address ?: 'Belum ada data alamat tercatat.' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-caption font-bold text-text-muted uppercase tracking-wider block">Tanggal Bergabung</span>
                        <span class="text-text block mt-0.5 font-medium">
                            {{ $customer->joined_at ? $customer->joined_at->translatedFormat('d F Y') : $customer->created_at->translatedFormat('d F Y') }}
                        </span>
                    </div>
                </div>
            </x-ui.card>

            {{-- Col Right: Akun Login Portal Warga --}}
            <x-ui.card variant="bordered" class="p-5 sm:p-6 space-y-4">
                <div class="border-b border-border-subtle pb-3">
                    <h3 class="text-heading-sm font-bold text-text">Koneksi Akun Portal Warga</h3>
                    <p class="text-caption text-text-muted mt-0.5">Akses mandiri nasabah untuk memantau saldo tabungan</p>
                </div>

                @if($customer->user)
                    <div class="p-4 rounded-radius-md bg-primary-light/40 border border-primary/20 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-success"></span>
                            <span class="text-body-sm font-bold text-text">Akun Pengguna Aktif</span>
                        </div>
                        <div class="text-body-sm space-y-1 pt-1">
                            <div>
                                <span class="text-caption text-text-muted block">Nama Akun:</span>
                                <span class="font-semibold text-text">{{ $customer->user->name }}</span>
                            </div>
                            <div>
                                <span class="text-caption text-text-muted block">Email:</span>
                                <span class="font-mono text-text">{{ $customer->user->email ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-radius-md bg-surface-muted border border-border-subtle space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-text-muted"></span>
                            <span class="text-body-sm font-bold text-text">Belum Terhubung</span>
                        </div>
                        <p class="text-caption text-text-muted leading-relaxed">
                            Nasabah ini bertransaksi langsung di kasir Bank Sampah dan belum ditautkan ke akun login portal warga mandiri.
                        </p>
                    </div>
                @endif

                <div class="pt-2 text-caption text-text-muted">
                    <span>Butuh bantuan penyesuaian profil?</span>
                    <a href="{{ route('bank-sampah.customers.edit', $customer) }}" class="text-primary font-semibold hover:underline ml-1">
                        Edit Profil Nasabah
                    </a>
                </div>
            </x-ui.card>
        </div>

        {{-- Danger Zone: Hapus Nasabah (Only if allowed) --}}
        <div class="pt-4 border-t border-border-subtle flex items-center justify-between text-body-sm">
            <span class="text-caption text-text-muted">
                Nasabah yang memiliki riwayat timbangan atau tabungan tidak dapat dihapus demi integritas akuntansi.
            </span>
            <button type="button"
                    @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.customers.destroy', $customer) }}'})"
                    class="text-caption font-semibold text-danger hover:underline cursor-pointer">
                Hapus Nasabah Ini
            </button>
        </div>

        {{-- Confirmation Modal --}}
        <x-confirm-delete-modal />
    </div>
</x-layouts.dashboard>
