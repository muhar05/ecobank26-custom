<x-layouts.dashboard title="Edit Nasabah Bank Sampah">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('bank-sampah.customers.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Edit Nasabah Bank Sampah</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ubah profil nasabah atau sambungan warga untuk {{ $customer->customer_code }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 transition-colors duration-300">
            <form method="POST" action="{{ route('bank-sampah.customers.update', $customer) }}" x-data="{ 
                mode: '{{ old('mode', $customer->member_id ? 'existing' : 'manual') }}',
                members: @js($members),
                selectedMemberId: '{{ old('member_id', $customer->member_id) }}',
                name: '{{ old('name', $customer->name) }}',
                phone: '{{ old('phone', $customer->phone) }}',
                address: '{{ old('address', $customer->address) }}',
                updateFromMember() {
                    if (this.mode === 'existing' && this.selectedMemberId) {
                        const member = this.members.find(m => m.id == this.selectedMemberId);
                        if (member) {
                            this.name = member.name;
                            this.phone = member.phone || '';
                            this.address = member.address || '';
                        }
                    }
                }
            }" x-init="updateFromMember()">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    {{-- Mode Selector --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Metode Pendaftaran</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer focus:outline-none transition"
                                   :class="mode === 'existing' ? 'border-emerald-500 bg-emerald-50/20 dark:bg-emerald-950/10' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                                <input type="radio" name="mode" value="existing" x-model="mode" @change="updateFromMember()" class="sr-only">
                                <span class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Hubungkan Warga
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pilih dari profil warga existing</span>
                            </label>

                            <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer focus:outline-none transition"
                                   :class="mode === 'manual' ? 'border-emerald-500 bg-emerald-50/20 dark:bg-emerald-950/10' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                                <input type="radio" name="mode" value="manual" x-model="mode" @change="selectedMemberId = ''" class="sr-only">
                                <span class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Nasabah Manual
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 mt-1">Input nama dan kontak secara manual</span>
                            </label>
                        </div>
                    </div>

                    {{-- Form Mode: Existing --}}
                    <div x-show="mode === 'existing'" x-transition class="space-y-4">
                        <div>
                            <label for="member_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Pilih Warga / Member</label>
                            <select name="member_id" id="member_id" x-model="selectedMemberId" @change="updateFromMember()" class="mt-1 block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-slate-950 dark:text-slate-50 transition">
                                <option value="">-- Pilih Warga --</option>
                                <template x-for="m in members" :key="m.id">
                                    <option :value="m.id" x-text="m.name + (m.member_code ? ' (' + m.member_code + ')' : '')" :selected="m.id == selectedMemberId"></option>
                                </template>
                            </select>
                            @error('member_id')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl space-y-2.5">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">Data Warga Terdeteksi</p>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">Nama:</span>
                                <span class="col-span-2 font-semibold text-slate-800 dark:text-slate-200" x-text="name || '-'"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">No HP:</span>
                                <span class="col-span-2 font-semibold text-slate-800 dark:text-slate-200" x-text="phone || '-'"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">Alamat:</span>
                                <span class="col-span-2 font-semibold text-slate-800 dark:text-slate-200" x-text="address || '-'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Form Mode: Manual --}}
                    <div x-show="mode === 'manual'" x-transition class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap</label>
                            <input type="text" name="name" id="name" x-model="name" class="mt-1 block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-slate-950 dark:text-slate-50 transition" placeholder="Masukkan nama lengkap nasabah">
                            @error('name')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300">No HP / Kontak</label>
                                <input type="text" name="phone" id="phone" x-model="phone" class="mt-1 block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-slate-950 dark:text-slate-50 transition" placeholder="Contoh: 08123456789">
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
                            <textarea name="address" id="address" x-model="address" rows="3" class="mt-1 block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-slate-950 dark:text-slate-50 transition" placeholder="Alamat lengkap nasabah"></textarea>
                            @error('address')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div x-show="mode === 'existing'">
                        {{-- Hidden inputs to submit for existing mode --}}
                        <input type="hidden" name="name" :value="name">
                        <input type="hidden" name="phone" :value="phone">
                        <input type="hidden" name="address" :value="address">
                        <input type="hidden" name="status" value="active">
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
