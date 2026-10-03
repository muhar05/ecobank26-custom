<x-layouts.dashboard title="Timbangan Kami - SAL-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}">
    <div class="space-y-6 max-w-4xl mx-auto pb-12">
        {{-- Action Bar (Hidden on Print) --}}
        <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border-subtle pb-4">
            <div>
                <x-ui.page-header title="Laporan Timbangan Agregator" description="Tinjau dan cetak hasil timbangan penjualan yang sudah tersimpan untuk diserahkan ke agregator">
                    <x-slot:breadcrumbs>
                        <a href="{{ route('bank-sampah.dashboard') }}" class="hover:text-text transition-subtle">Dashboard</a>
                        <span>&rarr;</span>
                        <a href="{{ route('bank-sampah.sales.index') }}" class="hover:text-text transition-subtle">Penjualan</a>
                        <span>&rarr;</span>
                        <span class="text-text">SAL-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </x-slot:breadcrumbs>
                </x-ui.page-header>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-center shrink-0">
                <x-ui.button variant="outline" size="sm" onclick="window.location='{{ route('bank-sampah.sales.index') }}'">
                    &larr; Kembali ke Daftar
                </x-ui.button>
                <x-ui.button variant="primary" size="md" onclick="window.print()">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </x-ui.button>
            </div>
        </div>

        {{-- Success Flash Alert --}}
        @if(session('success'))
            <div class="no-print p-4 rounded-radius-md bg-success-light border border-success/30 flex items-center justify-between gap-3 text-success-text">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-body-sm font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="window.print()" class="text-caption font-bold text-success underline cursor-pointer">
                    Cetak Sekarang &rarr;
                </button>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- REPORT: TIMBANGAN KAMI BOX                                --}}
        {{-- ========================================================= --}}
        <div id="timbangan-kami-box" class="p-6 sm:p-8 rounded-radius-lg border border-border bg-surface shadow-subtle space-y-6 print:p-0 print:border-none print:shadow-none">
            {{-- Report Header --}}
            <div class="border-b-2 border-text pb-4 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div>
                    <h1 class="text-heading-lg font-black text-text uppercase tracking-tight">Timbangan Kami Box</h1>
                    <p class="text-caption font-semibold text-text-muted mt-0.5">Bukti Muat &amp; Timbangan Sampah Terjual</p>
                </div>

                <div class="sm:text-right space-y-0.5 font-mono text-body-sm">
                    <div class="text-caption font-bold text-text-muted uppercase tracking-wider">Tanggal:</div>
                    <div class="text-body font-bold text-text">{{ $sale->date->translatedFormat('d F Y') }}</div>
                    <div class="text-caption text-text-muted">Kode: SAL-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>

            {{-- Metadata Agregator --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-body-sm p-4 rounded-radius-md bg-surface-muted/40 border border-border-subtle">
                <div>
                    <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">Mitra Agregator / Pembeli</span>
                    <span class="text-body font-bold text-text block mt-0.5">{{ $sale->collector->name ?? '-' }}</span>
                    @if($sale->collector && ($sale->collector->phone || $sale->collector->address))
                        <span class="text-caption text-text-muted block mt-0.5">
                            {{ $sale->collector->phone ?? '' }} {{ $sale->collector->address ? '• ' . $sale->collector->address : '' }}
                        </span>
                    @endif
                </div>

                <div>
                    <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">Catatan Muatan</span>
                    <span class="text-body-sm text-text block mt-0.5 italic">
                        {{ $sale->notes ?: '-' }}
                    </span>
                </div>
            </div>

            {{-- Table Hasil Penjualan --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse border border-border">
                    <thead>
                        <tr class="border-b border-border bg-surface-muted/60 text-caption font-bold text-text-muted uppercase tracking-wider">
                            <th class="py-3 px-3 w-12 text-center border-r border-border">No</th>
                            <th class="py-3 px-4 border-r border-border">Jenis Sampah</th>
                            <th class="py-3 px-4 text-right border-r border-border">Harga</th>
                            <th class="py-3 px-4 text-right border-r border-border">Berat</th>
                            <th class="py-3 px-4 text-right">Total Harga</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @foreach($sale->details as $index => $detail)
                            <tr class="hover:bg-surface-muted/30 transition-subtle">
                                <td class="py-3 px-3 text-center font-mono text-body-sm text-text-muted border-r border-border">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3 px-4 border-r border-border">
                                    <span class="font-bold text-text block">{{ $detail->wasteCategory->name ?? '-' }}</span>
                                    @if($detail->wasteCategory && $detail->wasteCategory->code)
                                        <span class="font-mono text-caption text-text-muted block">{{ $detail->wasteCategory->code }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-body-sm text-text border-r border-border">
                                    Rp {{ number_format($detail->price_per_unit, 0, ',', '.') }} <span class="text-caption text-text-muted font-normal">/ {{ $detail->wasteCategory->unit ?? 'kg' }}</span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-body-sm text-text border-r border-border">
                                    {{ number_format($detail->weight, 2, ',', '.') }} {{ $detail->wasteCategory->unit ?? 'kg' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-body text-text">
                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Di bawah tabel: Total Berat & Total Harga --}}
            <div class="border-t-2 border-border pt-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center bg-surface-muted/30 p-4 rounded-radius-md border border-border">
                    <div>
                        <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">Total Berat</span>
                        <span class="font-mono text-heading-md font-extrabold text-primary">
                            {{ number_format($sale->details->sum('weight'), 2, ',', '.') }} <span class="text-body font-normal text-text-muted">kg</span>
                        </span>
                    </div>

                    <div class="sm:text-right">
                        <span class="text-caption font-bold uppercase tracking-wider text-text-muted block">Total Harga</span>
                        <span class="font-mono text-heading-lg font-black text-text">
                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Signature Block for Agregator Receipt --}}
            <div class="pt-8 grid grid-cols-2 gap-8 text-center text-body-sm print:pt-6">
                <div class="space-y-16">
                    <p class="text-caption font-bold text-text-muted uppercase tracking-wider">Diserahkan Oleh (Bank Sampah)</p>
                    <div>
                        <div class="border-b border-border w-48 mx-auto"></div>
                        <p class="text-caption text-text-muted mt-1">Petugas Timbangan</p>
                    </div>
                </div>

                <div class="space-y-16">
                    <p class="text-caption font-bold text-text-muted uppercase tracking-wider">Diterima Oleh (Agregator)</p>
                    <div>
                        <div class="border-b border-border w-48 mx-auto"></div>
                        <p class="text-caption text-text font-semibold mt-1">{{ $sale->collector->name ?? 'Sopir / Perwakilan' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clean Print Stylesheet --}}
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .no-print, header, aside, nav, #eco-nav-progress {
                display: none !important;
            }
            #app-content {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
            }
            #timbangan-kami-box {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                width: 100% !important;
            }
            table {
                border: 1px solid #111 !important;
            }
            th, td {
                border: 1px solid #111 !important;
            }
        }
    </style>
</x-layouts.dashboard>
