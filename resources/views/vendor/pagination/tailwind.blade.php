@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-1">
        {{-- Info pagination teks ramah --}}
        <div class="text-body-sm text-text-muted text-center sm:text-left select-none">
            @if ($paginator->total() > 0)
                Menampilkan
                <span class="font-bold text-text">{{ number_format($paginator->firstItem(), 0, ',', '.') }}</span>
                sampai
                <span class="font-bold text-text">{{ number_format($paginator->lastItem(), 0, ',', '.') }}</span>
                dari
                <span class="font-bold text-text">{{ number_format($paginator->total(), 0, ',', '.') }}</span>
                data
            @else
                Tidak ada data untuk ditampilkan
            @endif
        </div>

        {{-- Kontrol tombol navigasi --}}
        <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-radius-md border border-border-subtle bg-surface-muted/40 text-text-muted/40 text-body-sm font-medium cursor-not-allowed select-none min-h-[38px]">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-radius-md border border-border bg-surface text-text hover:bg-surface-muted hover:border-primary/40 text-body-sm font-semibold transition-subtle shadow-subtle min-h-[38px]">
                    <svg class="w-4 h-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </a>
            @endif

            {{-- Nomor Halaman (Desktop & Tablet) --}}
            <div class="hidden sm:inline-flex items-center gap-1">
                @foreach ($elements as $element)
                    {{-- Tiga titik separator "..." --}}
                    @if (is_string($element))
                        <span aria-disabled="true" class="px-2.5 py-2 text-text-muted text-body-sm font-bold select-none min-h-[38px] flex items-center">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Kumpulan array link halaman --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex items-center justify-center px-3.5 py-2 rounded-radius-md bg-primary text-white font-bold text-body-sm border border-primary shadow-subtle select-none min-w-[38px] min-h-[38px]">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center px-3.5 py-2 rounded-radius-md border border-border bg-surface text-text hover:bg-surface-muted hover:border-primary/40 text-body-sm font-semibold transition-subtle shadow-subtle min-w-[38px] min-h-[38px]">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Halaman Aktif saat di Layar Mobile Sangat Kecil (< sm) --}}
            <div class="sm:hidden inline-flex items-center px-3 py-2 rounded-radius-md border border-border bg-surface text-body-sm font-semibold text-text min-h-[38px]">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </div>

            {{-- Tombol Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-radius-md border border-border bg-surface text-text hover:bg-surface-muted hover:border-primary/40 text-body-sm font-semibold transition-subtle shadow-subtle min-h-[38px]">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-4 h-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-radius-md border border-border-subtle bg-surface-muted/40 text-text-muted/40 text-body-sm font-medium cursor-not-allowed select-none min-h-[38px]">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
