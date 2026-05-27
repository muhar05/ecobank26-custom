<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class WasteCustomer extends Model
{
    protected $fillable = [
        'user_id',
        'member_id',
        'customer_code',
        'name',
        'phone',
        'address',
        'status',
        'joined_at'
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function savingsLedgers(): HasMany
    {
        return $this->hasMany(SavingsLedger::class);
    }

    /**
     * Generate next customer code safely with lockForUpdate to prevent race conditions.
     * Format: NSB-YYYYMM-XXXX
     */
    public static function generateNextCustomerCode(): string
    {
        return DB::transaction(function () {
            $prefix = 'NSB-' . date('Ym') . '-';

            // Use lockForUpdate to ensure only one process can calculate the next code at a time
            $lastCode = self::where('customer_code', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('customer_code', 'desc')
                ->value('customer_code');

            if ($lastCode) {
                $lastNumber = (int) substr($lastCode, -4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
