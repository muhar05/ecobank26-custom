@php
    $progressStep = $step ?? 'upload';

    $progressStage = match ($progressStep) {
        'review' => 2,
        'result' => 3,
        default => 1,
    };

    $progressItems = [
        ['label' => 'Upload'],
        ['label' => 'Mapping & Review'],
        ['label' => 'Selesai'],
    ];
@endphp

<nav aria-label="Progress import harga agregator" class="border-b border-border-subtle pb-4">
    <ol class="flex flex-wrap items-center gap-x-3 gap-y-2.5">
        @foreach($progressItems as $i => $item)
            @php
                $stepNumber = $i + 1;
                $isComplete = $stepNumber < $progressStage;
                $isCurrent = $stepNumber === $progressStage;
            @endphp

            @if($i > 0)
                <li aria-hidden="true" class="hidden sm:block h-px flex-1 min-w-8 max-w-20 {{ $isComplete || $isCurrent ? 'bg-primary/40' : 'bg-border' }}"></li>
            @endif

            <li @if($isCurrent) aria-current="step" @endif class="flex items-center gap-2 shrink-0">
                @if($isComplete)
                    <span aria-hidden="true" class="w-7 h-7 shrink-0 rounded-full bg-primary text-white flex items-center justify-center font-bold text-caption shadow-subtle">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                @elseif($isCurrent)
                    <span aria-hidden="true" class="w-7 h-7 shrink-0 rounded-full border-2 border-primary bg-primary-light text-primary flex items-center justify-center text-body-sm font-bold shadow-subtle">
                        {{ $stepNumber }}
                    </span>
                @else
                    <span aria-hidden="true" class="w-7 h-7 shrink-0 rounded-full border border-border bg-surface text-text-muted flex items-center justify-center text-body-sm font-semibold">
                        {{ $stepNumber }}
                    </span>
                @endif

                <span class="text-body-sm {{ $isCurrent ? 'text-text font-bold' : ($isComplete ? 'text-text font-semibold' : 'text-text-muted font-medium') }}">
                    {{ $item['label'] }}
                </span>
            </li>
        @endforeach
    </ol>
</nav>
