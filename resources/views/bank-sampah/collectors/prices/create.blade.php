<x-layouts.dashboard title="Tambah Harga Lapak - {{ $collector->name }}">
    @php
        $materialOptions = $categories->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code,
            'unit' => $c->unit ?? 'kg',
            'group_name' => $c->wasteCategoryGroup->name ?? '',
        ])->values()->all();
    @endphp

    <div x-data="priceCreateWorkspace(@js($materialOptions), '{{ old('waste_category_id', '') }}', '{{ old('collector_price', '') }}')" class="space-y-6 max-w-5xl mx-auto pb-12">
        <x-ui.page-header title="Tambah Harga Lapak" description="Pilih satu material dari Master Material, lalu tentukan harga beli agregator untuk material tersebut.">
            <x-slot:breadcrumbs>
                <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.collectors.index') }}" class="hover:text-text transition-subtle">Agregator</a>
                <span>&rarr;</span>
                <a href="{{ route('bank-sampah.collectors.show', $collector) }}" class="hover:text-text transition-subtle">{{ $collector->name }}</a>
                <span>&rarr;</span>
                <span class="text-text">Tambah Harga</span>
            </x-slot:breadcrumbs>
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.collectors.show', $collector) }}'">
                    &larr; Kembali ke Detail Agregator
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @if($errors->any())
            <div class="p-4 rounded-radius-md bg-danger-light border border-danger/30 text-danger-text shadow-subtle">
                <p class="text-body-sm font-bold">Harga belum tersimpan. Periksa kembali isian berikut.</p>
                <ul class="text-body-sm space-y-1 list-disc list-inside mt-1.5 font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="p-4 sm:p-5 rounded-radius-lg bg-surface border border-border shadow-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-radius-md bg-primary-light text-primary flex items-center justify-center font-bold text-heading-sm shrink-0">
                    {{ strtoupper(substr($collector->name, 0, 1)) }}
                </div>
                <div>
                    <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">Agregator Tujuan</span>
                    <h2 class="text-heading-sm font-bold text-text mt-0.5">{{ $collector->name }}</h2>
                    <p class="text-caption text-text-muted mt-0.5">{{ $collector->phone ?: 'Tidak ada nomor telepon' }} &bull; {{ $collector->address ?: 'Alamat belum diatur' }}</p>
                </div>
            </div>
            <div class="px-3 py-1.5 rounded-radius-md bg-surface-muted border border-border text-text shrink-0">
                <span class="text-text-muted uppercase text-caption font-semibold block">Material Aktif</span>
                <span class="font-mono text-body-sm font-bold text-primary">{{ $collector->wastePrices()->count() }} Jenis</span>
            </div>
        </div>

        @if($categories->isEmpty())
            <x-ui.card variant="bordered">
                <div class="py-10 px-4 text-center flex flex-col items-center justify-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-surface-muted text-text-muted flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-body font-bold text-text">Semua material Master sudah memiliki harga lapak.</p>
                        <p class="text-body-sm text-text-muted mt-1 max-w-md mx-auto">
                            Tidak ada material tersisa untuk ditambahkan. Ubah harga existing dari halaman detail agregator bila diperlukan.
                        </p>
                    </div>
                    <x-ui.button variant="primary" size="md" onclick="window.location='{{ route('bank-sampah.collectors.show', $collector) }}'">
                        Kembali ke Detail Agregator
                    </x-ui.button>
                </div>
            </x-ui.card>
        @else
            <form method="POST" action="{{ route('bank-sampah.collectors.prices.store', $collector) }}" @submit="return validateBeforeSubmit()" class="space-y-6">
                @csrf
                <input type="hidden" name="waste_category_id" :value="selectedId">

                <x-ui.card variant="bordered" title="1. Pilih Material" description="Material berasal dari Master Material yang belum memiliki harga pada agregator ini. Ketik untuk mencari.">
                    <div class="space-y-3">
                        <div class="relative">
                            <input type="text"
                                   x-model="search"
                                   @input="highlightIndex = 0"
                                   @keydown.arrow-down.prevent="moveHighlight(1)"
                                   @keydown.arrow-up.prevent="moveHighlight(-1)"
                                   @keydown.enter.prevent="chooseHighlighted()"
                                   placeholder="Cari nama material atau kode (contoh: Kardus, PLS.01)..."
                                   autocomplete="off"
                                   role="combobox"
                                   aria-expanded="true"
                                   aria-label="Cari material"
                                   class="w-full text-body rounded-radius-md border border-border bg-surface text-text pl-10 pr-4 py-2.5 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <p class="text-caption text-text-muted">
                            Menampilkan <span class="font-mono font-bold text-text" x-text="filteredMaterials().length"></span>
                            dari <span class="font-mono font-bold text-text" x-text="materials.length"></span> material tersedia.
                        </p>

                        <div x-show="clientError === 'material'" class="p-3 rounded-radius-md bg-danger-light border border-danger/30 text-danger-text text-body-sm font-medium">
                            Pilih salah satu material dari daftar Master Material.
                        </div>

                        <div class="border border-border rounded-radius-md divide-y divide-border-subtle max-h-80 overflow-y-auto bg-surface" role="listbox" aria-label="Daftar material">
                            <template x-for="(mat, idx) in filteredMaterials()" :key="mat.id">
                                <button type="button"
                                        role="option"
                                        :aria-selected="String(selectedId) === String(mat.id)"
                                        @click="selectMaterial(mat)"
                                        :class="highlightIndex === idx ? 'bg-primary-light/50' : ''"
                                        class="w-full text-left p-3.5 flex items-center gap-3 transition-subtle cursor-pointer min-h-[44px] hover:bg-surface-muted/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                                    <span class="w-9 h-9 rounded-radius-md flex items-center justify-center font-bold text-body-sm shrink-0"
                                          :class="String(selectedId) === String(mat.id) ? 'bg-primary text-white' : 'bg-surface-muted text-text-muted'"
                                          x-text="mat.name.substr(0, 1).toUpperCase()"></span>
                                    <span class="flex-1 min-w-0">
                                        <span class="font-bold text-body-sm text-text block truncate" x-text="mat.name"></span>
                                        <span class="font-mono text-caption text-text-muted block truncate">
                                            <span x-text="mat.code || '-'"></span>
                                            <span x-show="mat.group_name" x-text="' · ' + mat.group_name"></span>
                                        </span>
                                    </span>
                                    <span class="font-mono text-caption uppercase px-2 py-0.5 rounded bg-surface-muted border border-border text-text font-semibold shrink-0" x-text="mat.unit"></span>
                                    <span x-show="String(selectedId) === String(mat.id)" class="text-primary shrink-0" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </button>
                            </template>
                            <template x-if="filteredMaterials().length === 0">
                                <div class="p-6 text-center space-y-1.5">
                                    <p class="text-body-sm font-bold text-text">Material tidak ditemukan.</p>
                                    <p class="text-caption text-text-muted">Coba kata kunci lain. Material baru tidak dibuat dari halaman ini, kelola lewat Master Material.</p>
                                </div>
                            </template>
                        </div>

                        <div x-show="selectedMaterial()" class="p-3.5 rounded-radius-md bg-primary-light/40 border border-primary/20 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <span class="text-caption font-bold uppercase tracking-wider text-primary block">Material Terpilih</span>
                                <span class="text-body-sm font-bold text-text" x-text="selectedMaterial() ? selectedMaterial().name : ''"></span>
                                <span class="font-mono text-caption text-text-muted" x-text="selectedMaterial() ? ' (' + (selectedMaterial().code || '-') + ')' : ''"></span>
                            </div>
                            <button type="button" @click="clearSelection()" class="text-body-sm font-semibold text-text-muted hover:text-text underline underline-offset-2 min-h-[44px] px-2 self-start sm:self-center cursor-pointer">
                                Ganti pilihan
                            </button>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card variant="bordered" title="2. Tentukan Harga Lapak" description="Harga beli agregator per satuan master. Satuan mengikuti Master Material dan tidak dapat diubah.">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <span class="text-body-sm font-semibold text-text block">Satuan Ukuran</span>
                            <div class="px-3 py-2.5 rounded-radius-md bg-surface-muted border border-border font-mono text-body font-semibold text-text" x-text="selectedMaterial() ? selectedMaterial().unit : '-'"></div>
                            <p class="text-caption text-text-muted">Mengikuti Master Material otomatis.</p>
                        </div>

                        <div class="space-y-1.5">
                            <x-input-label for="collector_price" value="Harga Lapak (Rp)" :required="true" />
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-mono text-body-sm font-bold text-text-muted pointer-events-none">Rp</span>
                                <input type="number"
                                       name="collector_price"
                                       id="collector_price"
                                       x-model="price"
                                       step="1"
                                       min="0"
                                       inputmode="numeric"
                                       placeholder="Contoh: 2500"
                                       required
                                       class="w-full text-body font-mono font-bold rounded-radius-md border border-border bg-surface text-text pl-11 pr-3 py-2.5 placeholder:text-text-muted placeholder:font-normal focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
                            </div>
                            <p class="text-caption text-text-muted">Masukkan angka tanpa titik atau koma. Contoh: 2500 untuk Rp 2.500.</p>
                            <p x-show="pricePreview()" class="text-body-sm font-semibold text-primary font-mono" x-text="pricePreview()"></p>
                            <p x-show="clientError === 'price'" class="text-body-sm font-medium text-danger">Harga lapak wajib diisi dengan angka 0 atau lebih.</p>
                            @error('collector_price')
                                <p class="text-body-sm font-medium text-danger">{{ $message }}</p>
                            @enderror
                            @error('waste_category_id')
                                <p class="text-body-sm font-medium text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 p-3.5 rounded-radius-md bg-surface-muted/60 border border-border-subtle text-body-sm text-text-muted leading-relaxed">
                        Harga pertama dicatat sebagai harga awal pada riwayat. Perubahan berikutnya dicatat dari harga lama ke harga baru. Harga yang disimpan ulang tanpa perubahan tidak menambah riwayat baru.
                    </div>
                </x-ui.card>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
                    <a href="{{ route('bank-sampah.collectors.show', $collector) }}"
                       class="inline-flex items-center justify-center px-4 py-2.5 rounded-radius-md text-body font-semibold text-text-muted hover:text-text hover:bg-surface-muted border border-transparent transition-subtle min-h-[44px]">
                        Batal
                    </a>
                    <button type="submit"
                            :disabled="submitting || !selectedId"
                            :class="(!selectedId || submitting) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-primary-hover cursor-pointer'"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-radius-md bg-primary text-white text-body font-bold shadow-subtle transition-subtle min-h-[44px]">
                        <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg x-show="submitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="submitting ? 'Menyimpan...' : 'Simpan Harga'"></span>
                    </button>
                </div>
            </form>
        @endif
    </div>

    <script>
        function priceCreateWorkspace(materials, oldCategoryId, oldPrice) {
            return {
                materials: materials || [],
                search: '',
                selectedId: oldCategoryId ? String(oldCategoryId) : '',
                price: oldPrice || '',
                highlightIndex: 0,
                clientError: '',
                submitting: false,

                filteredMaterials() {
                    const q = (this.search || '').toLowerCase().trim();
                    if (!q) return this.materials;
                    return this.materials.filter((m) =>
                        m.name.toLowerCase().includes(q) ||
                        (m.code && m.code.toLowerCase().includes(q)) ||
                        (m.group_name && m.group_name.toLowerCase().includes(q))
                    );
                },

                selectedMaterial() {
                    if (!this.selectedId) return null;
                    return this.materials.find((m) => String(m.id) === String(this.selectedId)) || null;
                },

                selectMaterial(mat) {
                    this.selectedId = String(mat.id);
                    this.clientError = '';
                },

                clearSelection() {
                    this.selectedId = '';
                    this.$nextTick(() => {
                        const input = document.querySelector('input[x-model="search"]');
                        if (input) input.focus();
                    });
                },

                moveHighlight(dir) {
                    const len = this.filteredMaterials().length;
                    if (!len) return;
                    this.highlightIndex = (this.highlightIndex + dir + len) % len;
                    this.scrollHighlightedIntoView();
                },

                chooseHighlighted() {
                    const list = this.filteredMaterials();
                    if (list[this.highlightIndex]) this.selectMaterial(list[this.highlightIndex]);
                },

                scrollHighlightedIntoView() {
                    this.$nextTick(() => {
                        const container = this.$el.querySelector('[role="listbox"]');
                        const target = container ? container.children[this.highlightIndex] : null;
                        if (target && target.scrollIntoView) target.scrollIntoView({ block: 'nearest' });
                    });
                },

                pricePreview() {
                    const n = Number(this.price);
                    if (this.price === '' || isNaN(n) || n < 0) return '';
                    return 'Rp ' + Math.round(n).toLocaleString('id-ID') + ' / ' + (this.selectedMaterial() ? this.selectedMaterial().unit : 'kg');
                },

                validateBeforeSubmit() {
                    this.clientError = '';
                    if (!this.selectedId) {
                        this.clientError = 'material';
                        return false;
                    }
                    const n = Number(this.price);
                    if (this.price === '' || isNaN(n) || n < 0) {
                        this.clientError = 'price';
                        return false;
                    }
                    this.submitting = true;
                    return true;
                },
            };
        }
    </script>
</x-layouts.dashboard>
