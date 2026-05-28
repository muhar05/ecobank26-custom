<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteCategory extends Model
{
    protected $fillable = ['name', 'unit', 'code', 'category_group', 'waste_category_group_id'];

    public const GROUPS = [
        'Plastik',
        'Kertas',
        'Logam',
        'Kaca',
        'Organik',
        'Elektronik',
        'Lainnya',
    ];

    /**
     * Relationship: belongsTo WasteCategoryGroup
     */
    public function wasteCategoryGroup()
    {
        return $this->belongsTo(WasteCategoryGroup::class, 'waste_category_group_id');
    }

    public static function generateCode($groupInput): string
    {
        $prefix = 'UNC';
        if ($groupInput instanceof WasteCategoryGroup) {
            $prefix = $groupInput->code;
        } elseif (is_numeric($groupInput)) {
            $group = WasteCategoryGroup::find($groupInput);
            if ($group) {
                $prefix = $group->code;
            }
        } elseif (is_string($groupInput)) {
            $group = WasteCategoryGroup::where('code', $groupInput)->orWhere('name', $groupInput)->first();
            if ($group) {
                $prefix = $group->code;
            } else {
                $prefixMap = [
                    'Plastik' => 'PLS',
                    'Kertas' => 'KRT',
                    'Logam' => 'LOG',
                    'Kaca' => 'KCA',
                    'Organik' => 'ORG',
                    'Elektronik' => 'ELK',
                    'Lainnya' => 'LNY',
                ];
                $prefix = isset($prefixMap[$groupInput]) ? $prefixMap[$groupInput] : strtoupper(substr($groupInput, 0, 3));
            }
        }

        $prefix = strtoupper(trim($prefix));

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
