<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Carga extends Model
{
    use HasFactory;

    protected $table = 'cargas';

    protected $fillable = [
        'nombre',
        'cantidad',
        'fecha_de_recepcion',
        'fecha_de_entrega',
        'precio',
        'espacio',
        'tipo',
        'destino',
        'client_id',
        'remito_id',
        'estado_de_envio',
        'notificacion_de_recepcion',
        'notificacion_de_entrega',
        'cliente_tercero_id',
        'motivo',
        'travel_certificate_id'
    ];

    protected $casts = [
        'cantidad'                  => 'integer',
        'fecha_de_recepcion'        => 'date',
        'fecha_de_entrega'          => 'date',
        'precio'                    => 'decimal:2',
        'espacio'                   => 'string',
        'notificacion_de_recepcion' => 'boolean',
        'notificacion_de_entrega'   => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function remito(): BelongsTo
    {
        return $this->belongsTo(Remito::class, 'remito_id');
    }
    public function cliente_tercero()
    {
        return $this->belongsTo(ClienteTercero::class, 'cliente_tercero_id');
    }
    public function travel_certificate()
    {
        return $this->belongsTo(TravelCertificate::class, 'travel_certificate_id');
    }
}