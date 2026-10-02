@php
    /**
     * Progress utama Import Setoran (4 tahap):
     * Upload → Hubungkan Data → Rekap & Validasi → Selesai.
     * Nasabah / Material adalah tab navigasi utama di dalam tahap Hubungkan Data.
     *
     * $progressStage = jumlah tahap yang sudah dicapai:
     * 1 = Upload berjalan, 2 = Hubungkan Data berjalan, 3 = Rekap berjalan, 4 = seluruh tahap selesai.
     */
    $progressStep = $step ?? 'upload';
    $progressSubView = $subView ?? 'resolve';

    $progressStage = match (true) {
        $progressStep === 'result' => 4,
        $progressStep === 'preview' => $progressSubView === 'recap' ? 3 : 2,
        default => 1,
    };

    $progressAllComplete = $progressStage >= 4;
    $progressItems = [
        ['label' => 'Upload'],
        ['label' => 'Hubungkan Data'],
        ['label' => 'Rekap & Validasi'],
        ['label' => 'Selesai'],
    ];
    $progressPrevComplete = false;
@endphp

<nav aria-label="Progress import setoran" class="border-b border-border-subtle pb-4">
    <ol class="flex flex-wrap items-center gap-x-3 gap-y-2.5">
        @foreach($progressItems as $i => $progressItem)
            @php
                $isComplete = $progressAllComplete || (($i + 1) < $progressStage);
                $isCurrent = !$progressAllComplete && (($i + 1) === $progressStage);
            @endphp

            @if($i > 0)
                <li aria-hidden="true" class="hidden sm:block h-px flex-1 min-w-6 max-w-14 {{ $progressPrevComplete ? 'bg-primary/40' : 'bg-border' }}"></li>
            @endif

            <li @if($isCurrent) aria-current="step" @endif class="flex items-center gap-2 shrink-0">
                @if($isComplete)
                    <span aria-hidden="true" class="w-7 h-7 shrink-0 rounded-full bg-primary text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                @elseif($isCurrent)
                    <span aria-hidden="true" class="w-7 h-7 shrink-0 rounded-full border-2 border-primary bg-primary-light text-primary flex items-center justify-center text-body-sm font-bold">
                        {{ $i + 1 }}
                    </span>
                @else
                    <span aria-hidden="true" class="w-7 h-7 shrink-0 rounded-full border border-border bg-surface text-text-muted flex items-center justify-center text-body-sm font-semibold">
                        {{ $i + 1 }}
                    </span>
                @endif

                <span class="text-body-sm {{ $isCurrent ? 'text-text font-bold' : ($isComplete ? 'text-text font-semibold' : 'text-text-muted font-medium') }}">
                    {{ $progressItem['label'] }}
                </span>

                <span class="sr-only">
                    @if($isComplete)
                        (selesai)
                    @elseif($isCurrent)
                        (tahap saat ini)
                    @else
                        (menunggu)
                    @endif
                </span>
            </li>

            @php $progressPrevComplete = $isComplete; @endphp
        @endforeach
    </ol>
</nav>
