<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityExpense extends Model
{
    protected $fillable = [
        'fund_category_id', 'amount', 'date', 'description', 'recorded_by',
    ];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'decimal:2'];
    }

    public function fundCategory(): BelongsTo
    {
        return $this->belongsTo(FundCategory::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
