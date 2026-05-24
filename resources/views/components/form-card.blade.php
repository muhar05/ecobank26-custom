@props(['title' => null, 'description' => null])

<div class="mx-auto py-8 px-4 sm:px-0">
    @if($title)
    <div class="mb-6 text-center sm:text-left">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $title }}</h2>
        @if($description)
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
    @endif
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-all">
        {{ $slot }}
    </div>
</div>
