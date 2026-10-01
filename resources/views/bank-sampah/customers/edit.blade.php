<x-layouts.dashboard title="Edit Nasabah — {{ $customer->name }}">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header --}}
        <x-ui.page-header title="Edit Nasabah" description="Perbarui profil dan data kontak nasabah {{ $customer->customer_code }}">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.customers.index') }}" class="hover:text-text transition-subtle">Nasabah</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.customers.show', $customer) }}" class="hover:text-text transition-subtle">{{ $customer->customer_code }}</a>
                <span>&rarr;</span>
                <span class="text-text">Edit</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.customers.show', $customer) }}'">
                    Lihat Detail
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Form Container --}}
        <x-ui.card variant="bordered" class="p-6 sm:p-8">
            <form method="POST" action="{{ route('bank-sampah.customers.update', $customer) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-5">
                    {{-- Kode Nasabah (Read-only reference) --}}
                    <div class="p-3.5 rounded-radius-md bg-surface-muted border border-border-subtle flex items-center justify-between">
                        <div>
                            <span class="text-caption font-bold text-text-muted uppercase tracking-wider block">Kode Nasabah Resmi</span>
                            <span class="font-mono text-body font-bold text-text mt-0.5 block">{{ $customer->customer_code }}</span>
                        </div>
                        <span class="text-caption text-text-muted italic">Kode otomatis dibuat sistem</span>
                    </div>

                    {{-- Nama Lengkap --}}
                    <div>
                        <x-input-label for="name" value="Nama Lengkap Nasabah" :required="true" />
                        <x-text-input type="text"
                                      name="name"
                                      id="name"
                                      value="{{ old('name', $customer->name) }}"
                                      placeholder="Nama lengkap nasabah"
                                      required
                                      autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    {{-- Nomor Telepon --}}
                    <div>
                        <x-input-label for="phone" value="Nomor Telepon / WhatsApp (Opsional)" />
                        <x-text-input type="text"
                                      name="phone"
                                      id="phone"
                                      value="{{ old('phone', $customer->phone) }}"
                                      placeholder="Contoh: 08123456789" />
                        <p class="text-caption text-text-muted mt-1">Digunakan untuk verifikasi setoran dan pencarian instan.</p>
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>

                    {{-- Status Nasabah --}}
                    <div>
                        <x-input-label for="status" value="Status Nasabah" :required="true" />
                        <select name="status"
                                id="status"
                                required
                                class="w-full text-body rounded-radius-md border border-border bg-surface text-text px-3.5 py-2.5 focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                            <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Aktif (Dapat mencatat setoran & penarikan)</option>
                            <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Nonaktif (Akun ditangguhkan sementara)</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>

                    {{-- Alamat --}}
                    <div>
                        <x-input-label for="address" value="Alamat Lengkap / Keterangan Domisili (Opsional)" />
                        <textarea name="address"
                                  id="address"
                                  rows="3"
                                  placeholder="Contoh: RT 03 / RW 05, Blok C No. 12"
                                  class="w-full text-body rounded-radius-md border border-border bg-surface text-text px-3.5 py-2.5 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">{{ old('address', $customer->address) }}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-1" />
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-5 border-t border-border-subtle flex flex-col sm:flex-row items-center justify-between gap-3">
                    <x-ui.button type="button" variant="ghost" onclick="window.location='{{ route('bank-sampah.customers.show', $customer) }}'">
                        Batal
                    </x-ui.button>

                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <x-ui.button type="submit" variant="primary">
                            Simpan Perubahan
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.dashboard>
