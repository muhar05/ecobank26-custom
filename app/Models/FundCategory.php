<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundCategory extends Model
{
    protected $fillable = ['name', 'description', 'target_amount', 'is_active', 'is_mandatory', 'monthly_amount'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_mandatory' => 'boolean',
            'target_amount' => 'decimal:2',
            'monthly_amount' => 'decimal:2',
        ];
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
