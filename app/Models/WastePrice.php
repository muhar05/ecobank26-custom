<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WastePrice extends Model
{
    protected $fillable = ['waste_category_id', 'collector_id', 'collector_price'];

    protected function casts(): array
    {
        return [
            'collector_price' => 'decimal:2',
        ];
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Collector::class);
    }

    public function histories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WastePriceHistory::class)->latest('changed_at');
    }
}
