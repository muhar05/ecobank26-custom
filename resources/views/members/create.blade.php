<x-layouts.dashboard title="Tambah Warga">
    <x-form-card title="Tambah Warga" description="Daftarkan warga baru ke dalam sistem.">
        <form method="POST" action="{{ route('members.store') }}">
            @csrf
            <x-form-section title="Identitas Warga">
                <div class="space-y-5">
                    <x-field-group label="Kode Warga" name="member_code" helper="Kosongkan untuk generate otomatis. Kode berikutnya: {{ $nextCode }}">
                        <input type="text" name="member_code" id="member_code" value="{{ old('member_code') }}" placeholder="{{ $nextCode }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                    </x-field-group>

                    <x-field-group label="Nama Lengkap" name="name" required>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-field-group label="Tanggal Lahir" name="birth_date">
                            <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </x-field-group>

                        <x-field-group label="Jenis Kelamin" name="gender">
                            <select name="gender" id="gender" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" {{ old('gender') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('gender') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </x-field-group>
                    </div>
                </div>
            </x-form-section>

            <x-form-section title="Kontak">
                <div class="space-y-5">
                    <x-field-group label="Telepon" name="phone">
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>

                    <x-field-group label="Alamat" name="address">
                        <input type="text" name="address" id="address" value="{{ old('address') }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-section title="Keluarga & Wilayah">
                <div class="space-y-5">
                    <x-field-group label="Kartu Keluarga (KK)" name="kk_id" helper="Opsional (Hubungkan warga ke KK)">
                        <select name="kk_id" id="kk_id" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Belum Terhubung KK</option>
                            @foreach($kks as $kk)
                                <option value="{{ $kk->id }}" {{ old('kk_id') == $kk->id ? 'selected' : '' }}>Keluarga {{ $kk->family_head }} (RT {{ $kk->rt->rt_number }})</option>
                            @endforeach
                        </select>
                    </x-field-group>

                    <x-field-group label="Hubungan Keluarga" name="relationship" helper="Opsional (Misal: Kepala Keluarga, Istri, Anak)">
                        <input type="text" name="relationship" id="relationship" value="{{ old('relationship') }}" placeholder="Contoh: Kepala Keluarga" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('members.index') }}" submitLabel="Simpan Warga" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
