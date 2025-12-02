<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RunningText extends Model
{
    protected $fillable = [
        'message',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
