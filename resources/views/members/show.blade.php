<x-layouts.dashboard title="Detail Warga">
    <div class="max-w-2xl space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ $member->name }}</h2>
            <a href="{{ route('members.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Kembali</a>
        </div>

        {{-- Info Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500 dark:text-slate-400">Kode Warga</dt>
                    <dd class="font-mono font-medium text-slate-900 dark:text-slate-100">{{ $member->member_code }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 dark:text-slate-400">Telepon</dt>
                    <dd class="text-slate-900 dark:text-slate-100">{{ $member->phone ?? '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500 dark:text-slate-400">Alamat</dt>
                    <dd class="text-slate-900 dark:text-slate-100">{{ $member->address ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 dark:text-slate-400">Status Akun</dt>
                    <dd>
                        @if($member->user_id)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">Terhubung ke akun</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Belum terhubung</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total Iuran</p>
                <p class="text-lg font-bold text-emerald-700 dark:text-emerald-400 mt-1">Rp {{ number_format($totalContribution, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Jumlah Transaksi</p>
                <p class="text-lg font-bold text-slate-900 dark:text-slate-100 mt-1">{{ $member->contributions_count }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Aktivitas Terakhir</p>
                <p class="text-lg font-bold text-slate-900 dark:text-slate-100 mt-1">{{ $latestActivity ? $latestActivity->date->format('d/m/Y') : '-' }}</p>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
