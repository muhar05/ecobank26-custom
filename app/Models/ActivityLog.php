<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    // Append-only, no updated_at needed
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'severity',
        'event_type',
        'description',
        'ip_address',
        'user_agent',
        'correlation_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Disable updates and deletes to maintain immutable integrity.
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            throw new \Exception('Activity logs are immutable (append-only) and cannot be updated.');
        });

        static::deleting(function ($model) {
            throw new \Exception('Activity logs are immutable (append-only) and cannot be deleted.');
        });
    }

    /**
     * Relasi ke model User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
