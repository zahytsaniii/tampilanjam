<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Ambil value setting berdasarkan key.
     */
    public static function getValue($key, $default = null)
    {
        return static::where('key', $key)->first()->value ?? $default;
    }

    /**
     * Set atau update setting.
     */
    public static function setValue($key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
