@props(['title' => null, 'description' => null])

<div class="p-6 sm:p-8 space-y-6">
    @if($title)
    <div class="pb-4 border-b border-slate-100 dark:border-slate-800/60">
        <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">{{ $title }}</h3>
        @if($description)
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
    @endif
    {{ $slot }}
</div>
