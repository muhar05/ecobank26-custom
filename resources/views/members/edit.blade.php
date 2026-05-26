<x-layouts.dashboard title="Edit Warga">
    <x-form-card title="Edit Warga" description="Perbarui data warga yang sudah terdaftar.">
        <form method="POST" action="{{ route('members.update', $member) }}">
            @csrf @method('PUT')
            <x-form-section title="Identitas Warga">
                <div class="space-y-5">
                    <x-field-group label="Kode Warga" name="member_code" required helper="Kode harus unik. Ubah hanya jika diperlukan.">
                        <input type="text" name="member_code" id="member_code" value="{{ old('member_code', $member->member_code) }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                    </x-field-group>

                    <x-field-group label="Nama Lengkap" name="name" required>
                        <input type="text" name="name" id="name" value="{{ old('name', $member->name) }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-section title="Kontak">
                <div class="space-y-5">
                    <x-field-group label="Telepon" name="phone">
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $member->phone) }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>

                    <x-field-group label="Alamat" name="address">
                        <input type="text" name="address" id="address" value="{{ old('address', $member->address) }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-section title="Keluarga & Wilayah">
                <div class="space-y-5">
                    <x-field-group label="Kartu Keluarga (KK)" name="kk_id" helper="Opsional (Hubungkan warga ke KK)">
                        <select name="kk_id" id="kk_id" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Belum Terhubung KK</option>
                            @foreach($kks as $kk)
                                <option value="{{ $kk->id }}" {{ old('kk_id', $member->kk_id) == $kk->id ? 'selected' : '' }}>Keluarga {{ $kk->family_head }} (RT {{ $kk->rt->rt_number }})</option>
                            @endforeach
                        </select>
                    </x-field-group>

                    <x-field-group label="Hubungan Keluarga" name="relationship" helper="Opsional (Misal: Kepala Keluarga, Istri, Anak)">
                        <input type="text" name="relationship" id="relationship" value="{{ old('relationship', $member->relationship) }}" placeholder="Contoh: Kepala Keluarga" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('members.index') }}" submitLabel="Perbarui" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
