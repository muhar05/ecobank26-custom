@props(['id' => 'confirm-delete-modal'])

<div x-data="{ open: false, action: '', deleting: false }" x-on:open-delete-modal.window="if ($event.detail.id === '{{ $id }}') { action = $event.detail.action; deleting = false; open = true; }" @keydown.escape.window="open = false" x-cloak>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="if(!deleting) open = false"></div>
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 w-full max-w-sm overflow-hidden">
            <div class="p-6 text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Yakin ingin menghapus data ini?</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Data yang dihapus mungkin mempengaruhi laporan dan saldo. Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="px-6 pb-6 flex gap-3">
                <button @click="open = false" :disabled="deleting" type="button" class="flex-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Batal</button>
                <form :action="action" method="POST" class="flex-1" @submit="deleting = true">
                    @csrf
                    @method('DELETE')
                    <button type="submit" :disabled="deleting" class="w-full inline-flex items-center justify-center gap-2 bg-red-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-red-700 transition disabled:opacity-60 disabled:cursor-not-allowed">
                        <template x-if="!deleting">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        </template>
                        <template x-if="deleting">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </template>
                        <span x-text="deleting ? 'Menghapus...' : 'Ya, Hapus Data'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
