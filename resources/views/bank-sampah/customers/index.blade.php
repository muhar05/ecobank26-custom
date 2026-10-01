<x-layouts.dashboard title="Data Nasabah Bank Sampah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header --}}
        <x-ui.page-header title="Data Nasabah" description="Direktori nasabah aktif Bank Sampah dan pengelolaan profil">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-text">Nasabah</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.customers.create') }}'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nasabah Baru
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

        {{-- Search & Filter Toolbar --}}
        <x-ui.card variant="bordered" class="p-4 sm:p-5">
            <form method="GET" action="{{ route('bank-sampah.customers.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari nama nasabah, kode (NSB-...), no. telepon, atau alamat..."
                           class="w-full text-body rounded-radius-md border border-border bg-surface text-text pl-10 pr-4 py-2 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                </div>

                <div class="flex items-center gap-2.5">
                    <select name="status"
                            class="text-body-sm rounded-radius-md border border-border bg-surface text-text py-2 pl-3 pr-8 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>

                    <x-ui.button type="submit" variant="secondary" size="md">
                        Filter
                    </x-ui.button>

                    @if(request()->anyFilled(['search', 'status']))
                        <a href="{{ route('bank-sampah.customers.index') }}"
                           class="text-caption font-semibold text-text-muted hover:text-danger px-2.5 py-2 transition-subtle">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </x-ui.card>

        {{-- Main Directory List / Table --}}
        @if($customers->count() > 0)
            <x-ui.card variant="bordered" class="p-0 overflow-hidden">
                {{-- DESKTOP TABLE VIEW (md+) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted/40 text-caption font-bold text-text-muted uppercase tracking-wider">
                                <th class="py-3 px-5">Nasabah</th>
                                <th class="py-3 px-5">Kontak & Alamat</th>
                                <th class="py-3 px-5">Status & Akun</th>
                                <th class="py-3 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @foreach($customers as $customer)
                                <tr class="hover:bg-surface-muted/50 transition-subtle">
                                    {{-- Nasabah Name & Code --}}
                                    <td class="py-3.5 px-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-body-sm shrink-0 shadow-subtle">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <a href="{{ route('bank-sampah.customers.show', $customer) }}" class="text-body font-bold text-text hover:text-primary transition-subtle block truncate">
                                                    {{ $customer->name }}
                                                </a>
                                                <span class="font-mono text-caption text-text-muted font-semibold block">
                                                    {{ $customer->customer_code }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Contact & Address --}}
                                    <td class="py-3.5 px-5">
                                        <div>
                                            @if($customer->phone)
                                                <span class="text-body-sm font-medium text-text font-mono block">
                                                    {{ $customer->phone }}
                                                </span>
                                            @else
                                                <span class="text-caption text-text-muted italic block">Tanpa kontak</span>
                                            @endif
                                            @if($customer->address)
                                                <span class="text-caption text-text-muted block truncate max-w-xs mt-0.5" title="{{ $customer->address }}">
                                                    {{ $customer->address }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Status & Portal Account --}}
                                    <td class="py-3.5 px-5">
                                        <div class="space-y-1">
                                            <div>
                                                @if($customer->status === 'active')
                                                    <x-ui.badge variant="success" size="sm">Aktif</x-ui.badge>
                                                @else
                                                    <x-ui.badge variant="neutral" size="sm">Nonaktif</x-ui.badge>
                                                @endif
                                            </div>
                                            <div>
                                                @if($customer->user_id)
                                                    <span class="text-caption text-primary font-semibold block">
                                                        &bull; Akun Terhubung
                                                    </span>
                                                @else
                                                    <span class="text-caption text-text-muted block">
                                                        Nasabah Langsung
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center justify-end gap-3 text-body-sm font-semibold">
                                            <a href="{{ route('bank-sampah.customers.show', $customer) }}"
                                               class="text-primary hover:underline transition-subtle">
                                                Detail
                                            </a>
                                            <a href="{{ route('bank-sampah.customers.edit', $customer) }}"
                                               class="text-text-muted hover:text-text transition-subtle">
                                                Edit
                                            </a>
                                            <button type="button"
                                                    @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.customers.destroy', $customer) }}'})"
                                                    class="text-danger hover:underline transition-subtle cursor-pointer">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE ADAPTIVE CARDS VIEW (< md) --}}
                <div class="block md:hidden divide-y divide-border-subtle p-3 space-y-3">
                    @foreach($customers as $customer)
                        <div class="p-4 rounded-radius-md border border-border bg-surface shadow-subtle space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-body shrink-0 shadow-subtle">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('bank-sampah.customers.show', $customer) }}" class="text-body font-bold text-text hover:text-primary transition-subtle block truncate">
                                            {{ $customer->name }}
                                        </a>
                                        <span class="font-mono text-caption text-text-muted font-semibold block">
                                            {{ $customer->customer_code }}
                                        </span>
                                    </div>
                                </div>
                                @if($customer->status === 'active')
                                    <x-ui.badge variant="success" size="sm">Aktif</x-ui.badge>
                                @else
                                    <x-ui.badge variant="neutral" size="sm">Nonaktif</x-ui.badge>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-caption pt-1 border-t border-border-subtle">
                                <div>
                                    <span class="text-text-muted uppercase font-bold text-caption block">Kontak</span>
                                    <span class="text-text font-medium">{{ $customer->phone ?: 'Tanpa kontak' }}</span>
                                </div>
                                <div>
                                    <span class="text-text-muted uppercase font-bold text-caption block">Status Akun</span>
                                    <span class="text-text font-medium">{{ $customer->user_id ? 'Terhubung Portal' : 'Nasabah Langsung' }}</span>
                                </div>
                            </div>

                            @if($customer->address)
                                <div class="text-caption text-text-muted">
                                    <span class="font-semibold text-text">Alamat:</span> {{ $customer->address }}
                                </div>
                            @endif

                            <div class="pt-2 border-t border-border-subtle flex items-center justify-end gap-2">
                                <x-ui.button variant="secondary" size="sm" onclick="window.location='{{ route('bank-sampah.customers.show', $customer) }}'">
                                    Detail
                                </x-ui.button>
                                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.customers.edit', $customer) }}'">
                                    Edit
                                </x-ui.button>
                                <x-ui.button variant="ghost" size="sm" class="text-danger" @click="$dispatch('open-delete-modal', {id: 'confirm-delete-modal', action: '{{ route('bank-sampah.customers.destroy', $customer) }}'})">
                                    Hapus
                                </x-ui.button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination Footer --}}
                @if($customers->hasPages())
                    <div class="p-4 border-t border-border-subtle bg-surface-muted/30">
                        {{ $customers->links() }}
                    </div>
                @endif
            </x-ui.card>
        @else
            {{-- HONEST EMPTY STATES --}}
            @if(request()->anyFilled(['search', 'status']))
                {{-- Search No Result State --}}
                <div class="p-10 text-center border-2 border-dashed border-border rounded-radius-lg bg-surface flex flex-col items-center justify-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-heading-sm font-bold text-text">Nasabah Tidak Ditemukan</h3>
                        <p class="text-body-sm text-text-muted mt-1 max-w-md mx-auto">
                            Tidak ada profil nasabah yang cocok dengan pencarian kata kunci atau filter yang Anda pilih.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="{{ route('bank-sampah.customers.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-radius-md bg-surface border border-border text-body-sm font-semibold text-text hover:bg-surface-muted transition-subtle">
                            Reset Filter
                        </a>
                        <a href="{{ route('bank-sampah.customers.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-radius-md bg-primary text-white text-body-sm font-semibold hover:bg-primary-hover shadow-subtle transition-subtle">
                            + Tambah Nasabah Baru
                        </a>
                    </div>
                </div>
            @else
                {{-- Zero Database Empty State --}}
                <div class="p-12 text-center border-2 border-dashed border-border rounded-radius-lg bg-surface flex flex-col items-center justify-center space-y-3">
                    <div class="w-14 h-14 rounded-full bg-primary-light text-primary flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-heading-sm font-bold text-text">Belum Ada Data Nasabah</h3>
                        <p class="text-body-sm text-text-muted mt-1 max-w-md mx-auto">
                            Daftarkan nasabah Bank Sampah pertama untuk mulai mencatat penimbangan sampah dan pembukuan tabungan warga.
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('bank-sampah.customers.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-radius-md bg-primary text-white text-body-sm font-semibold hover:bg-primary-hover shadow-subtle transition-subtle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            + Tambah Nasabah Pertama
                        </a>
                    </div>
                </div>
            @endif
        @endif

        {{-- Confirmation Modal --}}
        <x-confirm-delete-modal />
    </div>
</x-layouts.dashboard>
