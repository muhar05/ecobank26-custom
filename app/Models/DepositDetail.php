<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositDetail extends Model
{
    protected $fillable = ['deposit_id', 'waste_category_id', 'weight', 'price_per_unit', 'subtotal'];

    protected function casts(): array
    {
        return ['weight' => 'decimal:2', 'price_per_unit' => 'decimal:2', 'subtotal' => 'decimal:2'];
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }
}
