<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\TravelCertificate;
use App\Models\Receipt;
use App\Models\Credit;
// TEAM NOTE: usamos Schema para detectar columnas existentes en runtime
use Illuminate\Support\Facades\Schema;

class Invoice extends Model
{
    use HasFactory;

    /**     
     * - En DB la columna del punto de venta puede venir como snake_case: point_of_sale
     *   o (histórico) camelCase: pointOfSale, según el entorno.
     * - En vistas/controladores usamos a veces camelCase: pointOfSale
     *   => agregamos accessors/mutators para que ambos funcionen.
     * - También normalizamos totalWithIva (tuvimos totalWhitIva mal escrito en fillable).
     *   Mantenemos compatibilidad sin romper nada.
     */
    protected $fillable = [
        'number',
        'pointOfSale',     // compat camelCase (no rompe si se usa mass-assignment desde request)
        'point_of_sale',   // nombre real en DB (preferido)
        'date',
        'total',
        'iva',
        'totalWhitIva',    // (typo histórico) lo dejamos para no romper forms viejos
        'totalWithIva',    // nombre correcto que realmente queremos usar
        'total_with_iva',  // por si en algún entorno quedó con snake_case
        'invoiced',
        'paid',
        'balance',
        'clientId',
        'receiptId',
    ];
    public function debits()
    {
        return $this->hasMany(Debit::class, 'invoiceId');
    }

    // Que se castee numéricos como float
    protected $casts = [
        'total'         => 'float',
        'iva'           => 'float',
        'totalWithIva'  => 'float',
        'balance'       => 'float',
        'date'          => 'datetime',
    ];

    /* ===================== Compatibilidad de nombres de columnas ===================== */

    // Getter: permite $invoice->pointOfSale aunque la columna sea point_of_sale
    public function getPointOfSaleAttribute()
    {
        // priorizamos snake (DB actual), luego camel (DB histórica)
        return $this->attributes['point_of_sale'] ?? $this->attributes['pointOfSale'] ?? null;
    }

    // Setter: ahora escribe en la columna que exista en la tabla (evita "Unknown column")
    public function setPointOfSaleAttribute($value)
    {
        // TEAM NOTE: si existe point_of_sale lo usamos; si no, caemos a pointOfSale.
        if (Schema::hasColumn($this->getTable(), 'point_of_sale')) {
            $this->attributes['point_of_sale'] = (int) $value;
        } elseif (Schema::hasColumn($this->getTable(), 'pointOfSale')) {
            $this->attributes['pointOfSale'] = (int) $value;
        } else {
            // fallback defensivo para entornos raros
            $this->attributes['point_of_sale'] = (int) $value;
        }
    }

    // Getter: unifica totalWithIva (y variantes)
    public function getTotalWithIvaAttribute()
    {
        // orden de prioridad: camelCase actual, snake_case alternativo, y typo histórico
        if (array_key_exists('totalWithIva', $this->attributes)) {
            return $this->attributes['totalWithIva'];
        }
        if (array_key_exists('total_with_iva', $this->attributes)) {
            return $this->attributes['total_with_iva'];
        }
        if (array_key_exists('totalWhitIva', $this->attributes)) {
            return $this->attributes['totalWhitIva'];
        }
        return null;
    }

    // Setter: escribe en la columna que exista (evita inconsistencias entre entornos)
    public function setTotalWithIvaAttribute($value)
    {
        // TEAM NOTE: preferimos escribir donde exista realmente.
        if (Schema::hasColumn($this->getTable(), 'totalWithIva')) {
            $this->attributes['totalWithIva'] = (float) $value;
        } elseif (Schema::hasColumn($this->getTable(), 'total_with_iva')) {
            $this->attributes['total_with_iva'] = (float) $value;
        } elseif (Schema::hasColumn($this->getTable(), 'totalWhitIva')) {
            // compat con el typo histórico si quedó en alguna DB
            $this->attributes['totalWhitIva'] = (float) $value;
        } else {
            // fallback razonable
            $this->attributes['totalWithIva'] = (float) $value;
        }
    }

    /* ============================= Relaciones ============================= */

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function travelCertificates()
    {
        return $this->hasMany(TravelCertificate::class, 'invoiceId');
    }

    public function receipts()
    {
        return $this->belongsToMany(Receipt::class)->withPivot('paymentMethodId', 'taxId', 'total', 'taxAmount');
    }

    public function credits()
    {
        return $this->hasMany(Credit::class, 'invoiceId');
    }
}

