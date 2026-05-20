@props(['cancelUrl', 'submitLabel' => 'Simpan'])

<div x-data="{ submitting: false }" class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
    <a href="{{ $cancelUrl }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition" :class="submitting && 'pointer-events-none opacity-50'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Batal
    </a>
    <button type="submit" @click="submitting = true" :disabled="submitting" class="inline-flex items-center gap-2 bg-emerald-700 dark:bg-emerald-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-emerald-800 dark:hover:bg-emerald-500 shadow-sm hover:shadow transition-all disabled:opacity-60 disabled:cursor-not-allowed">
        <template x-if="!submitting">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </template>
        <template x-if="submitting">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </template>
        <span x-text="submitting ? 'Menyimpan...' : '{{ $submitLabel }}'"></span>
    </button>
</div>
