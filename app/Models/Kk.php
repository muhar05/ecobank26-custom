<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kk extends Model
{
    use HasFactory;

    protected $table = 'kks';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CONTRACT = 'kontrak';
    public const STATUS_MOVED = 'pindah';
    public const STATUS_VACANT = 'kosong';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_CONTRACT => 'Kontrak',
            self::STATUS_MOVED => 'Pindah',
            self::STATUS_VACANT => 'Kosong',
        ];
    }

    protected $fillable = [
        'rt_id',
        'kk_number',
        'family_head',
        'address',
        'phone',
        'status',
    ];

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'kk_id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'kk_id');
    }

    public function scopeActiveOrContract(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'kontrak']);
    }
}
