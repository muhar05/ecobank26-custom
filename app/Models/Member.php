<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'kk_id', 'member_code', 'name', 'phone', 'birth_date', 'gender', 'address', 'relationship'];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }
        return $this->birth_date->age;
    }

    public function getAgeGroupAttribute(): ?string
    {
        $age = $this->age;
        if ($age === null) {
            return null;
        }

        if ($age <= 5) {
            return 'balita';
        } elseif ($age <= 12) {
            return 'anak';
        } elseif ($age <= 17) {
            return 'remaja';
        } elseif ($age <= 59) {
            return 'dewasa';
        } else {
            return 'lansia';
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kk(): BelongsTo
    {
        return $this->belongsTo(Kk::class, 'kk_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(CommunityContribution::class);
    }

    public function wasteCustomers(): HasMany
    {
        return $this->hasMany(WasteCustomer::class);
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
