@props(['cancelUrl', 'submitLabel' => 'Simpan'])

<div x-data="{ submitting: false }" x-init="$el.closest('form').addEventListener('submit', () => { submitting = true })" class="px-6 sm:px-8 py-5 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-800 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
    <a href="{{ $cancelUrl }}" class="inline-flex justify-center items-center gap-2 text-sm font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition px-4 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" :class="submitting && 'pointer-events-none opacity-50'">
        Batal
    </a>
    <button type="submit" :disabled="submitting" class="inline-flex justify-center items-center gap-2 bg-emerald-600 dark:bg-emerald-500 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 dark:hover:bg-emerald-400 shadow-sm hover:shadow transition-all disabled:opacity-60 disabled:cursor-not-allowed w-full sm:w-auto">
        <template x-if="submitting">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </template>
        <span x-text="submitting ? 'Menyimpan...' : '{{ $submitLabel }}'"></span>
    </button>
</div>
