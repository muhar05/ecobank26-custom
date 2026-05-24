@props([
    'action' => null, 
    'placeholder' => 'Cari data...', 
    'search' => '',
    'filters' => [],
    'sortOptions' => [],
    'showDateFilter' => false,
    'dateFrom' => '',
    'dateTo' => '',
    'selectedFilter' => '',
    'selectedSort' => ''
])

<div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6">
    <form method="GET" action="{{ $action ?? request()->url() }}" x-data="{
        loading: false, 
        showFilters: {{ json_encode(count($filters) > 0 || $showDateFilter) }},
        hasActiveFilters: {{ json_encode((bool)($search || $selectedFilter || $selectedSort || $dateFrom || $dateTo)) }}
    }" @submit="loading = true">
        
        {{-- Main Search Row --}}
        <div class="flex flex-col lg:flex-row gap-3">
            {{-- Search Input --}}
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="{{ $placeholder }}" 
                           class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2">
                @if(count($filters) > 0 || $showDateFilter)
                    <button type="button" 
                            @click="showFilters = !showFilters"
                            class="inline-flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
                            :class="hasActiveFilters && 'text-emerald-600 dark:text-emerald-400'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
                        </svg>
                        Filter
                        <template x-if="hasActiveFilters">
                            <span class="inline-flex items-center justify-center w-2 h-2 bg-emerald-500 rounded-full"></span>
                        </template>
                    </button>
                @endif

                <button type="submit" 
                        :disabled="loading" 
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="!loading">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </template>
                    <template x-if="loading">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </template>
                    <span x-text="loading ? 'Mencari...' : 'Cari'"></span>
                </button>

                @if($search || $selectedFilter || $selectedSort || $dateFrom || $dateTo)
                    <a href="{{ $action ?? request()->url() }}" 
                       class="inline-flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </a>
                @endif
            </div>
        </div>

        {{-- Advanced Filters --}}
        @if(count($filters) > 0 || $showDateFilter)
            <div x-show="showFilters" 
                 x-transition:enter="transition ease-out duration-200" 
                 x-transition:enter-start="opacity-0 -translate-y-2" 
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Category/Status Filter --}}
                    @if(count($filters) > 0)
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Filter</label>
                            <select name="filter" class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Semua</option>
                                @foreach($filters as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedFilter == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Sort Options --}}
                    @if(count($sortOptions) > 0)
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Urutkan</label>
                            <select name="sort" class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Default</option>
                                @foreach($sortOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedSort == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Date Range Filter --}}
                    @if($showDateFilter)
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Dari Tanggal</label>
                            <input type="date" 
                                   name="date_from" 
                                   value="{{ $dateFrom }}" 
                                   class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Sampai Tanggal</label>
                            <input type="date" 
                                   name="date_to" 
                                   value="{{ $dateTo }}" 
                                   class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </form>
</div>