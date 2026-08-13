<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsLedger extends Model
{
    protected $fillable = ['waste_customer_id', 'type', 'amount', 'description', 'reference_type', 'reference_id'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function wasteCustomer(): BelongsTo
    {
        return $this->belongsTo(WasteCustomer::class);
    }
}
