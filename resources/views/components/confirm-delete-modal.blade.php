@props(['id' => 'confirm-delete-modal'])

<div x-data="{ open: false, action: '' }" x-on:open-delete-modal.window="if ($event.detail.id === '{{ $id }}') { action = $event.detail.action; open = true; }" @keydown.escape.window="open = false" x-cloak>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 w-full max-w-sm">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Konfirmasi Hapus</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="mt-4 flex justify-end gap-3">
                <button @click="open = false" type="button" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</button>
                <form :action="action" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
