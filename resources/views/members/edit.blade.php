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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-field-group label="Tanggal Lahir" name="birth_date">
                            <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date', $member->birth_date ? $member->birth_date->format('Y-m-d') : '') }}" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </x-field-group>

                        <x-field-group label="Jenis Kelamin" name="gender">
                            <select name="gender" id="gender" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" {{ old('gender', $member->gender) === 'Laki-laki' || old('gender', $member->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('gender', $member->gender) === 'Perempuan' || old('gender', $member->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </x-field-group>
                    </div>
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
                    @if(auth()->user()->hasAnyRole(['admin_rt', 'admin_rw', 'bendahara', 'bendahara_rw']))
                        <div class="p-3.5 bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-300 rounded-xl text-xs border border-amber-200/60 dark:border-amber-900/40">
                            <strong>PENTING:</strong> Data warga operasional RT wajib terhubung dengan Kartu Keluarga.
                        </div>
                    @endif

                    <x-field-group label="Kartu Keluarga (KK) {{ auth()->user()->hasAnyRole(['admin_rt', 'admin_rw', 'bendahara', 'bendahara_rw']) ? '*' : '' }}" name="kk_id" helper="{{ auth()->user()->hasAnyRole(['admin_rt', 'admin_rw', 'bendahara', 'bendahara_rw']) ? 'Wajib terhubung dengan KK.' : 'Opsional (Hubungkan warga ke KK)' }}">
                        <select name="kk_id" id="kk_id" {{ auth()->user()->hasAnyRole(['admin_rt', 'admin_rw', 'bendahara', 'bendahara_rw']) ? 'required' : '' }} class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Pilih Kartu Keluarga (KK)</option>
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
