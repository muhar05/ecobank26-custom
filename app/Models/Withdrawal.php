<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    protected $fillable = ['waste_customer_id', 'amount', 'date', 'notes'];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'decimal:2'];
    }

    public function wasteCustomer(): BelongsTo
    {
        return $this->belongsTo(WasteCustomer::class);
    }
}
