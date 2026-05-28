<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteCategory extends Model
{
    protected $fillable = ['name', 'unit', 'code', 'category_group'];

    public const GROUPS = [
        'Plastik',
        'Kertas',
        'Logam',
        'Kaca',
        'Organik',
        'Elektronik',
        'Lainnya',
    ];

    public static function generateCode(?string $group): string
    {
        $prefixMap = [
            'Plastik' => 'PLS',
            'Kertas' => 'KRT',
            'Logam' => 'LOG',
            'Kaca' => 'KCA',
            'Organik' => 'ORG',
            'Elektronik' => 'ELK',
            'Lainnya' => 'LNY',
        ];

        $prefix = $group && isset($prefixMap[$group]) ? $prefixMap[$group] : 'UNC';

        $lastCode = self::where('code', 'like', $prefix . '.%')
            ->orderByRaw('LENGTH(code) DESC')
            ->orderBy('code', 'desc')
            ->value('code');

        if ($lastCode) {
            $parts = explode('.', $lastCode);
            $lastNumber = (int)end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . '.' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }
}
