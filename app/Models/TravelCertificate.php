<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Driver;
use App\Models\Client;
use App\Models\TravelItem;
use App\Models\Invoice;

class TravelCertificate extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'total', 'iva', 'date', 'destiny', 'clientId', 'driverId', 'invoiceId'];

    protected $casts = [
        'total' => 'decimal:2',
        'iva'   => 'decimal:2',
        'date'  => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driverId');
    }

    public function travelItems()
    {
        // Nota: en este proyecto la FK es camelCase: travelCertificateId
        return $this->hasMany(TravelItem::class, 'travelCertificateId');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoiceId');
    }

    /* =========================
     *  --------------------------------
     *  Objetivo: que el "ADICIONAL (%)" impacte sobre la Tarifa Fija (ítem FIJO)
     *  y que el total/IVA se calculen sin tocar migraciones.
     *
     *  - Base del adicional: precio del ítem FIJO de esta constancia.
     *  - El adicional NO guarda monto en DB; se calcula en runtime.
     *  - El descuento NO afecta PEAJES.
     *  - El IVA se calcula sobre lo gravado (excluye peajes).
     * ========================= */

    /** Tarifa fija base (precio del ítem FIJO de la constancia) */
    public function getTarifaFijaBaseAttribute(): float
    {
        return (float) ($this->travelItems()
            ->where('type', 'FIJO')
            ->value('price') ?? 0);
    }

    /** Ítems que suman directo (excluye remito, descuento, adicional y peajes) */
    public function getSubtotalSinPeajesAttribute(): float
    {
        return (float) $this->travelItems()
            ->whereNotIn('type', ['REMITO', 'DESCUENTO', 'ADICIONAL', 'PEAJE'])
            ->sum('price');
    }

    /** Total de peajes (no descontables) */
    public function getTotalPeajesAttribute(): float
    {
        return (float) $this->travelItems()
            ->where('type', 'PEAJE')
            ->sum('price');
    }

    /** Suma de descuentos (guardados como positivos) */
    public function getTotalDescuentosAttribute(): float
    {
        return (float) $this->travelItems()
            ->where('type', 'DESCUENTO')
            ->sum('price');
    }

    /** Monto adicional = ∑(porcentaje/100 * tarifa_fija) para cada ítem ADICIONAL */
    // app/Models/TravelCertificate.php

public function getMontoAdicionalAttribute(): float
{
    // base = precio del ítem FIJO de la constancia
    $tarifaFija = (float) $this->travelItems()->where('type', 'FIJO')->value('price');
    if ($tarifaFija <= 0) return 0.0;

    return (float) $this->travelItems()
        ->where('type', 'ADICIONAL')
        ->get()
        ->sum(function ($item) use ($tarifaFija) {
            // ⚠️ IMPORTANTE: en BD la columna es 'percent'. Usamos fallback a 'porcentaje' por compatibilidad.
            $p = (float) ($item->percent ?? $item->porcentaje ?? 0);
            return ($p / 100.0) * $tarifaFija;
        });
}

    /** Descuento aplicable (topeado al subtotal sin peajes) */
    public function getDescuentoAplicableAttribute(): float
    {
        return min($this->total_descuentos, $this->subtotal_sin_peajes);
    }

    /** Total calculado = (subtotal sin peajes − descuento) + peajes + adicionales */
    public function getTotalCalculadoAttribute(): float
    {
        $total = ($this->subtotal_sin_peajes - $this->descuento_aplicable)
               + $this->total_peajes
               + $this->monto_adicional;

        return max(0, round($total, 2));
    }

    /** IVA calculado (21%) sólo sobre lo gravado (excluye peajes) */
    public function getIvaCalculadoAttribute(): float
    {
        $baseGravada = ($this->subtotal_sin_peajes - $this->descuento_aplicable)
                     + $this->monto_adicional; // los adicionales sí son gravados
        $baseGravada = max(0, $baseGravada);

        return round($baseGravada * 0.21, 2);
    }

    /**
     * Recalcula y persiste total/iva usando la lógica anterior.
     * Llamar luego de crear/editar/eliminar TravelItems.
     */
    public function recalcTotals(): void
    {
        $this->total = $this->total_calculado;
        $this->iva   = $this->iva_calculado;
        $this->save();
    }
}
