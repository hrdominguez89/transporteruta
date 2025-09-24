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
}

