@props(['action' => null, 'placeholder' => 'Cari data...', 'search' => ''])

<form method="GET" action="{{ $action ?? request()->url() }}" x-data="{ loading: false }" @submit="loading = true" class="flex flex-col sm:flex-row gap-2">
    <div class="flex-1">
        <input type="text" name="search" value="{{ $search }}" placeholder="{{ $placeholder }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500 placeholder-slate-400 dark:placeholder-slate-500">
    </div>
    <div class="flex gap-2">
        <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 bg-emerald-700 dark:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-500 transition disabled:opacity-60">
            <template x-if="!loading">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </template>
            <template x-if="loading">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </template>
            <span x-text="loading ? 'Mencari...' : 'Cari'"></span>
        </button>
        @if($search)
        <a href="{{ $action ?? request()->url() }}" class="inline-flex items-center px-3 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition">Reset</a>
        @endif
    </div>
</form>
