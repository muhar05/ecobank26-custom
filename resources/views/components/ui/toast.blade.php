<div
    x-data="{
        toasts: [],
        addToast(type, message) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type, message, visible: true });
            setTimeout(() => this.removeToast(id), 4000);
        },
        removeToast(id) {
            const t = this.toasts.find(t => t.id === id);
            if (t) {
                t.visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(x => x.id !== id);
                }, 200);
            }
        },
        init() {
            @if(session('success'))
                this.addToast('success', @js(session('success')));
            @endif
            @if(session('error'))
                this.addToast('danger', @js(session('error')));
            @endif
            @if(session('warning'))
                this.addToast('warning', @js(session('warning')));
            @endif
            @if(session('info') || session('status'))
                this.addToast('info', @js(session('info') ?? session('status')));
            @endif
        }
    }"
    @toast.window="addToast($event.detail.type || 'info', $event.detail.message)"
    class="fixed top-5 right-5 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none"
    x-cloak
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="pointer-events-auto p-3.5 sm:p-4 rounded-radius-md border shadow-popover flex items-start gap-3 bg-surface transition-subtle"
            :class="{
                'border-success/40 bg-success-light/90 text-success-text': toast.type === 'success',
                'border-danger/40 bg-danger-light/90 text-danger-text': toast.type === 'danger',
                'border-warning/40 bg-warning-light/90 text-warning-text': toast.type === 'warning',
                'border-info/40 bg-info-light/90 text-info-text': toast.type === 'info',
            }"
        >
            {{-- Icon --}}
            <div class="shrink-0 mt-0.5">
                <template x-if="toast.type === 'success'">
                    <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </template>
                <template x-if="toast.type === 'danger'">
                    <svg class="w-5 h-5 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </template>
                <template x-if="toast.type === 'warning'">
                    <svg class="w-5 h-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="w-5 h-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </div>

            {{-- Message --}}
            <div class="flex-1 text-body-sm font-medium" x-text="toast.message"></div>

            {{-- Close Button --}}
            <button
                type="button"
                @click="removeToast(toast.id)"
                class="shrink-0 p-1 rounded-radius-sm opacity-70 hover:opacity-100 transition-subtle"
                aria-label="Tutup notifikasi"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>
