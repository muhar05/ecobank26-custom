<x-layouts.dashboard title="Tagihan Saya">
    <div class="space-y-8 pb-8 mx-auto">

        {{-- Header --}}
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-slate-100">Tagihan Saya</h2>
            <p class="text-base text-slate-600 dark:text-slate-400 mt-2">Daftar tagihan iuran wajib bulanan dan riwayat pembayaran Anda.</p>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
            <form method="GET" action="{{ route('warga.bills') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pilih Bulan</label>
                    <select name="month" class="w-full rounded-2xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 h-14 px-4 text-base font-medium focus:ring-emerald-500">
                        <option value="">Semua Bulan</option>
                        @foreach($months as $m => $name)
                            <option value="{{ $m }}" {{ ($monthFilter ?? '') == $m ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pilih Tahun</label>
                    <select name="year" class="w-full rounded-2xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 h-14 px-4 text-base font-medium focus:ring-emerald-500">
                        <option value="">Semua Tahun</option>
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ ($yearFilter ?? '') == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status</label>
                    <select name="status" class="w-full rounded-2xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 h-14 px-4 text-base font-medium focus:ring-emerald-500">
                        <option value="">Semua Status</option>
                        <option value="unpaid" {{ ($statusFilter ?? '') === 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                        <option value="partially_paid" {{ ($statusFilter ?? '') === 'partially_paid' ? 'selected' : '' }}>Dicicil</option>
                        <option value="paid" {{ ($statusFilter ?? '') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>
                <button type="submit" class="w-full h-14 bg-emerald-600 dark:bg-emerald-700 text-white rounded-2xl text-base font-bold hover:bg-emerald-700 dark:hover:bg-emerald-600 transition shadow-sm">
                    Tampilkan Tagihan
                </button>
            </form>
        </div>

        {{-- Bills List --}}
        <div class="space-y-6">
            @forelse($bills as $bill)
                @php
                    $isOverdue = $bill->due_date && $bill->due_date->isPast() && $bill->status !== 'paid';
                    // Warna badge disesuaikan agar kontrasnya bagus di light maupun dark mode
                    $statusColor = $bill->status === 'paid' 
                        ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300' 
                        : ($bill->status === 'partially_paid' 
                            ? 'bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300' 
                            : 'bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300');
                    $statusText = $bill->status === 'paid' ? 'Lunas' : ($bill->status === 'partially_paid' ? 'Dicicil' : 'Belum Lunas');
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-extrabold text-slate-900 dark:text-slate-100">{{ $bill->fundCategory->name }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-bold mt-1">{{ $months[$bill->month] }} {{ $bill->year }}</p>
                        </div>
                        <span class="px-4 py-2 rounded-full text-sm font-bold {{ $statusColor }}">
                            {{ $statusText }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Total Tagihan</p>
                            <p class="text-base font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($bill->amount, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Sisa Tunggakan</p>
                            <p class="text-base font-extrabold {{ $bill->outstanding_balance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                Rp {{ number_format($bill->outstanding_balance, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    @if($bill->payments->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Riwayat Pembayaran</p>
                            @foreach($bill->payments as $p)
                                <div class="flex justify-between text-sm py-1 border-b border-dashed border-slate-50 dark:border-slate-800/50 last:border-0">
                                    <span class="text-slate-600 dark:text-slate-400">{{ $p->paid_at->format('d/m/Y') }} <span class="text-xs opacity-70">({{ $p->receipt_number }})</span></span>
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400">Rp {{ number_format($p->amount_paid, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-slate-50 dark:bg-slate-900/50 p-12 rounded-3xl text-center border-2 border-dashed border-slate-200 dark:border-slate-800">
                    <p class="text-lg font-bold text-slate-600 dark:text-slate-400">Belum ada tagihan pada periode ini.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($bills->hasPages())
            <div class="pt-4">
                {{ $bills->links() }}
            </div>
        @endif
    </div>
</x-layouts.dashboard>