@props(['title' => null, 'description' => null])

<div class="max-w-2xl">
    @if($title)
    <div class="mb-5">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">{{ $title }}</h2>
        @if($description)
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
    @endif
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        {{ $slot }}
    </div>
</div>
