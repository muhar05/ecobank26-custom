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

    public function latestSavingsLedger()
    {
        return $this->hasOne(SavingsLedger::class)->latestOfMany();
    }

    /**
     * Get extracted RT number (e.g. "RT 03") from address or name.
     */
    public function getRtNumberAttribute(): string
    {
        if ($this->address && preg_match('/RT\s*0*(\d+)/i', $this->address, $m)) {
            return 'RT ' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }
        if ($this->name && preg_match('/RT\s*0*(\d+)/i', $this->name, $m)) {
            return 'RT ' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }
        return $this->address ?: '-';
    }

    /**
     * Get computed customer savings balance from existing ledger / service logic.
     */
    public function getBalanceAttribute(): float
    {
        if (isset($this->attributes['total_credit']) || array_key_exists('total_credit', $this->attributes)) {
            return (float) ($this->total_credit ?? 0) - (float) ($this->total_debit ?? 0);
        }
        return app(\App\Services\BankSampahService::class)->getCustomerBalance($this->id);
    }

    /**
     * Generate the next customer code safely with lockForUpdate to prevent race conditions.
     * Format: NSB-000001, NSB-000002, dst. (sequential, never changes once created).
     */
    public static function generateNextCustomerCode(): string
    {
        return DB::transaction(function () {
            // Use lockForUpdate to ensure only one process can calculate the next code at a time
            $codes = self::where('customer_code', 'like', 'NSB-%')
                ->lockForUpdate()
                ->pluck('customer_code');

            $max = 0;
            foreach ($codes as $code) {
                $parts = explode('-', $code);
                $num = (int) end($parts);
                if ($num > $max) {
                    $max = $num;
                }
            }

            return 'NSB-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
        });
    }
}
