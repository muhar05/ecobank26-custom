@props([
    'title' => '',
    'value' => '',
    'change' => null,
    'changeType' => 'neutral', // positive, negative, neutral
    'icon' => null,
    'variant' => 'default', // default, primary, success, warning, danger
    'size' => 'md' // sm, md, lg
])

@php
$variantClasses = [
    'default' => 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700',
    'primary' => 'bg-emerald-50 dark:bg-emerald-950 border-emerald-200 dark:border-emerald-800',
    'success' => 'bg-green-50 dark:bg-green-950 border-green-200 dark:border-green-800',
    'warning' => 'bg-amber-50 dark:bg-amber-950 border-amber-200 dark:border-amber-800',
    'danger' => 'bg-red-50 dark:bg-red-950 border-red-200 dark:border-red-800'
];

$titleClasses = [
    'default' => 'text-slate-600 dark:text-slate-400',
    'primary' => 'text-emerald-600 dark:text-emerald-400',
    'success' => 'text-green-600 dark:text-green-400',
    'warning' => 'text-amber-600 dark:text-amber-400',
    'danger' => 'text-red-600 dark:text-red-400'
];

$valueClasses = [
    'default' => 'text-slate-900 dark:text-slate-100',
    'primary' => 'text-emerald-800 dark:text-emerald-300',
    'success' => 'text-green-800 dark:text-green-300',
    'warning' => 'text-amber-800 dark:text-amber-300',
    'danger' => 'text-red-800 dark:text-red-300'
];

$sizeClasses = [
    'sm' => 'p-4',
    'md' => 'p-6',
    'lg' => 'p-8'
];

$changeClasses = [
    'positive' => 'text-green-600 dark:text-green-400',
    'negative' => 'text-red-600 dark:text-red-400',
    'neutral' => 'text-slate-500 dark:text-slate-400'
];

$containerClass = $variantClasses[$variant] ?? $variantClasses['default'];
$titleClass = $titleClasses[$variant] ?? $titleClasses['default'];
$valueClass = $valueClasses[$variant] ?? $valueClasses['default'];
$sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
$changeClass = $changeClasses[$changeType] ?? $changeClasses['neutral'];
@endphp

<div class="rounded-xl shadow-sm border {{ $containerClass }} {{ $sizeClass }} transition-all duration-200 hover:shadow-md">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
                @if($icon)
                    <div class="flex-shrink-0">
                        {!! $icon !!}
                    </div>
                @endif
                <p class="text-xs font-semibold uppercase tracking-wide {{ $titleClass }}">{{ $title }}</p>
            </div>
            
            <div class="space-y-1">
                <p class="text-2xl font-bold {{ $valueClass }}">{{ $value }}</p>
                
                @if($change)
                    <div class="flex items-center gap-1">
                        @if($changeType === 'positive')
                            <svg class="w-4 h-4 {{ $changeClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7"/>
                            </svg>
                        @elseif($changeType === 'negative')
                            <svg class="w-4 h-4 {{ $changeClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7l-9.2 9.2M7 7v10h10"/>
                            </svg>
                        @endif
                        <span class="text-sm font-medium {{ $changeClass }}">{{ $change }}</span>
                    </div>
                @endif
            </div>
        </div>
        
        @if($slot->isNotEmpty())
            <div class="flex-shrink-0 ml-4">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>