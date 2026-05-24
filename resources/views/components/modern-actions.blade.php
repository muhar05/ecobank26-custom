@props([
    'actions' => [],
    'size' => 'sm' // sm, md, lg
])

@php
$sizeClasses = [
    'sm' => 'px-2.5 py-1.5 text-xs',
    'md' => 'px-3 py-2 text-sm',
    'lg' => 'px-4 py-2.5 text-sm'
];
$baseClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

<div class="flex items-center gap-2">
    @foreach($actions as $action)
        @if($action['type'] === 'link')
            <a href="{{ $action['url'] }}" 
               class="inline-flex items-center gap-1.5 {{ $baseClass }} font-medium rounded-lg transition-colors duration-150 {{ $action['class'] ?? 'text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/20' }}">
                @if(isset($action['icon']))
                    {!! $action['icon'] !!}
                @endif
                {{ $action['label'] }}
            </a>
        @elseif($action['type'] === 'button')
            <button type="button" 
                    @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                    @if(isset($action['alpine'])) {!! $action['alpine'] !!} @endif
                    class="inline-flex items-center gap-1.5 {{ $baseClass }} font-medium rounded-lg transition-colors duration-150 {{ $action['class'] ?? 'text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20' }}">
                @if(isset($action['icon']))
                    {!! $action['icon'] !!}
                @endif
                {{ $action['label'] }}
            </button>
        @elseif($action['type'] === 'dropdown')
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" 
                        class="inline-flex items-center gap-1.5 {{ $baseClass }} font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                    </svg>
                </button>
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-10">
                    @foreach($action['items'] as $item)
                        @if($item['type'] === 'link')
                            <a href="{{ $item['url'] }}" 
                               class="flex items-center gap-2 px-4 py-2 text-sm {{ $item['class'] ?? 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                                @if(isset($item['icon']))
                                    {!! $item['icon'] !!}
                                @endif
                                {{ $item['label'] }}
                            </a>
                        @else
                            <button type="button" 
                                    @if(isset($item['onclick'])) onclick="{{ $item['onclick'] }}" @endif
                                    @if(isset($item['alpine'])) {!! $item['alpine'] !!} @endif
                                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-left {{ $item['class'] ?? 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                                @if(isset($item['icon']))
                                    {!! $item['icon'] !!}
                                @endif
                                {{ $item['label'] }}
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>