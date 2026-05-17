@props(['href', 'active' => false])

<a href="{{ $href }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ $active ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-emerald-50 dark:hover:bg-slate-800 hover:text-emerald-700 dark:hover:text-emerald-400' }}">
    <span class="flex-shrink-0 w-5 h-5">{{ $icon ?? '' }}</span>
    {{ $slot }}
</a>
