<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranVerse extends Model
{
    protected $fillable = [
        'surah',
        'arabic_text',
        'translation',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
