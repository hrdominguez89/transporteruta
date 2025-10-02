<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TravelCertificate;

class TravelItem extends Model
{
    use HasFactory;

    /**
     * REFACTORIZACION:
     * Agregamos 'percent' (campo real en la BD para adicionales),
     * además de 'porcentaje' como fallback por compatibilidad,
     * y 'remito_number' para remitos.
     */
    protected $fillable = [
        'type',
        'price',
        'departureTime',
        'arrivalTime',
        'totalTime',
        'distance',
        'travelCertificateId',
        'percent',        // campo real en BD
        'porcentaje',     // alias opcional
        'remito_number',  // para remitos
    ];

    // NUEVO: exponer el importe calculado por fila en vistas/JSON.
    protected $appends = ['computed_price'];

    /**
     * Relación con TravelCertificate.
     * Corregido el nombre (antes estaba mal escrito como TravelCerficate).
     */
    public function travelCertificate()
    {
        return $this->belongsTo(TravelCertificate::class, 'travelCertificateId');
    }

    /**
     * REFACTORIZACION:
     * Accessor 'display_price' → valor real a mostrar en la vista/PDF.
     * 
     * - Si el ítem es ADICIONAL → calcula (percent/100 * tarifa_fija de la constancia).
     *   Así, aunque en DB el campo 'price' quede 0, en pantalla y en el PDF
     *   se ve el monto correcto.
     * 
     * - Para los demás tipos → devuelve el campo 'price' normalmente.
     */
    public function getDisplayPriceAttribute(): float
    {
        if ($this->type === 'ADICIONAL') {
            // Buscamos la tarifa fija asociada al certificado
            $tarifaFija = (float) optional(
                $this->travelCertificate
                    ->travelItems()
                    ->where('type', 'FIJO')
                    ->first()
            )->price;

            // usamos percent (real en BD), fallback a porcentaje
            $p = (float) ($this->percent ?? $this->porcentaje ?? 0);

            return $tarifaFija > 0 ? ($p / 100.0) * $tarifaFija : 0.0;
        }

        // Para cualquier otro tipo de ítem, devolvemos el precio normal
        return (float) ($this->price ?? 0);
    }

    /**
     * NUEVO:
     * Accessor 'computed_price' → importe para la grilla "Precio Total".
     * - DESCUENTO %: calcula el monto (negativo) sobre la misma base que usa
     *   TravelCertificate (subtotal sin peajes + adicionales − desc. de monto).
     * - DESCUENTO fijo: muestra el monto negativo.
     * - ADICIONAL %: calcula sobre tarifa fija (igual a display_price).
     * - Resto: devuelve price.
     */
    public function getComputedPriceAttribute(): float
    {
        $price = (float) ($this->price ?? 0);

        // 1) Descuentos
        if ($this->type === 'DESCUENTO') {
            $percent = (float) ($this->percent ?? $this->porcentaje ?? 0);

            // 1.a) Descuento porcentual: mostrar el monto calculado en negativo
            if ($percent > 0 && $this->travelCertificate) {
                $c = $this->travelCertificate;

                // Base gravada, alineada a TravelCertificate:
                $base0     = (float) $c->subtotal_sin_peajes + (float) $c->monto_adicional;
                // Descuentos de MONTO ya cargados (los % tienen price=0):
                $descMonto = min((float) $c->total_descuentos, $base0);
                $baseFinal = max(0.0, $base0 - $descMonto);

                $montoDesc = round($baseFinal * ($percent / 100.0), 2);
                return -$montoDesc; // negativo (resta)
            }

            // 1.b) Descuento de monto fijo: negativo
            return -abs($price);
        }

        // 2) Adicional %: mismo cálculo que display_price
        if ($this->type === 'ADICIONAL') {
            $p = (float) ($this->percent ?? $this->porcentaje ?? 0);
            if ($p > 0 && $this->travelCertificate) {
                $tarifaFija = (float) $this->travelCertificate
                    ->travelItems()->where('type', 'FIJO')->value('price');
                return $tarifaFija > 0 ? round(($p / 100.0) * $tarifaFija, 2) : 0.0;
            }
            // Si fue adicional de monto, caemos al return general
        }

        // 3) Resto de tipos: price tal cual
        return $price;
    }
}
