<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    use HasFactory;
    protected $fillable = [
    'client_id',
    'nombre',
    'apellido',
    'categoria',
    'mail',
    'telefono',
    'comentario',
    'cliente_tercero_id'
    ];
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
    public function clienteTercero()
    {
        return $this->belongsTo(ClienteTercero::class, 'cliente_tercero_id');
    }
}
