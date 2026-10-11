<x-layouts.dashboard title="Riwayat Harga Agregator">
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-text flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Riwayat Perubahan Harga Agregator</span>
                </h2>
                <p class="text-sm text-text-muted mt-1">
                    Audit log kronologis perubahan harga jual sampah ke agregator/pengepul
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('bank-sampah.devices.index') }}" class="inline-flex items-center gap-1.5 bg-surface border border-border text-text px-3.5 py-2 rounded-radius-md text-xs font-semibold shadow-xs hover:bg-surface-muted transition">
                    <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Perangkat Saya
                </a>
                <a href="{{ route('bank-sampah.waste-prices.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-text-muted hover:text-text bg-surface border border-border px-3.5 py-2 rounded-radius-md shadow-xs transition">
                    &larr; Kembali ke Harga Sampah
                </a>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-surface rounded-radius-lg shadow-sm border border-border p-4">
            <form action="{{ route('bank-sampah.waste-prices.history') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-caption font-bold text-text-muted uppercase tracking-wider mb-1">Agregator</label>
                    <select name="collector_id" class="w-full text-xs rounded-radius-md border-border bg-surface text-text">
                        <option value="">Semua Agregator</option>
                        @foreach($collectors as $col)
                            <option value="{{ $col->id }}" {{ $collectorId == $col->id ? 'selected' : '' }}>{{ $col->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-caption font-bold text-text-muted uppercase tracking-wider mb-1">Material / Kategori</label>
                    <x-ui.combobox
                        name="waste_category_id"
                        :options="$categories"
                        :value="$wasteCategoryId"
                        placeholder="Semua Material"
                        searchPlaceholder="Cari material..."
                        allowClear
                        compact
                    />
                </div>

                <div>
                    <label class="block text-caption font-bold text-text-muted uppercase tracking-wider mb-1">Sumber Perubahan</label>
                    <x-ui.filter-pills
                        name="source"
                        :value="$source"
                        :options="[
                            ['value' => '', 'label' => 'Semua'],
                            ['value' => 'MANUAL', 'label' => 'Manual'],
                            ['value' => 'IMPORT', 'label' => 'Import'],
                        ]"
                        autoSubmit
                    />
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 bg-surface text-text border border-border hover:bg-surface-muted text-xs font-bold py-2 px-3 rounded-radius-md transition shadow-xs">
                        Filter Riwayat
                    </button>
                    @if($collectorId || $wasteCategoryId || $source)
                        <a href="{{ route('bank-sampah.waste-prices.history') }}" class="text-xs text-text-muted hover:text-danger px-2 py-1 font-semibold">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Riwayat Perubahan --}}
        <div id="table-section" class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden scroll-mt-20">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border text-left text-xs">
                    <thead class="bg-surface-muted/60 font-bold text-text-muted uppercase tracking-wider text-caption">
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
                    <tbody class="divide-y divide-border-subtle font-medium">
                        @forelse($histories as $h)
                            <tr class="hover:bg-surface-muted/60 transition">
                                {{-- Waktu --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-text">
                                        {{ $h->changed_at ? $h->changed_at->format('d M Y H:i') : '-' }}
                                    </div>
                                    <div class="text-caption text-text-muted mt-0.5">
                                        {{ $h->changed_at ? $h->changed_at->diffForHumans() : '' }}
                                    </div>
                                </td>

                                {{-- Agregator --}}
                                <td class="py-3.5 px-4 font-bold text-text whitespace-nowrap">
                                    {{ $h->collector->name ?? '-' }}
                                </td>

                                {{-- Material --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-text">
                                        {{ $h->wasteCategory->name ?? '-' }}
                                    </div>
                                    <div class="text-caption text-text-muted font-mono">
                                        {{ $h->wasteCategory->code ?? '' }}
                                    </div>
                                </td>

                                {{-- Perubahan Harga --}}
                                <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono">
                                    @if($h->old_price !== null)
                                        <span class="text-text-muted line-through mr-1 text-caption">
                                            Rp {{ number_format($h->old_price, 0, ',', '.') }}
                                        </span>
                                        &rarr;
                                        <span class="font-extrabold text-primary ml-1">
                                            Rp {{ number_format($h->new_price, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="mr-1.5"><x-ui.badge variant="info" size="sm">Awal</x-ui.badge></span>
                                        <span class="font-extrabold text-text">
                                            Rp {{ number_format($h->new_price, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Sumber --}}
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($h->source === 'MANUAL')
                                        <x-ui.badge variant="neutral" size="sm">MANUAL</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="info" size="sm">IMPORT</x-ui.badge>
                                    @endif
                                </td>

                                {{-- Petugas --}}
                                <td class="py-3.5 px-4 whitespace-nowrap text-text">
                                    {{ $h->changedBy->name ?? 'Sistem' }}
                                </td>

                                {{-- Perangkat --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($h->adminDevice)
                                        <div class="font-bold text-text flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-primary"></span>
                                            <span>{{ $h->adminDevice->device_name ?: 'Perangkat Tanpa Nama' }}</span>
                                        </div>
                                        <div class="text-caption text-text-muted mt-0.5">
                                            {{ $h->adminDevice->browser }} &bull; {{ $h->adminDevice->platform }}
                                        </div>
                                    @else
                                        <span class="text-text-muted italic text-caption">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-text-muted">
                                    Belum ada catatan riwayat perubahan harga agregator.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($histories->hasPages())
                <div class="p-4 border-t border-border bg-surface-muted/50 dark:bg-surface-muted/40">
                    {{ $histories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
