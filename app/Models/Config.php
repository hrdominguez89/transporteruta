<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'config';

    protected $fillable = [
        'automatico',
        'dia',
        'hora',
    ];

    protected $casts = [
        'automatico' => 'boolean',
        'dia' => 'integer',
    ];
}