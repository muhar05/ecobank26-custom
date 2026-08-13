<x-layouts.dashboard title="Edit Nasabah Bank Sampah">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('bank-sampah.customers.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Edit Nasabah Bank Sampah</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ubah profil nasabah {{ $customer->customer_code }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 transition-colors duration-300">
            <form method="POST" action="{{ route('bank-sampah.customers.update', $customer) }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $customer->name) }}" class="mt-1 block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-slate-950 dark:text-slate-50 transition" placeholder="Nama lengkap nasabah" required>
                            @error('name')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nomor Telepon</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}" class="mt-1 block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-slate-950 dark:text-slate-50 transition" placeholder="Contoh: 08123456789">
                            @error('phone')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status Nasabah</label>
                            <select name="status" id="status" class="mt-1 block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-slate-950 dark:text-slate-50 transition">
                                <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Active (Aktif)</option>
                                <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactive (Nonaktif)</option>
                            </select>
                            @error('status')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Alamat Lengkap</label>
                        <textarea name="address" id="address" rows="3" class="mt-1 block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-slate-950 dark:text-slate-50 transition" placeholder="Alamat lengkap nasabah">{{ old('address', $customer->address) }}</textarea>
                        @error('address')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                        <a href="{{ route('bank-sampah.customers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Batal
                        </a>
                        <button type="submit" class="bg-emerald-600 dark:bg-emerald-500 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
