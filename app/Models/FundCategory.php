<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundCategory extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(CommunityContribution::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(CommunityExpense::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(CommunityCashLedger::class);
    }
}
