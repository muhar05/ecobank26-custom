<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposit extends Model
{
    protected $fillable = ['member_id', 'collector_id', 'date', 'total_amount', 'notes'];

    protected function casts(): array
    {
        return ['date' => 'date', 'total_amount' => 'decimal:2'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Collector::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DepositDetail::class);
    }
}
