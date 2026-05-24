@props([
    'title' => '',
    'subtitle' => '',
    'breadcrumbs' => [],
    'actions' => []
])

<div class="mb-8">
    {{-- Breadcrumbs --}}
    @if(count($breadcrumbs) > 0)
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                @foreach($breadcrumbs as $index => $breadcrumb)
                    <li class="inline-flex items-center">
                        @if($index > 0)
                            <svg class="w-4 h-4 text-slate-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        
                        @if(isset($breadcrumb['url']) && !$loop->last)
                            <a href="{{ $breadcrumb['url'] }}" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                                {{ $breadcrumb['label'] }}
                            </a>
                        @else
                            <span class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ $breadcrumb['label'] ?? $breadcrumb }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    {{-- Header Content --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                {{ $title }}
            </h1>
            @if($subtitle)
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        {{-- Actions --}}
        @if(count($actions) > 0)
            <div class="flex flex-col sm:flex-row gap-3">
                @foreach($actions as $action)
                    @if($action['type'] === 'link')
                        <a href="{{ $action['url'] }}" 
                           class="inline-flex items-center justify-center gap-2 {{ $action['class'] ?? 'bg-emerald-600 hover:bg-emerald-700 text-white' }} px-4 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150">
                            @if(isset($action['icon']))
                                {!! $action['icon'] !!}
                            @endif
                            {{ $action['label'] }}
                        </a>
                    @elseif($action['type'] === 'button')
                        <button type="button" 
                                @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                @if(isset($action['alpine'])) {!! $action['alpine'] !!} @endif
                                class="inline-flex items-center justify-center gap-2 {{ $action['class'] ?? 'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200' }} px-4 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150">
                            @if(isset($action['icon']))
                                {!! $action['icon'] !!}
                            @endif
                            {{ $action['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{-- Additional Content --}}
    @if($slot->isNotEmpty())
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif
</div>