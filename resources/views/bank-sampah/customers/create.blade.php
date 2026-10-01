<x-layouts.dashboard title="Tambah Nasabah Bank Sampah">
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        {{-- Page Header --}}
        <x-ui.page-header title="Tambah Nasabah Baru" description="Daftarkan nasabah baru ke direktori Bank Sampah">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.customers.index') }}" class="hover:text-text transition-subtle">Nasabah</a>
                <span>&rarr;</span>
                <span class="text-text">Tambah Baru</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.customers.index') }}'">
                    Kembali ke Daftar
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Form Container --}}
        <x-ui.card variant="bordered" class="p-6 sm:p-8">
            <form method="POST" action="{{ route('bank-sampah.customers.store') }}" class="space-y-6">
                @csrf

                <div class="space-y-5">
                    {{-- Nama Lengkap --}}
                    <div>
                        <x-input-label for="name" value="Nama Lengkap Nasabah" :required="true" />
                        <x-text-input type="text"
                                      name="name"
                                      id="name"
                                      value="{{ old('name') }}"
                                      placeholder="Contoh: Ibu Rahayu, Pak Budi"
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
                                      value="{{ old('phone') }}"
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
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif (Dapat mencatat setoran & penarikan)</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif (Akun ditangguhkan sementara)</option>
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
                                  class="w-full text-body rounded-radius-md border border-border bg-surface text-text px-3.5 py-2.5 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">{{ old('address') }}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-1" />
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-5 border-t border-border-subtle flex flex-col sm:flex-row items-center justify-between gap-3">
                    <x-ui.button type="button" variant="ghost" onclick="window.location='{{ route('bank-sampah.customers.index') }}'">
                        Batal
                    </x-ui.button>

                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <x-ui.button type="submit" variant="primary">
                            Simpan Nasabah Baru
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.dashboard>
