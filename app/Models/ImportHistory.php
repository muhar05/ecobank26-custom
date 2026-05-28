<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportHistory extends Model
{
    protected $table = 'import_histories';

    protected $fillable = [
        'filename',
        'import_type',
        'user_id',
        'total_rows',
        'total_success',
        'total_failed',
        'total_skipped',
        'total_duplicates',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
