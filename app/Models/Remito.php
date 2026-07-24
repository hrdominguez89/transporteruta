<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Remito extends Model
{
    use HasFactory;

    protected $table = 'remitos';

    protected $fillable = [
        'numero',
        'client_id',
        'path',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function cargas(): HasMany
    {
        return $this->hasMany(Carga::class, 'remito_id');
    }
}