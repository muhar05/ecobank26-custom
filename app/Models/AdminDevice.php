<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_token',
        'device_name',
        'browser',
        'platform',
        'user_agent',
        'last_ip',
        'is_revoked',
        'first_seen_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at'  => 'datetime',
            'is_revoked'    => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(WastePriceHistory::class, 'admin_device_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_revoked', false);
    }

    public function isNamed(): bool
    {
        return !empty($this->device_name);
    }
}
