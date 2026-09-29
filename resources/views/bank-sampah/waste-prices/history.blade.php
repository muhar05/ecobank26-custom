<x-layouts.dashboard title="Riwayat Harga Agregator">
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Riwayat Perubahan Harga Agregator</span>
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Audit log kronologis perubahan harga jual sampah ke agregator/pengepul
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('bank-sampah.devices.index') }}" class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 px-3.5 py-2 rounded-lg text-xs font-semibold shadow-xs hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Perangkat Saya
                </a>
                <a href="{{ route('bank-sampah.waste-prices.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3.5 py-2 rounded-lg shadow-xs transition">
                    &larr; Kembali ke Harga Sampah
                </a>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-4">
            <form action="{{ route('bank-sampah.waste-prices.history') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Agregator</label>
                    <select name="collector_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                        <option value="">Semua Agregator</option>
                        @foreach($collectors as $col)
                            <option value="{{ $col->id }}" {{ $collectorId == $col->id ? 'selected' : '' }}>{{ $col->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Material / Kategori</label>
                    <select name="waste_category_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                        <option value="">Semua Material</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $wasteCategoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sumber Perubahan</label>
                    <select name="source" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                        <option value="">Semua Sumber</option>
                        <option value="MANUAL" {{ $source === 'MANUAL' ? 'selected' : '' }}>Manual (Aplikasi)</option>
                        <option value="IMPORT" {{ $source === 'IMPORT' ? 'selected' : '' }}>Import Excel</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 text-white text-xs font-bold py-2 px-3 rounded-lg transition shadow-xs">
                        Filter Riwayat
                    </button>
                    @if($collectorId || $wasteCategoryId || $source)
                        <a href="{{ route('bank-sampah.waste-prices.history') }}" class="text-xs text-slate-500 hover:text-rose-600 px-2 py-1 font-semibold">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Riwayat Perubahan --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 w-44">Waktu Perubahan</th>
                            <th class="py-3.5 px-4">Agregator</th>
                            <th class="py-3.5 px-4">Material</th>
                            <th class="py-3.5 px-4 text-right">Perubahan Harga</th>
                            <th class="py-3.5 px-4 text-center w-24">Sumber</th>
                            <th class="py-3.5 px-4">Oleh Petugas</th>
                            <th class="py-3.5 px-4">Perangkat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @forelse($histories as $h)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                {{-- Waktu --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $h->changed_at ? $h->changed_at->format('d M Y H:i') : '-' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $h->changed_at ? $h->changed_at->diffForHumans() : '' }}
                                    </div>
                                </td>

                                {{-- Agregator --}}
                                <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                    {{ $h->collector->name ?? '-' }}
                                </td>

                                {{-- Material --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">
                                        {{ $h->wasteCategory->name ?? '-' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ $h->wasteCategory->code ?? '' }}
                                    </div>
                                </td>

                                {{-- Perubahan Harga --}}
                                <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono">
                                    @if($h->old_price !== null)
                                        <span class="text-slate-400 line-through mr-1 text-[11px]">
                                            Rp {{ number_format($h->old_price, 0, ',', '.') }}
                                        </span>
                                        &rarr;
                                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 ml-1">
                                            Rp {{ number_format($h->new_price, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 mr-1.5">
                                            Awal
                                        </span>
                                        <span class="font-extrabold text-slate-900 dark:text-white">
                                            Rp {{ number_format($h->new_price, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Sumber --}}
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($h->source === 'MANUAL')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            MANUAL
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            IMPORT
                                        </span>
                                    @endif
                                </td>

                                {{-- Petugas --}}
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-700 dark:text-slate-300">
                                    {{ $h->changedBy->name ?? 'Sistem' }}
                                </td>

                                {{-- Perangkat --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($h->adminDevice)
                                        <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span>{{ $h->adminDevice->device_name ?: 'Perangkat Tanpa Nama' }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            {{ $h->adminDevice->browser }} &bull; {{ $h->adminDevice->platform }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    Belum ada catatan riwayat perubahan harga agregator.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($histories->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                    {{ $histories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
