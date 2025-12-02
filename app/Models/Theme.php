<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'background_image',
        'background_color',
        'text_color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Theme aktif untuk display.
     */
    public static function active()
    {
        return static::where('is_active', true)->first();
    }
}
