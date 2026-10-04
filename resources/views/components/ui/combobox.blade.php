@props([
    'name' => null,
    'value' => null,
    'options' => [],
    'placeholder' => 'Pilih opsi...',
    'searchPlaceholder' => 'Ketik untuk mencari...',
    'emptyText' => 'Tidak ada hasil ditemukan.',
    'required' => false,
    'disabled' => false,
    'id' => null,
    'allowClear' => false,
    'compact' => false,
    'valueKey' => 'id',
    'labelKey' => 'name',
    'sublabelKey' => null,
    'optionsExpr' => null,
])

@php
    $triggerId = $id ?? ($name ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name) : 'combobox-' . Str::random(8));
    $comboboxWrapperId = 'combobox-wrapper-' . Str::random(8);

    // Normalize options from PHP array or collection
    $normalizedOptions = collect($options)->map(function ($opt) use ($valueKey, $labelKey, $sublabelKey) {
        if (is_array($opt)) {
            $val = $opt['value'] ?? $opt[$valueKey] ?? $opt['id'] ?? '';
            $lbl = $opt['label'] ?? $opt[$labelKey] ?? $opt['name'] ?? '';
            $sub = $opt['sublabel'] ?? ($sublabelKey ? ($opt[$sublabelKey] ?? null) : ($opt['customer_code'] ?? $opt['code'] ?? null));
        } elseif (is_object($opt)) {
            $val = $opt->value ?? $opt->{$valueKey} ?? $opt->id ?? '';
            $lbl = $opt->label ?? $opt->{$labelKey} ?? $opt->name ?? '';
            $sub = $opt->sublabel ?? ($sublabelKey ? ($opt->{$sublabelKey} ?? null) : ($opt->customer_code ?? $opt->code ?? null));
        } else {
            $val = $opt;
            $lbl = $opt;
            $sub = null;
        }
        return [
            'value' => (string) $val,
            'label' => (string) $lbl,
            'sublabel' => $sub ? (string) $sub : null,
        ];
    })->values()->all();

    $initialSelected = null;
    if ($value !== null && $value !== '') {
        $initialSelected = collect($normalizedOptions)->firstWhere('value', (string) $value);
    }
@endphp

