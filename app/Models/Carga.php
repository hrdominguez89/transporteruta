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
        'cantidad',//SE VA 
        'fecha_de_recepcion',
        'fecha_de_entrega',// SE VA 
        'precio',
        'espacio',//SE VA 
        'tipo', // SE VA 
        'destino',
        'client_id',
        'remito_id',
        'estado_de_envio',// SE VA 
        'notificacion_de_recepcion',// PROXIMAMENTE SE USARA?
        'notificacion_de_entrega',// PROXIMAMENTE SE USARA?
        'cliente_tercero_id',
        'motivo',
        'travel_certificate_id',
        'cantidad_bulto',
        'cantidad_pallet_normal',
        'cantidad_pallet_grande',
        'rechazado_bulto',// PROXIMAMENTE SE USARA?
        'rechazado_pallet_normal',// PROXIMAMENTE SE USARA?
        'rechazado_pallet_grande',  // PROXIMAMENTE SE USARA?
        'driver_id',
        'estado_envio_id',// PROXIMAMENTE SE USARA?
        'pallet_costo',
        'bulto_costo',
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
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    public function cliente_tercero()
    {
        return $this->belongsTo(ClienteTercero::class, 'cliente_tercero_id');
    }
    public function travel_certificate()
    {
        return $this->belongsTo(TravelCertificate::class, 'travel_certificate_id');
    }
    public function estadoEnvios()
    {
        return $this->hasMany(EstadoEnvio::class);
    }

    public function estadoActual()
    {
        return $this->hasOne(EstadoEnvio::class)->where('estado_actual', true);
    }
}