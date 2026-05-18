<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'collector_id',
        'date',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Collector::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }
}
