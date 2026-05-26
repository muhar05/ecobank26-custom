<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'community_contribution_id',
        'receipt_number',
        'amount_paid',
        'payment_method',
        'paid_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(CommunityContribution::class, 'community_contribution_id');
    }
}
