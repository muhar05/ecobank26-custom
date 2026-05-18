<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteBankCashLedger extends Model
{
    protected $fillable = [
        'type',
        'amount',
        'balance',
        'reference_type',
        'reference_id',
        'date',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];
}
