<?php

namespace App\Http\Controllers;

use App\Models\TravelItem;
use App\Models\TravelCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TravelItemController extends Controller
{
    /**
     * Guarda un ítem en una Constancia.
     *
     * REFACTORIZACIÓN (claridad):
     * - ADICIONAL: ahora el form puede enviar el porcentaje como `percent` (nuevo)
     *   o `porcentaje` (legacy). La validación acepta ambos y exige que
     *   al menos uno venga si `type=ADICIONAL`. En BD guardamos SIEMPRE en `percent`
     *   y dejamos `price=0`; el monto real lo calculan el modelo (`display_price`,
     *   `total_calculado`) y las vistas/PDF. Esto evita migraciones y desincronizaciones.
     */

    
    public function store(Request $request, $travelCertificateId)
{
    // Buscar la constancia (para asegurar que existe)
    $travelCertificate = TravelCertificate::findOrFail($travelCertificateId);

    // ============== Validación (acepta percent o porcentaje) ==============
    $rules = [
        'type'          => 'required|in:HORA,KILOMETRO,PEAJE,ADICIONAL,FIJO,MULTIDESTINO,DESCARGA,DESCUENTO,REMITO',
        'description'   => 'nullable|string|max:255',

        // ADICIONAL → aceptar percent/porcentaje, ignorarlos si NO es adicional
        'percent'       => 'exclude_unless:type,ADICIONAL|nullable|numeric|min:0',
        'porcentaje'    => 'exclude_unless:type,ADICIONAL|nullable|numeric|min:0',

        // REMITO → exigir remito_number sólo en REMITO, ignorarlo si no
        'remito_number' => 'exclude_unless:type,REMITO|required|string|max:50',

        // Para ADICIONAL o REMITO ignoramos completamente price
        'price'         => 'exclude_if:type,ADICIONAL|exclude_if:type,REMITO|nullable|numeric|min:0',

        // HORA
        'totalHours'    => 'exclude_unless:type,HORA|nullable|integer|min:0',
        'totalMinutes'  => 'exclude_unless:type,HORA|nullable|integer|in:0,15,30,45',

        // KILOMETRO
        'distance'      => 'exclude_unless:type,KILOMETRO|nullable|numeric|min:0',
    ];

    $messages = [
        'remito_number.required_if' => 'Ingresá el número de Remito.',
        'price.required_unless'     => 'Ingresá un precio para este tipo de ítem.',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    // Exigir "al menos uno" (percent o porcentaje) cuando es ADICIONAL
    $validator->after(function ($v) use ($request) {
        if ($request->type === 'ADICIONAL'
            && !$request->filled('percent')
            && !$request->filled('porcentaje')) {
            $v->errors()->add('percent', 'Ingresá el porcentaje para el Adicional.');
        }
    });

    // >>> DEBUG: si falla, lo logueamos y devolvemos con errores (sin explotar)
    if ($validator->fails()) {
        Log::error('storeTravelItem VALIDATION FAILED', $validator->errors()->toArray());
        return back()->withErrors($validator)->withInput();
    }
    // =====================================================================
    
    if ($request->input('type') === 'DESCUENTO') {
        // 1) ¿Hay base gravada?
        $hayBaseGravada = $travelCertificate->travelItems()
            ->whereNotIn('type', ['PEAJE', 'REMITO'])
            ->exists();

        if (!$hayBaseGravada) {
            return back()
                ->withErrors([
                    'price' => 'No podés aplicar descuentos si la constancia solo tiene peajes/remitos.',
                ])
                ->withInput();
        }

        /* Reglas que no deje Aplicar Descuentos en Peajes*/
        // 2) ¿Está intentando “descontar peajes”?
        $desc = mb_strtolower((string)$request->input('description', ''));
        if (str_contains($desc, 'peaje') || str_contains($desc, 'peajes')) {
            return back()
                ->withErrors([
                    'description' => 'No se permiten descuentos sobre peajes.',
                ])
                ->withInput();
        }
    }

    $type = $request->input('type');

    $item = new TravelItem();
    $item->travelCertificateId = $travelCertificate->id;
    $item->type        = $type;
    $item->description = $request->input('description');

    switch ($type) {
        case 'ADICIONAL':
            $rawPercent    = $request->input('percent', $request->input('porcentaje', 0));
            $item->percent = (float) str_replace(',', '.', $rawPercent);
            $item->price   = 0.0; // el monto real lo calcula el modelo/vistas (display_price)
            if (empty($item->description)) {
                $item->description = 'Adicional ' . $item->percent . '%';
            }
            break;

        case 'REMITO':
            $item->remito_number = $request->input('remito_number');
            $item->price         = 0.0;
            if (empty($item->description)) {
                $item->description = 'Remito N° ' . $item->remito_number;
            }
            break;

        case 'HORA':
            $hours   = (int) $request->input('totalHours', 0);
            $mins    = (int) $request->input('totalMinutes', 0);
            $totalHs = round($hours + ($mins / 60), 2);
            $unit    = (float) $request->input('price', 0);

            $item->totalTime = $totalHs;
            $item->price     = $unit * $totalHs;

            $item->description = trim(($request->input('description') ?: '') . ' (' .
                $hours . ':' . str_pad((string)$mins, 2, '0', STR_PAD_LEFT) .
                ' Hs. x $ ' . number_format($unit, 2, ',', '.') . ')');
            break;

        case 'KILOMETRO':
            $dist = (float) $request->input('distance', 0);
            $unit = (float) $request->input('price', 0);

            $item->distance = $dist;
            $item->price    = $dist * $unit;

            $item->description = trim(($request->input('description') ?: '') . ' (' .
                $dist . ' Kms. x $ ' . number_format($unit, 2, ',', '.') . ')');
            break;

        case 'DESCUENTO':
            // Guardamos positivo; el modelo lo descuenta del subtotal gravado (no peajes)
            $item->price = (float) $request->input('price', 0);
            break;

        default:
            // PEAJE, FIJO, MULTIDESTINO, DESCARGA, etc.
            $item->price = (float) $request->input('price', 0);
            break;
    }

    $item->save();

    // Recalcular totales/IVA con la fórmula central del modelo
    $travelCertificate->recalcTotals();

    return redirect()->route('showTravelCertificate', $travelCertificate->id)
        ->with('success', 'Ítem agregado correctamente.');
}

    public function delete($id, $travelCertificateId)
    {
        $travelItem = TravelItem::findOrFail($id);
        $travelCertificate = TravelCertificate::findOrFail($travelCertificateId);

        $travelItem->delete();

        // De nuevo, centralizamos en la fórmula del modelo
        $travelCertificate->recalcTotals();

        return redirect()->route('showTravelCertificate', $travelCertificateId)
            ->with('success', 'Ítem eliminado correctamente.');
    }
}
