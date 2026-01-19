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

    public function client()   { return $this->belongsTo(Client::class,  'clientId'); }
    public function driver()   { return $this->belongsTo(Driver::class,  'driverId'); }
    public function invoice()  { return $this->belongsTo(Invoice::class, 'invoiceId'); }
    public function vehicle(){ return $this->belongsTo(Vehicle::class,'vehicleId');}

    // Nota: FK camelCase en este proyecto
    public function travelItems()
    {
        return $this->hasMany(TravelItem::class, 'travelCertificateId');
    }

    /* =========================
     *  Helpers de totales
     * ========================= */

    /** Tarifa fija base (precio del ítem FIJO de la constancia) */
    public function getTarifaFijaBaseAttribute(): float
    {
        return (float) ($this->travelItems()->where('type', 'FIJO')->value('price') ?? 0);
    }

    /** Ítems que suman directo (excluye remito, descuento, adicional y peajes) */
    public function getSubtotalSinPeajesAttribute(): float
    {
        return (float) $this->travelItems()
            ->whereNotIn('type', ['REMITO', 'DESCUENTO', 'ADICIONAL', 'PEAJE'])
            ->sum('price');
    }

    /** Total de peajes (no gravan y no son descontables) */
    public function getTotalPeajesAttribute(): float
    {
        return (float) $this->travelItems()->where('type', 'PEAJE')->sum('price');
    }

    /** Suma de descuentos de monto (guardados positivos en price) */
    public function getTotalDescuentosAttribute(): float
    {
        return (float) $this->travelItems()
            ->where('type', 'DESCUENTO')
            ->where(function ($q) {
                $q->whereNull('percent')->orWhere('percent', 0);
            })
            ->sum('price');
    }

    /** NUEVO: suma de descuentos por porcentaje (percent > 0) */
    public function getTotalDescuentoPorcentajeAttribute(): float
    {
        // Sumamos los %; luego se aplican sobre la base gravada en bloque
        return (float) $this->travelItems()
            ->where('type', 'DESCUENTO')
            ->where('percent', '>', 0)
            ->sum('percent');
    }

    /** Monto adicional = ∑(percent/100 * tarifa_fija) para cada ADICIONAL (price=0) */
    public function getMontoAdicionalAttribute(): float
    {
        $tarifaFija = (float) $this->travelItems()->where('type', 'FIJO')->value('price');
        if ($tarifaFija <= 0) return 0.0;

        return (float) $this->travelItems()
            ->where('type', 'ADICIONAL')
            ->get()
            ->sum(function ($item) use ($tarifaFija) {
                // Compatibilidad: 'percent' es la columna real; 'porcentaje' legacy
                $p = (float) ($item->percent ?? $item->porcentaje ?? 0);
                return ($p / 100.0) * $tarifaFija;
            });
    }

    /**
     * REEMPLAZAR: descuento_aplicable
     * Antes: sólo montos y topeado a subtotal.
     * Ahora: aplica (1) descuentos de monto, luego (2) % sobre la base ya descontada.
     * La base gravada incluye adicionales y excluye peajes.
     */
    public function getDescuentoAplicableAttribute(): float
    {
        // Base gravada = subtotal sin peajes + adicionales
        $base0 = $this->subtotal_sin_peajes + $this->monto_adicional;

        // 1) Descuento de monto (topeado a la base)
        $descMonto = min($this->total_descuentos, $base0);
        $base1     = max(0, $base0 - $descMonto);

        // 2) Descuento por porcentaje sobre base ya descontada
        $pct = max(0, (float) $this->total_descuento_porcentaje);
        $descPct = $pct > 0 ? round($base1 * ($pct / 100), 2) : 0.0;

        return round($descMonto + $descPct, 2);
    }

    /**
     * REEMPLAZAR: total_calculado
     * Total = base gravada final + peajes
     */
    public function getTotalCalculadoAttribute(): float
    {
        // Base gravada final (sin peajes) = (subtotal + adicionales) - descuentos (monto + %)
        $gravada = max(
            0,
            ($this->subtotal_sin_peajes + $this->monto_adicional) - $this->descuento_aplicable
        );

        return round($gravada + $this->total_peajes, 2);
    }

    /**
     * REEMPLAZAR: iva_calculado
     * IVA 21% sobre base gravada final; 0 si cliente EXENTO.
     */
    public function getIvaCalculadoAttribute(): float
    {
        $gravada = max(
            0,
            ($this->subtotal_sin_peajes + $this->monto_adicional) - $this->descuento_aplicable
        );

        // Detectar EXENTO (si querés incluir MONOTRIBUTO, agregalo al match)
        $cond  = strtoupper($this->client->ivaCondition ?? $this->client->iva_condition ?? '');
        $esExento = str_contains($cond, 'EXENTO');

        return $esExento ? 0.0 : round($gravada * 0.21, 2);
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

