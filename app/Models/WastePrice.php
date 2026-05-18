<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WastePrice extends Model
{
    protected $fillable = ['waste_category_id', 'collector_id', 'price_per_unit'];

    protected function casts(): array
    {
        return ['price_per_unit' => 'decimal:2'];
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Collector::class);
    }
}
