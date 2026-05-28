<x-layouts.dashboard title="Import Data Kartu Keluarga (KK)">
    <div class="space-y-6">
        <x-form-card title="Import Data Kartu Keluarga (KK)" description="Upload file Excel (.xlsx) atau CSV (.csv) untuk meng-import data Kartu Keluarga secara massal.">
            
            @if(session('import_result'))
                @php $r = session('import_result'); @endphp
                @if($r['success'])
                    <div class="mx-6 sm:mx-8 mt-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-5">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-800/50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-emerald-900 dark:text-emerald-100">Import Berhasil</h4>
                                <div class="mt-2 text-sm text-emerald-700 dark:text-emerald-300 space-y-1">
                                    <p>✓ {{ $r['created'] }} data Kartu Keluarga baru ditambahkan.</p>
                                </div>
                                @if(isset($r['stats']))
                                    <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 bg-white/50 dark:bg-slate-900/40 p-3 rounded-lg border border-emerald-100 dark:border-emerald-900/40 text-xs">
                                        <div><span class="text-slate-500 block">Total Baris:</span><strong class="text-slate-800 dark:text-slate-200">{{ $r['stats']['total'] }}</strong></div>
                                        <div><span class="text-slate-500 block">Berhasil:</span><strong class="text-emerald-600 dark:text-emerald-400">{{ $r['stats']['success'] }}</strong></div>
                                        <div><span class="text-slate-500 block">Gagal:</span><strong class="text-rose-600">{{ $r['stats']['failed'] }}</strong></div>
                                        <div><span class="text-slate-500 block">Duplikat:</span><strong class="text-amber-600">{{ $r['stats']['duplicates'] }}</strong></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mx-6 sm:mx-8 mt-6 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl p-5">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-800/50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-rose-900 dark:text-rose-100">Import Gagal</h4>
                                <p class="mt-1 text-xs text-rose-700 dark:text-rose-300">
                                    Ditemukan {{ count($r['errors']) }} kesalahan data. Transaksi database dibatalkan seluruhnya demi keselamatan data Anda. Silakan unduh detail kesalahan di bawah ini, perbaiki, dan unggah ulang.
                                </p>
                                @if(isset($r['stats']))
                                    <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 bg-white/50 dark:bg-slate-900/40 p-3 rounded-lg border border-rose-100 dark:border-rose-900/40 text-xs">
                                        <div><span class="text-slate-500 block">Total Baris:</span><strong class="text-slate-800 dark:text-slate-200">{{ $r['stats']['total'] }}</strong></div>
                                        <div><span class="text-slate-500 block">Berhasil:</span><strong class="text-emerald-600 dark:text-emerald-400">{{ $r['stats']['success'] }}</strong></div>
                                        <div><span class="text-slate-500 block">Gagal:</span><strong class="text-rose-600">{{ $r['stats']['failed'] }}</strong></div>
                                        <div><span class="text-slate-500 block">Duplikat:</span><strong class="text-amber-600">{{ $r['stats']['duplicates'] }}</strong></div>
                                    </div>
                                @endif
                                @if($r['has_failed_download'])
                                    <div class="mt-4">
                                        <a href="{{ route('kks.import.failed-download') }}" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-4 py-2 rounded-lg shadow transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Download File Error (Alasan Gagal per Baris)
                                        </a>
                                    </div>
                                @endif
                                <details class="mt-4 bg-white/50 dark:bg-black/20 rounded-lg p-3">
                                    <summary class="text-xs font-semibold text-rose-800 dark:text-rose-300 cursor-pointer">Lihat Ringkasan Error</summary>
                                    <ul class="mt-2 text-xs text-rose-600 dark:text-rose-400 space-y-1 pl-4 list-disc max-h-60 overflow-y-auto">
                                        @foreach($r['errors'] as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <form method="POST" action="{{ route('kks.import.store') }}" enctype="multipart/form-data">
                @csrf
                
                <x-form-section title="1. Download Template Excel" description="Unduh template Excel dengan multi-sheet (Sheet 1: Data, Sheet 2: Petunjuk).">
                    <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 shadow-sm flex items-center justify-center border border-slate-200 dark:border-slate-600">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">template_kartu_keluarga.xlsx</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Dilengkapi format header bold, indikator kolom wajib, dan sheet petunjuk pengisian.</p>
                            </div>
                        </div>
                        <a href="{{ route('kks.import.template') }}" class="inline-flex items-center justify-center bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition px-4 py-2 rounded-lg text-sm font-semibold shadow-sm">
                            Download
                        </a>
                    </div>
                </x-form-section>

                <x-form-section title="2. Upload File Excel / CSV" description="Pastikan format kolom sesuai dengan template yang diunduh.">
                    @error('file')
                        <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl flex items-start gap-3">
                            <svg class="w-5 h-5 text-rose-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-medium text-rose-800 dark:text-rose-300">{{ $message }}</p>
                        </div>
                    @enderror

                    <div x-data="{ fileName: '', dragging: false }" class="relative">
                        <div 
                            @dragover.prevent="dragging = true" 
                            @dragleave.prevent="dragging = false" 
                            @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $refs.fileInput.files[0].name"
                            :class="dragging ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-slate-300 dark:border-slate-700 hover:border-emerald-400 dark:hover:border-emerald-500 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                            class="flex justify-center rounded-xl border-2 border-dashed px-6 py-12 transition-colors cursor-pointer"
                            @click="$refs.fileInput.click()">
                            
                            <div class="text-center">
                                <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4 transition-transform group-hover:scale-110">
                                    <svg class="h-7 w-7 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="17 8 12 3 7 8" />
                                        <line x1="12" y1="3" x2="12" y2="15" />
                                    </svg>
                                </div>
                                <div class="flex text-sm leading-6 text-slate-600 dark:text-slate-400 justify-center">
                                    <span class="relative font-semibold text-emerald-600 dark:text-emerald-400">
                                        Pilih file Excel / CSV
                                    </span>
                                    <span class="pl-1">atau drag & drop kesini</span>
                                </div>
                                <p class="text-xs leading-5 text-slate-500 dark:text-slate-400 mt-2">Mendukung format .xlsx, .csv (Maksimal 1000 baris / 2MB)</p>
                                <div class="mt-3" x-show="fileName">
                                    <span class="font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-100/80 dark:bg-emerald-950 px-3 py-1.5 rounded-full text-xs" x-text="fileName"></span>
                                </div>
                            </div>
                            <input type="file" x-ref="fileInput" name="file" accept=".xlsx,.csv" required class="sr-only" @change="fileName = $refs.fileInput.files[0].name">
                        </div>
                    </div>
                </x-form-section>

                <x-form-actions cancelUrl="{{ route('kks.index') }}" submitLabel="Import Kartu Keluarga" />
            </form>
        </x-form-card>

        {{-- Import History Section --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Riwayat Import Terakhir (KK)</h3>
                <p class="text-xs text-slate-500 mt-1">Daftar file yang diimport oleh RT/RW selama 10 sesi terakhir</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50/50 dark:bg-slate-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama File</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Oleh</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Baris</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Berhasil</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Gagal/Duplikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($history as $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 text-xs font-medium text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ $item->created_at->setTimezone('Asia/Jakarta')->format('d M Y H:i:s') }}
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-slate-900 dark:text-white max-w-xs truncate">
                                    {{ $item->filename }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400">
                                    {{ $item->user->name ?? 'System' }}
                                </td>
                                <td class="px-6 py-4 text-xs text-center font-bold text-slate-800 dark:text-slate-200">
                                    {{ $item->total_rows }}
                                </td>
                                <td class="px-6 py-4 text-xs text-center">
                                    <span class="px-2 py-1 rounded bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold">
                                        {{ $item->total_success }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-center">
                                    <span class="px-2 py-1 rounded bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 font-bold">
                                        {{ $item->total_failed + $item->total_duplicates }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-xs text-slate-500">
                                    Belum ada riwayat proses import data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
