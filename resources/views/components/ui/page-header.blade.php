@props([
    'title',
    'description' => null,
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1">
    <div class="space-y-1">
        @if(isset($breadcrumbs))
            <div class="text-caption font-semibold text-text-muted flex items-center gap-1.5 mb-1">
                {{ $breadcrumbs }}
            </div>
        @endif
        <h1 class="text-heading-lg sm:text-heading-xl font-bold text-text tracking-tight flex items-center gap-2">
            {{ $title }}
        </h1>
        @if($description)
            <p class="text-body-sm text-text-muted max-w-2xl">
                {{ $description }}
            </p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
