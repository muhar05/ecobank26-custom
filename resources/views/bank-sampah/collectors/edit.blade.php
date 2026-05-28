<x-layouts.dashboard title="Edit Agregator">
    <x-form-card title="Edit Agregator" description="Perbarui data agregator.">
        <form method="POST" action="{{ route('bank-sampah.collectors.update', $collector) }}">
            @csrf @method('PUT')
            <x-form-section title="Data Agregator">
                <div class="space-y-5">
                    <x-field-group label="Nama Agregator" name="name" required>
                        <input type="text" name="name" id="name" value="{{ old('name', $collector->name) }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>

                    <x-field-group label="Telepon" name="phone">
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $collector->phone) }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>

                    <x-field-group label="Alamat" name="address">
                        <input type="text" name="address" id="address" value="{{ old('address', $collector->address) }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.collectors.index') }}" submitLabel="Perbarui" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
