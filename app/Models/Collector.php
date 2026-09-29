<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collector extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'phone', 'address'];

    public function wastePrices(): HasMany
    {
        return $this->hasMany(WastePrice::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function materialAliases(): HasMany
    {
        return $this->hasMany(MaterialAlias::class, 'source_id');
    }
}
