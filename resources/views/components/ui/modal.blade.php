@props([
    'name' => null,
    'show' => false,
    'maxWidth' => 'md', // sm, md, lg, xl, full
    'title' => null,
    'description' => null,
])

@php
$maxWidthClasses = [
    'sm'   => 'sm:max-w-sm',
    'md'   => 'sm:max-w-md',
    'lg'   => 'sm:max-w-2xl',
    'xl'   => 'sm:max-w-4xl',
    'full' => 'sm:max-w-6xl',
][$maxWidth] ?? 'sm:max-w-md';
@endphp

<div
    x-data="{
        show: @js($show),
        focusables() {
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])';
            return [...$el.querySelectorAll(selector)].filter(el => ! el.hasAttribute('disabled'));
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return (this.focusables().indexOf(document.activeElement) - 1) % (this.focusables().length + 1) },
    }"
    @if($name)
        x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
        x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    @endif
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50 flex items-center justify-center"
    style="display: {{ $show ? 'block' : 'none' }};"
    x-cloak
>
    {{-- Backdrop --}}
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 transform transition-all"
        @click="show = false"
    >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-xs"></div>
    </div>

    {{-- Dialog Box --}}
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="mb-6 bg-surface border border-border rounded-radius-lg shadow-popover overflow-hidden transform transition-all sm:w-full {{ $maxWidthClasses }} sm:mx-auto relative z-10"
    >
        @if($title || $description)
            <div class="p-5 border-b border-border-subtle flex items-start justify-between gap-4">
                <div>
                    @if($title)
                        <h3 class="text-heading-sm font-bold text-text">{{ $title }}</h3>
                    @endif
                    @if($description)
                        <p class="text-body-sm text-text-muted mt-0.5">{{ $description }}</p>
                    @endif
                </div>
                <button type="button" @click="show = false" class="p-1.5 rounded-radius-sm text-text-muted hover:text-text hover:bg-surface-muted transition-subtle">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <div class="p-5 sm:p-6">
            {{ $slot }}
        </div>

        @if(isset($actions))
            <div class="p-4 px-5 sm:px-6 bg-surface-muted/50 border-t border-border-subtle flex items-center justify-end gap-3">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
