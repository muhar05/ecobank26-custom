<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositDetail extends Model
{
    protected $fillable = ['deposit_id', 'waste_category_id', 'material_label', 'weight', 'price_per_unit', 'subtotal'];

    protected function casts(): array
    {
        return ['weight' => 'decimal:2', 'price_per_unit' => 'decimal:2', 'subtotal' => 'decimal:2'];
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }

    /**
     * Historical exception: baris tanpa WasteCategory, hanya raw label
     * (mis. "Buku, Majalah, Brosur, Koran" / "Seng/Kawat").
     */
    public function isHistoricalException(): bool
    {
        return $this->waste_category_id === null;
    }

    /**
     * Label tampil: nama kategori kanonik, atau raw label historis.
     */
    public function getDisplayLabelAttribute(): string
    {
        return $this->wasteCategory?->name ?? $this->material_label ?? 'Material';
    }

    public function getDisplayUnitAttribute(): string
    {
        return $this->wasteCategory?->unit ?? 'kg';
    }
}
