@props([
    'name' => null,
    'value' => null,
    'options' => [],
    'label' => null,
    'cards' => false,
    'autoSubmit' => false,
    'required' => false,
    'disabled' => false,
    'id' => null,
])

@php
    $pillsId = $id ?? 'pills-' . Str::random(8);

    // Normalize options
    $normalizedOptions = collect($options)->map(function ($opt) {
        if (is_array($opt)) {
            return [
                'value' => (string) ($opt['value'] ?? ''),
                'label' => (string) ($opt['label'] ?? ''),
                'count' => isset($opt['count']) ? $opt['count'] : null,
                'countExpr' => $opt['countExpr'] ?? null,
                'icon' => $opt['icon'] ?? null,
                'description' => $opt['description'] ?? null,
            ];
        }
        return [
            'value' => (string) $opt,
            'label' => (string) $opt,
            'count' => null,
            'countExpr' => null,
            'icon' => null,
            'description' => null,
        ];
    })->values()->all();

    $initVal = $value !== null ? (string) $value : '';
@endphp

<div x-data="uiFilterPills({
        initValue: @js($initVal),
        autoSubmit: @js($autoSubmit),
        disabled: @js($disabled),
     })"
     x-modelable="activeVal"
     class="w-full {{ $attributes->get('class') }}"
     id="{{ $pillsId }}">

    {{-- Hidden input for regular form submission --}}
    @if($name)
        <input type="hidden"
               name="{{ $name }}"
               :value="activeVal"
               x-ref="hiddenInput"
               {{ $required ? 'required' : '' }}>
    @endif

    @if($cards)
        {{-- Segmented Cards Layout (for complex choices with descriptions) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3" role="radiogroup" aria-label="{{ $label ?? ($name ?? 'Pilihan') }}">
            @foreach($normalizedOptions as $opt)
                <div @click="select('{{ $opt['value'] }}')"
                     :class="[
                         activeVal === '{{ $opt['value'] }}'
                             ? 'border-primary ring-1 ring-primary bg-primary-light/20 text-text'
                             : 'border-border bg-surface hover:bg-surface-muted/60 text-text',
                         disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                     ]"
                     class="p-4 rounded-radius-md border transition-subtle shadow-subtle flex flex-col justify-between select-none min-h-[44px]"
                     role="radio"
                     :aria-checked="activeVal === '{{ $opt['value'] }}'"
                     tabindex="0"
                     @keydown.space.prevent="select('{{ $opt['value'] }}')"
                     @keydown.enter.prevent="select('{{ $opt['value'] }}')">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-body" :class="activeVal === '{{ $opt['value'] }}' ? 'text-primary' : 'text-text'">
                                {{ $opt['label'] }}
                            </span>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 transition-subtle"
                                 :class="activeVal === '{{ $opt['value'] }}' ? 'border-primary bg-primary' : 'border-border bg-surface'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="activeVal === '{{ $opt['value'] }}'"></div>
                            </div>
                        </div>
                        @if($opt['description'])
                            <p class="text-body-sm text-text-muted mt-1.5 leading-relaxed">
                                {{ $opt['description'] }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Horizontal Filter Pills Layout (One-Click instant filter) --}}
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 pr-6" role="radiogroup" aria-label="{{ $label ?? ($name ?? 'Filter') }}">
            @foreach($normalizedOptions as $opt)
                <button type="button"
                        @click="select('{{ $opt['value'] }}')"
                        :disabled="disabled"
                        :class="[
                            activeVal === '{{ $opt['value'] }}'
                                ? 'bg-primary text-white font-bold shadow-subtle border-primary'
                                : 'bg-surface-muted text-text hover:bg-surface border border-border font-semibold',
                            disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                        ]"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-radius-md border text-body-sm transition-subtle select-none whitespace-nowrap min-h-[38px] focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1"
                        role="radio"
                        :aria-checked="activeVal === '{{ $opt['value'] }}'">
                    @if($opt['icon'])
                        {!! $opt['icon'] !!}
                    @endif
                    <span>{{ $opt['label'] }}</span>

                    @if($opt['count'] !== null)
                        <span class="font-mono text-caption px-1.5 py-0.2 rounded"
                              :class="activeVal === '{{ $opt['value'] }}' ? 'bg-white/20 text-white' : 'bg-surface text-text-muted'">
                            {{ $opt['count'] }}
                        </span>
                    @elseif($opt['countExpr'])
                        <span class="font-mono text-caption px-1.5 py-0.2 rounded"
                              :class="activeVal === '{{ $opt['value'] }}' ? 'bg-white/20 text-white' : 'bg-surface text-text-muted'"
                              x-text="{{ $opt['countExpr'] }}"></span>
                    @endif
                </button>
            @endforeach
        </div>
    @endif
</div>

<script>
    if (typeof window.uiFilterPills === 'undefined') {
        window.uiFilterPills = function(config) {
            return {
                activeVal: config.initValue || '',
                autoSubmit: Boolean(config.autoSubmit),
                disabled: Boolean(config.disabled),

                select(val) {
                    if (this.disabled) return;
                    this.activeVal = val;
                    this.$dispatch('change', val);
                    this.$dispatch('input', val);

                    if (this.$refs.hiddenInput) {
                        this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                        this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    if (this.autoSubmit) {
                        this.$nextTick(() => {
                            const form = this.$el.closest('form');
                            if (form) {
                                form.submit();
                            }
                        });
                    }
                }
            };
        };
    }
</script>
