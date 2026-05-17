<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityCashLedger extends Model
{
    protected $fillable = [
        'fund_category_id', 'type', 'amount', 'balance',
        'reference_type', 'reference_id', 'date', 'description',
    ];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'decimal:2', 'balance' => 'decimal:2'];
    }

    public function fundCategory(): BelongsTo
    {
        return $this->belongsTo(FundCategory::class);
    }
}
