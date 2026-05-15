<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Settlement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fecha',
        'travel_certificate_id',
        'cliente_id',
        'chofer_porcentaje',
        'importe_neto',
        'base_recaudacion',
        'peajes',
        'estacionamiento',
        'chofer_total',
        'carga_descarga_b',
        'carga_descarga_n',
        'noche_b',
        'noche_n',
        'chofer_cd_n',
        'chofer_n_n',
        'base_recaudacion_n',
        'chofer_n',
        'diferencia',
        'comentarios',
    ];

    protected $casts = [
        'fecha' => 'date',
        'chofer_porcentaje' => 'decimal:2',
        'importe_neto' => 'decimal:2',
        'base_recaudacion' => 'decimal:2',
        'peajes' => 'decimal:2',
        'estacionamiento' => 'decimal:2',
        'chofer_total' => 'decimal:2',
        'carga_descarga_b' => 'decimal:2',
        'carga_descarga_n' => 'decimal:2',
        'noche_b' => 'decimal:2',
        'noche_n' => 'decimal:2',
        'chofer_cd_n' => 'decimal:2',
        'chofer_n_n' => 'decimal:2',
        'base_recaudacion_n' => 'decimal:2',
        'chofer_n' => 'decimal:2',
        'diferencia' => 'decimal:2',
    ];

    public function travelCertificate(): BelongsTo
    {
        return $this->belongsTo(TravelCertificate::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'cliente_id');
    }
}