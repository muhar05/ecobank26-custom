<x-layouts.dashboard title="Edit Agregator - {{ $collector->name }}">
    <div class="max-w-2xl mx-auto space-y-6 pb-12">
        {{-- Page Header --}}
        <x-ui.page-header title="Edit Agregator" description="Perbarui data profil mitra pengepul.">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.collectors.index') }}" class="hover:text-text transition-subtle">Agregator</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.collectors.show', $collector) }}" class="hover:text-text transition-subtle">{{ $collector->name }}</a>
                <span>&rarr;</span>
                <span class="text-text">Edit</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.collectors.show', $collector) }}'">
                    &larr; Kembali ke Detail
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.card variant="bordered" class="space-y-5">
            <div class="border-b border-border-subtle pb-4">
                <h2 class="text-heading-sm font-bold text-text">Informasi Agregator</h2>
                <p class="text-caption text-text-muted mt-0.5">Perubahan profil tidak mempengaruhi harga lapak dan riwayat harga yang sudah tersimpan.</p>
            </div>

            <form method="POST" action="{{ route('bank-sampah.collectors.update', $collector) }}" class="space-y-5">
                @csrf @method('PUT')

                <x-field-group label="Nama Agregator" name="name" required>
                    <input type="text" name="name" id="name" value="{{ old('name', $collector->name) }}" required autofocus class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle min-h-[44px] px-3.5 focus:border-primary focus:ring-1 focus:ring-primary transition-subtle placeholder:text-text-muted" />
                </x-field-group>

                <x-field-group label="Telepon" name="phone" helper="Nomor kontak yang bisa dihubungi, contoh: 08xxxxxxxxxx.">
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $collector->phone) }}" inputmode="tel" class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle min-h-[44px] px-3.5 focus:border-primary focus:ring-1 focus:ring-primary transition-subtle placeholder:text-text-muted" />
                </x-field-group>

                <x-field-group label="Alamat" name="address" helper="Alamat gudang atau lokasi pengepul.">
                    <input type="text" name="address" id="address" value="{{ old('address', $collector->address) }}" class="block w-full rounded-radius-md border-border bg-surface text-text text-base shadow-subtle min-h-[44px] px-3.5 focus:border-primary focus:ring-1 focus:ring-primary transition-subtle placeholder:text-text-muted" />
                </x-field-group>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-border-subtle">
                    <x-ui.button variant="ghost" size="md" onclick="window.location='{{ route('bank-sampah.collectors.show', $collector) }}'">
                        Batal
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="md">
                        Simpan Perubahan
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.dashboard>
