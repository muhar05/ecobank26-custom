<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'member_code', 'name', 'phone', 'address'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(CommunityContribution::class);
    }

    public static function generateNextCode(): string
    {
        $last = static::withTrashed()
            ->where('member_code', 'like', 'WRG%')
            ->orderByRaw("CAST(SUBSTRING(member_code, 4) AS UNSIGNED) DESC")
            ->value('member_code');

        $nextNumber = $last ? ((int) substr($last, 3)) + 1 : 1;

        return 'WRG' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
