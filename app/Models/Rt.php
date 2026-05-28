<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rt extends Model
{
    use HasFactory;

    protected $fillable = [
        'rt_number',
        'description',
    ];

    public function kks(): HasMany
    {
        return $this->hasMany(Kk::class, 'rt_id');
    }

    public function adminUsers(): HasMany
    {
        return $this->hasMany(User::class, 'rt_id');
    }
}
