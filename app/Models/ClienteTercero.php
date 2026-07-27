<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClienteTercero extends Model
{
    use HasFactory;


    protected $fillable = [
        'client_id',
        'contacto_id',
        'nombre',
        'codigo_postal',
        'direccion',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Contacto::class, 'contacto_id');
    }

    public function cargas(): HasMany
    {
        return $this->hasMany(Carga::class, 'cliente_tercero_id');
    }
}