@props(['title' => null, 'description' => null])

<div class="p-6 space-y-5">
    @if($title)
    <div class="pb-3 border-b border-slate-100 dark:border-slate-800">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $title }}</h3>
        @if($description)
        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
    @endif
    {{ $slot }}
</div>
