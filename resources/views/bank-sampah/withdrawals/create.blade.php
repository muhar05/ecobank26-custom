<x-layouts.dashboard title="Catat Penarikan Saldo">
    <x-form-card title="Catat Penarikan Saldo" description="Proses penarikan saldo tabungan nasabah bank sampah.">
        <form method="POST" action="{{ route('bank-sampah.withdrawals.store') }}">
            @csrf
            <x-form-section title="Data Nasabah">
                <div class="space-y-5">
                    <x-field-group label="Nasabah" name="waste_customer_id" required>
                        <select name="waste_customer_id" id="waste_customer_id" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Nasabah --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('waste_customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->customer_code }})</option>
                            @endforeach
                        </select>
                    </x-field-group>

                    <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3">
                        <div class="flex gap-2">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                            <div>
                                <p class="text-xs font-medium text-amber-800 dark:text-amber-300">Syarat Penarikan</p>
                                <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">Nasabah minimal harus memiliki 2 kali setoran sebelum bisa menarik saldo. Jumlah penarikan tidak boleh melebihi saldo.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-form-section>

            <x-form-section title="Detail Penarikan">
                <div class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-field-group label="Jumlah (Rp)" name="amount" required>
                            <x-rupiah-input name="amount" :value="old('amount')" required />
                        </x-field-group>

                        <x-field-group label="Tanggal" name="date" required>
                            <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </x-field-group>
                    </div>

                    <x-field-group label="Catatan" name="notes">
                        <input type="text" name="notes" id="notes" value="{{ old('notes') }}" placeholder="Opsional" class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </x-field-group>
                </div>
            </x-form-section>

            <x-form-actions cancelUrl="{{ route('bank-sampah.withdrawals.index') }}" submitLabel="Simpan Penarikan" />
        </form>
    </x-form-card>
</x-layouts.dashboard>
