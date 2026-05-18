<x-layouts.dashboard title="Dashboard">
    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 text-center max-w-md">
            <p class="text-sm text-slate-600 dark:text-slate-400">Dashboard tidak tersedia untuk role ini.</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="bg-emerald-700 dark:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-400 transition">
                    Masuk Ulang
                </button>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
