<x-layouts.dashboard title="Edit Pengepul">
    <div class="max-w-2xl">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-300">
            <form method="POST" action="{{ route('bank-sampah.collectors.update', $collector) }}">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $collector->name) }}" required class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Telepon</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $collector->phone) }}" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label for="address" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Alamat</label>
                    <input type="text" name="address" id="address" value="{{ old('address', $collector->address) }}" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">Perbarui</button>
                    <a href="{{ route('bank-sampah.collectors.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
