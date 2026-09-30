@props(['href', 'active' => false])

<a href="{{ $href }}" 
   title="{{ strip_tags($slot) }}"
   {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2.5 rounded-radius-md text-body-sm font-medium transition-subtle ' . ($active ? 'bg-primary text-white shadow-subtle' : 'text-text-muted hover:bg-surface-muted hover:text-text')]) }}>
    <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center">{{ $icon ?? '' }}</span>
    <span class="truncate transition-opacity duration-200" x-show="!sidebarCollapsed" x-cloak>{{ $slot }}</span>
</a>