<div x-data="uiCombobox({
        name: @js($name),
        initValue: @js($initialSelected ? $initialSelected['value'] : ($value !== null ? (string)$value : '')),
        initLabel: @js($initialSelected ? $initialSelected['label'] : ''),
        initSublabel: @js($initialSelected ? $initialSelected['sublabel'] : ''),
        staticOptions: @js($normalizedOptions),
        optionsExpr: @js($optionsExpr),
        valueKey: @js($valueKey),
        labelKey: @js($labelKey),
        sublabelKey: @js($sublabelKey),
        allowClear: @js($allowClear),
        disabled: @js($disabled),
     })"
     x-modelable="selectedValue"
     @click.outside="close()"
     @keydown.escape.stop="close()"
     class="relative w-full {{ $attributes->get('class') }}"
     id="{{ $comboboxWrapperId }}">

    {{-- Hidden Input for Standard Form Submission --}}
    @if($name)
        <input type="hidden"
               name="{{ $name }}"
               :value="selectedValue"
               x-ref="hiddenInput"
               {{ $required ? 'required' : '' }}>
    @else
        <input type="hidden"
               :value="selectedValue"
               x-ref="hiddenInput">
    @endif

    {{-- Combobox Trigger Button --}}
    <button type="button"
            id="{{ $triggerId }}"
            @click="toggleDropdown()"
            :disabled="disabled"
            :class="[
                disabled ? 'opacity-50 cursor-not-allowed bg-surface-muted' : 'cursor-pointer hover:border-primary/50',
                open ? 'border-primary ring-1 ring-primary' : 'border-border'
            ]"
            class="w-full text-left rounded-radius-md border bg-surface text-text px-3 shadow-subtle transition-subtle flex items-center justify-between gap-2 select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:border-primary {{ $compact ? 'py-1.5 text-body-sm min-h-[38px]' : 'py-2.5 text-body min-h-[44px]' }}"
            aria-haspopup="listbox"
            :aria-expanded="open">
        <span class="truncate flex items-center gap-2 min-w-0">
            <template x-if="selectedLabel">
                <span class="font-medium text-text truncate" x-text="selectedLabel"></span>
            </template>
            <template x-if="selectedLabel && selectedSublabel">
                <span class="font-mono text-caption text-text-muted shrink-0" x-text="'(' + selectedSublabel + ')'"></span>
            </template>
            <template x-if="!selectedLabel">
                <span class="text-text-muted truncate">{{ $placeholder }}</span>
            </template>
        </span>

        <span class="flex items-center gap-1.5 shrink-0 text-text-muted">
            <template x-if="allowClear && selectedValue">
                <span role="button"
                      @click.stop="clear()"
                      title="Hapus pilihan"
                      class="p-0.5 hover:text-danger rounded transition-subtle">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </template>
            <svg class="w-4 h-4 transition-transform duration-150" :class="open ? 'rotate-180 text-primary' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>

    {{-- Floating Dropdown Panel --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-100"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="ease-in duration-75"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         class="absolute z-50 left-0 mt-1.5 bg-surface rounded-radius-md shadow-popover border border-border overflow-hidden w-full {{ $compact ? 'min-w-[260px]' : '' }} max-w-[calc(100vw-2rem)]"
         style="display: none;">

        {{-- Dropdown Search Input --}}
        <div class="p-2 border-b border-border-subtle bg-surface-muted/30">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-text-muted">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text"
                       x-ref="searchInput"
                       x-model="search"
                       @input="highlightIndex = 0"
                       @keydown.arrow-down.prevent="navigate(1)"
                       @keydown.arrow-up.prevent="navigate(-1)"
                       @keydown.enter.prevent="selectHighlighted()"
                       @keydown.escape.prevent="close()"
                       placeholder="{{ $searchPlaceholder }}"
                       autocomplete="off"
                       class="w-full text-body-sm rounded-radius-sm border border-border bg-surface text-text pl-8 pr-3 py-1.5 placeholder:text-text-muted focus:border-primary focus:ring-1 focus:ring-primary shadow-subtle transition-subtle">
            </div>
        </div>

        {{-- Filtered Options List --}}
        <div class="max-h-60 overflow-y-auto divide-y divide-border-subtle" role="listbox" x-ref="optionsList">
            <template x-for="(opt, oIdx) in filteredOptions()" :key="opt.value">
                <div @click="select(opt)"
                     @mouseenter="highlightIndex = oIdx"
                     :class="[
                         highlightIndex === oIdx ? 'bg-primary-light text-primary' : 'hover:bg-surface-muted text-text',
                         selectedValue == opt.value ? 'font-bold' : ''
                     ]"
                     class="p-2.5 text-body-sm cursor-pointer flex items-center justify-between transition-subtle select-none"
                     role="option"
                     :aria-selected="selectedValue == opt.value">
                    <div class="flex items-center gap-2 truncate min-w-0">
                        <span x-text="opt.label" class="truncate"></span>
                        <template x-if="opt.sublabel">
                            <span class="font-mono text-caption text-text-muted shrink-0" x-text="'(' + opt.sublabel + ')'"></span>
                        </template>
                    </div>
                    <template x-if="selectedValue == opt.value">
                        <svg class="w-4 h-4 text-primary shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                </div>
            </template>

            {{-- Empty Result State --}}
            <template x-if="filteredOptions().length === 0">
                <div class="p-4 text-center text-body-sm text-text-muted">
                    {{ $emptyText }}
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    if (typeof window.uiCombobox === 'undefined') {
        window.uiCombobox = function(config) {
            return {
                open: false,
                search: '',
                selectedValue: config.initValue || '',
                selectedLabel: config.initLabel || '',
                selectedSublabel: config.initSublabel || '',
                highlightIndex: 0,
                staticOptions: config.staticOptions || [],
                optionsExpr: config.optionsExpr || null,
                valueKey: config.valueKey || 'id',
                labelKey: config.labelKey || 'name',
                sublabelKey: config.sublabelKey || null,
                allowClear: Boolean(config.allowClear),
                disabled: Boolean(config.disabled),

                init() {
                    if (this.selectedValue !== '' && !this.selectedLabel) {
                        this.syncLabel(this.selectedValue);
                    }

                    this.$nextTick(() => {
                        if (this.selectedValue !== '' && !this.selectedLabel) {
                            this.syncLabel(this.selectedValue);
                        }
                    });

                    this.$watch('selectedValue', (newVal) => {
                        this.syncLabel(newVal);
                        this.$dispatch('change', newVal);
                        this.triggerNativeChange();
                    });
                },

                getOptions() {
                    if (this.optionsExpr) {
                        let raw = [];
                        try {
                            if (typeof this[this.optionsExpr] !== 'undefined') {
                                raw = this[this.optionsExpr];
                            } else if (window[this.optionsExpr]) {
                                raw = window[this.optionsExpr];
                            }
                        } catch (e) {}

                        return (raw || []).map((item) => ({
                            value: String(item[this.valueKey] ?? item.id ?? item.value ?? ''),
                            label: String(item[this.labelKey] ?? item.name ?? item.label ?? ''),
                            sublabel: (item[this.sublabelKey] ?? item.code ?? item.customer_code ?? item.unit ?? null)
                                ? String(item[this.sublabelKey] ?? item.code ?? item.customer_code ?? item.unit)
                                : null,
                        }));
                    }
                    return this.staticOptions || [];
                },

                filteredOptions() {
                    const opts = this.getOptions();
                    const q = (this.search || '').toLowerCase().trim();
                    if (!q) return opts;
                    return opts.filter((opt) =>
                        opt.label.toLowerCase().includes(q) ||
                        (opt.sublabel && opt.sublabel.toLowerCase().includes(q))
                    );
                },

                syncLabel(val) {
                    if (val === '' || val === null || val === undefined) {
                        this.selectedLabel = '';
                        this.selectedSublabel = '';
                        return;
                    }
                    const opts = this.getOptions();
                    const found = opts.find((o) => String(o.value) === String(val));
                    if (found) {
                        this.selectedLabel = found.label;
                        this.selectedSublabel = found.sublabel || '';
                    }
                },

                toggleDropdown() {
                    if (this.disabled) return;
                    if (this.open) {
                        this.close();
                    } else {
                        this.openDropdown();
                    }
                },

                openDropdown() {
                    if (this.disabled) return;
                    this.open = true;
                    this.search = '';
                    this.highlightIndex = 0;
                    this.$nextTick(() => {
                        if (this.$refs.searchInput) {
                            this.$refs.searchInput.focus();
                        }
                    });
                },

                close() {
                    this.open = false;
                    this.search = '';
                },

                select(opt) {
                    this.selectedValue = opt.value;
                    this.selectedLabel = opt.label;
                    this.selectedSublabel = opt.sublabel || '';
                    this.close();
                    this.$dispatch('change', opt.value);
                    this.triggerNativeChange();
                },

                clear() {
                    this.selectedValue = '';
                    this.selectedLabel = '';
                    this.selectedSublabel = '';
                    this.close();
                    this.$dispatch('change', '');
                    this.triggerNativeChange();
                },

                navigate(dir) {
                    const list = this.filteredOptions();
                    const len = list.length;
                    if (!len) return;
                    this.highlightIndex = (this.highlightIndex + dir + len) % len;
                    this.scrollIntoView();
                },

                selectHighlighted() {
                    const list = this.filteredOptions();
                    if (list[this.highlightIndex]) {
                        this.select(list[this.highlightIndex]);
                    }
                },

                scrollIntoView() {
                    this.$nextTick(() => {
                        const container = this.$refs.optionsList;
                        const item = container ? container.children[this.highlightIndex] : null;
                        if (item && item.scrollIntoView) {
                            item.scrollIntoView({ block: 'nearest' });
                        }
                    });
                },

                triggerNativeChange() {
                    this.$nextTick(() => {
                        if (this.$refs.hiddenInput) {
                            this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                            this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                },
            };
        };
    }
</script>
