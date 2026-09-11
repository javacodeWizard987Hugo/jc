<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'value' => 'string', // Keep as string, we'll cast it manually
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(function (SystemSetting $setting) {
            Cache::forget('system_setting:' . $setting->key);
        });
        static::deleted(function (SystemSetting $setting) {
            Cache::forget('system_setting:' . $setting->key);
        });
    }

    /**
     * Get the value attribute, cast to its proper type.
     *
     * @return mixed
     */
    public function getCastedValueAttribute()
    {
        $value = $this->attributes['value'];
        switch ($this->type) {
            case 'number':
                return (float) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            default: // string
                return $value;
        }
    }

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = Cache::rememberForever('system_setting:' . $key, function () use ($key) {
            return self::where('key', $key)->first();
        });

        if ($setting) {
            return $setting->casted_value;
        }

        return $default;
    }

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $type
     * @param string|null $description
     * @return Model
     */
    public static function setValue(string $key, $value, string $type = null, string $description = null)
    {
        $setting = self::firstOrNew(['key' => $key]);

        if (is_null($type)) {
            if (is_numeric($value)) $type = 'number';
            elseif (is_bool($value)) $type = 'boolean';
            elseif (is_array($value) || is_object($value)) $type = 'json';
            else $type = 'string';
        }

        if ($type === 'json') {
            $value = json_encode($value);
        } elseif ($type === 'boolean') {
            $value = $value ? '1' : '0';
        }

        $setting->value = (string) $value;
        $setting->type = $type;
        if ($description) {
            $setting->description = $description;
        }
        $setting->save();

        return $setting;
    }
}
