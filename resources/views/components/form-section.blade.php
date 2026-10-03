@props(['title' => null, 'description' => null])

<div class="p-6 sm:p-8 space-y-6">
    @if($title)
    <div class="pb-4 border-b border-border-subtle/60">
        <h3 class="text-base font-bold text-text">{{ $title }}</h3>
        @if($description)
        <p class="mt-1 text-sm text-text-muted">{{ $description }}</p>
        @endif
    </div>
    @endif
    {{ $slot }}
</div>
