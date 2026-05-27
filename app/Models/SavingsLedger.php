<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsLedger extends Model
{
    protected $fillable = ['member_id', 'waste_customer_id', 'type', 'amount', 'description', 'reference_type', 'reference_id'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function wasteCustomer(): BelongsTo
    {
        return $this->belongsTo(WasteCustomer::class);
    }
}
