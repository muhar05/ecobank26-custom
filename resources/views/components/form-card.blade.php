@props(['title' => null, 'description' => null])

<div class="mx-auto py-8 px-4 sm:px-0">
    @if($title)
    <div class="mb-6 text-center sm:text-left">
        <h2 class="text-2xl font-bold text-text">{{ $title }}</h2>
        @if($description)
        <p class="mt-2 text-sm text-text-muted">{{ $description }}</p>
        @endif
    </div>
    @endif
    <div class="bg-surface rounded-radius-lg shadow-sm border border-border overflow-hidden transition-all">
        {{ $slot }}
    </div>
</div>
