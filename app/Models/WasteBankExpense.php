<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class WasteBankExpense extends Model
{
    protected $fillable = [
        'expense_code',
        'amount',
        'description',
        'expense_date',
        'recorded_by',
        'proof_path',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function ledger(): MorphOne
    {
        return $this->morphOne(WasteBankCashLedger::class, 'reference');
    }

    public static function generateExpenseCode(): string
    {
        $prefix = 'EXP-' . date('Ym') . '-';
        $latest = static::where('expense_code', 'LIKE', "{$prefix}%")
            ->orderBy('expense_code', 'desc')
            ->first();

        if (!$latest) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($latest->expense_code, -4);
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $nextNumber;
    }
}
