<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        switch ($setting->type) {
            case 'boolean':
                return (bool) $setting->value;
            case 'number':
                return is_numeric($setting->value) ? (int) $setting->value : $default;
            case 'json':
                return json_decode($setting->value, true) ?? $default;
            default:
                return $setting->value ?? $default;
        }
    }

    public static function set($key, $value)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return self::create([
                'key' => $key,
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : (is_array($value) ? 'json' : 'string')),
            ]);
        }

        $setting->value = is_array($value) ? json_encode($value) : $value;
        $setting->save();
        
        return $setting;
    }
}
