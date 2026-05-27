<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    protected $fillable = ['member_id', 'waste_customer_id', 'amount', 'date', 'notes'];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'decimal:2'];
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
