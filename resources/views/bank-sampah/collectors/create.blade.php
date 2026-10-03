<x-layouts.dashboard title="Tambah Agregator">
    <div class="max-w-3xl mx-auto space-y-6 pb-12">
        {{-- Page Header --}}
        <x-ui.page-header title="Tambah Agregator" description="Daftarkan mitra pengepul baru.">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.collectors.index') }}" class="hover:text-text transition-subtle">Agregator</a>
                <span>&rarr;</span>
                <span class="text-text">Tambah</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.collectors.index') }}'">
                    &larr; Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.card variant="bordered" class="space-y-6">
            <div class="flex items-baseline justify-between gap-4">
                <h2 class="text-heading-sm font-bold text-text">Informasi Agregator</h2>
                <span class="text-caption text-text-muted shrink-0">Harga lapak diatur setelah agregator tersimpan</span>
            </div>

            <form method="POST" action="{{ route('bank-sampah.collectors.store') }}" class="space-y-6">
                @csrf

                {{-- Field utama: full width, bobot visual lebih besar --}}
                <x-field-group label="Nama Agregator" name="name" required>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus placeholder="Contoh: Lapak Berkah Jaya" class="block w-full rounded-radius-md border-border bg-surface text-text text-lg font-medium shadow-subtle min-h-[52px] px-4 focus:border-primary focus:ring-1 focus:ring-primary transition-subtle placeholder:text-text-muted placeholder:font-normal" />
                </x-field-group>

                {{-- Field pendukung: 2 kolom di desktop, 1 kolom di mobile --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-field-group label="Telepon" name="phone">
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" inputmode="tel" placeholder="08xxxxxxxxxx" class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle min-h-[44px] px-3.5 focus:border-primary focus:ring-1 focus:ring-primary transition-subtle placeholder:text-text-muted" />
                    </x-field-group>

                    <x-field-group label="Alamat" name="address">
                        <input type="text" name="address" id="address" value="{{ old('address') }}" placeholder="Contoh: Jl. Industri No. 12" class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle min-h-[44px] px-3.5 focus:border-primary focus:ring-1 focus:ring-primary transition-subtle placeholder:text-text-muted" />
                    </x-field-group>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                    <x-ui.button variant="ghost" size="md" onclick="window.location='{{ route('bank-sampah.collectors.index') }}'">
                        Batal
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="md">
                        Simpan Agregator
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.dashboard>
