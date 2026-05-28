<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundCategory extends Model
{
    protected $fillable = ['name', 'description', 'target_amount', 'is_active', 'is_mandatory', 'monthly_amount', 'rt_id'];

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

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    /**
     * Scope: tampilkan kategori global (NULL) + milik RT tersebut.
     * Digunakan oleh Admin RT untuk memilih kategori saat create contribution/expense.
     */
    public function scopeVisibleToRt($query, ?int $rtId)
    {
        if ($rtId === null) {
            return $query->whereNull('rt_id');
        }
        return $query->where(fn($q) => $q->whereNull('rt_id')->orWhere('rt_id', $rtId));
    }

    /**
     * Scope: hanya kategori yang benar-benar dimiliki RT (non-null rt_id).
     */
    public function scopeOwnedByRt($query, int $rtId)
    {
        return $query->where('rt_id', $rtId);
    }
}
