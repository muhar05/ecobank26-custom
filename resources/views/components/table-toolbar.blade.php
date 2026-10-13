@props(['action' => null, 'placeholder' => 'Cari data...', 'search' => ''])

<form method="GET" action="{{ $action ?? request()->url() }}" data-live-search x-data="{ loading: false }" @submit="loading = true" class="flex flex-col sm:flex-row gap-2">
    <div class="flex-1">
        <input type="text" name="search" value="{{ $search }}" placeholder="{{ $placeholder }}" class="w-full rounded-radius-md border-border bg-surface text-text shadow-sm text-sm focus:border-primary focus:ring-primary placeholder:text-text-muted">
    </div>
    <div class="flex gap-2">
        <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-radius-md text-sm font-medium hover:bg-primary-hover transition disabled:opacity-60">
            <template x-if="!loading">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </template>
            <template x-if="loading">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </template>
            <span x-text="loading ? 'Mencari...' : 'Cari'"></span>
        </button>
        <a href="{{ $action ?? request()->url() }}" data-live-reset class="inline-flex items-center px-3 py-2 text-sm text-text-muted hover:text-text transition {{ $search ? '' : 'hidden' }}">Reset</a>
    </div>
</form>
