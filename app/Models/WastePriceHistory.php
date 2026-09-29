<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WastePriceHistory extends Model
{
    public const SOURCE_MANUAL = 'MANUAL';
    public const SOURCE_IMPORT = 'IMPORT';

    protected $fillable = [
        'waste_price_id',
        'collector_id',
        'waste_category_id',
        'old_price',
        'new_price',
        'changed_at',
        'changed_by_user_id',
        'admin_device_id',
        'source',
        'import_batch_id',
    ];

    protected function casts(): array
    {
        return [
            'old_price'  => 'decimal:2',
            'new_price'  => 'decimal:2',
            'changed_at' => 'datetime',
        ];
    }

    public function wastePrice(): BelongsTo
    {
        return $this->belongsTo(WastePrice::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Collector::class);
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    public function adminDevice(): BelongsTo
    {
        return $this->belongsTo(AdminDevice::class, 'admin_device_id');
    }
}
