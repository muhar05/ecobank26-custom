<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'kk_id',
        'fund_category_id',
        'bill_code',
        'amount',
        'due_date',
        'month',
        'year',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'month' => 'integer',
        'year' => 'integer',
    ];

    public function kk(): BelongsTo
    {
        return $this->belongsTo(Kk::class, 'kk_id');
    }

    public function fundCategory(): BelongsTo
    {
        return $this->belongsTo(FundCategory::class, 'fund_category_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class, 'bill_id');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount_paid');
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return max(0.00, (float) $this->amount - $this->total_paid);
    }
}
