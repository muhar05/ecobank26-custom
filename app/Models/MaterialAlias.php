<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialAlias extends Model
{
    public const SOURCE_BANK_SAMPAH = 'BANK_SAMPAH';
    public const SOURCE_AGGREGATOR = 'AGGREGATOR';

    protected $fillable = [
        'source_type',
        'source_id',
        'external_name',
        'normalized_name',
        'waste_category_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Collector::class, 'source_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForBankSampah(Builder $query): Builder
    {
        return $query->where('source_type', self::SOURCE_BANK_SAMPAH)->whereNull('source_id');
    }

    public function scopeForAggregator(Builder $query, int $collectorId): Builder
    {
        return $query->where('source_type', self::SOURCE_AGGREGATOR)->where('source_id', $collectorId);
    }
}
