<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArcaFacturas extends Model
{
    use HasFactory;

    protected $table = 'arca_facturas';

    protected $fillable = [
        'invoiceId',
        'cae',
        'fecha_vto',
        'fecha_cbte',
        'punto_vta',
        'tipo_cbte',
        'cbt_desde',
        'imp_total',
        'resultado',
    ];

    protected $casts = [
        'imp_total' => 'float',
        'punto_vta' => 'integer',
        'tipo_cbte' => 'integer',
        'cbt_desde' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoiceId');
    }
}