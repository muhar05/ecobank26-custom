<x-layouts.dashboard title="Tambah Agregator">
    <x-form-card title="Tambah Agregator" description="Daftarkan agregator baru untuk transaksi bank sampah.">
        <form method="POST" action="{{ route('bank-sampah.collectors.store') }}">
            @csrf
            <x-form-section title="Data Agregator">
                <div class="space-y-5">
                    <x-field-group label="Nama Agregator" name="name" required>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>

                    <x-field-group label="Telepon" name="phone">
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>

                    <x-field-group label="Alamat" name="address">
                        <input type="text" name="address" id="address" value="{{ old('address') }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.collectors.index') }}" submitLabel="Simpan Agregator" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
