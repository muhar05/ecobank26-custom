<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'setting_group',
    ];

    /**
     * Get dynamic setting value with automatic type casting and fallback default.
     * Implements lightweight query caching.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = Cache::remember("app_setting:{$key}", 3600, function () use ($key) {
            return self::where('key', $key)->first();
        });

        if (!$setting || is_null($setting->value)) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Future-proof integer helper.
     */
    public static function getInt(string $key, int $default = 0): int
    {
        return (int) self::getValue($key, $default);
    }

    /**
     * Future-proof string helper.
     */
    public static function getString(string $key, string $default = ''): string
    {
        return (string) self::getValue($key, $default);
    }

    /**
     * Set setting value and invalidate cache instantly.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $settingGroup
     * @return self
     */
    public static function setValue(string $key, $value, string $type = 'string', ?string $settingGroup = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type,
                'setting_group' => $settingGroup,
            ]
        );

        // Clear cache instantly
        Cache::forget("app_setting:{$key}");

        return $setting;
    }

    /**
     * Cast raw string value to proper type.
     */
    protected static function castValue(string $value, string $type)
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'float':
            case 'double':
            case 'numeric':
                return (float) $value;
            case 'string':
            default:
                return $value;
        }
    }
}
