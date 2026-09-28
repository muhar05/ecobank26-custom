<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    protected $fillable = [
        'sale_id',
        'waste_category_id',
        'weight',
        'price_per_unit',
        'member_price',
        'subtotal',
        'margin',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'member_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'margin' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }
}
