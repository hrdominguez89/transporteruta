<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoEnvio extends Model
{
    protected $table = 'estado_envios';

   protected $fillable = [
    'carga_id',
    'estado',
    'label',
    'horario',
    'estado_actual',
];

    protected $casts = [
        'horario' => 'date',
    ];

    public function carga()
    {
        return $this->belongsTo(Carga::class);
    }
}