<?php

namespace App\Http\Controllers;

use App\Models\TravelItem;
use App\Models\TravelCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TravelItemController extends Controller
{
    public function store(Request $request, $travelCertificateId)
    {
        // Busca la constancia (para asegurar que existe)
        $travelCertificate = TravelCertificate::findOrFail($travelCertificateId);

        // ================================================================
        // FIX #1 — Normalizar TYPE (por si llega "Por Hora/Kilómetro")
        // ================================================================
        $typeRaw = strtoupper(trim($request->input('type', '')));
        $mapType = [
            'POR HORA'      => 'HORA',
            'POR KILOMETRO' => 'KILOMETRO',
            'POR KILÓMETRO' => 'KILOMETRO',
        ];
        $request->merge(['type' => $mapType[$typeRaw] ?? $typeRaw]);

        //Elimíne estos separados de miles porque me dan un monto erróneo del tipo: $ 11.689.750,00 

        // ================================================================
        // Mejora — Normalizar PRICE si llega con separadores (12.000,50 -> 12000.50)
        // ================================================================
        /*if ($request->has('price')) {
            $request->merge([
                'price' => str_replace(['.', ','], ['', '.'], (string) $request->input('price'))
            ]);
        }*/

        // ============== Validación (ADICIONAL + nuevo DESCUENTO %) ==============
        $rules = [
            'type'          => 'required|in:HORA,KILOMETRO,PEAJE,ADICIONAL,FIJO,MULTIDESTINO,DESCARGA,DESCUENTO,REMITO',
            'description'   => 'nullable|string|max:255',

            // ADICIONAL → aceptar percent/porcentaje, ignorarlos si NO es adicional
            'percent'       => 'exclude_unless:type,ADICIONAL|nullable|numeric|min:0',
            'porcentaje'    => 'exclude_unless:type,ADICIONAL|nullable|numeric|min:0',

            // REMITO → exigir remito_number sólo en REMITO
            'remito_number' => 'exclude_unless:type,REMITO|required|string|max:50',

            // DESCUENTO → modo y, según el modo, price o discount_percent
            'discount_mode'    => 'exclude_unless:type,DESCUENTO|nullable|in:amount,percent',
            'discount_percent' => 'exclude_unless:discount_mode,percent|nullable|numeric|min:0|max:100',

            // price: NO exigir en ADICIONAL/REMITO ni cuando el descuento es por %
            // (el requerido se fuerza en un after() según el modo)
            'price'         => 'exclude_if:type,ADICIONAL|exclude_if:type,REMITO|exclude_if:discount_mode,percent|nullable|numeric|min:0',

            // HORA
            'totalHours'    => 'exclude_unless:type,HORA|nullable|integer|min:0',
            'totalMinutes'  => 'exclude_unless:type,HORA|nullable|integer|in:0,15,30,45',

            // KILOMETRO
            'distance'      => 'exclude_unless:type,KILOMETRO|nullable|numeric|min:0',
        ];

        $messages = [
            'remito_number.required'      => 'Ingresá el número de Remito.',
            'price.required_unless'       => 'Ingresá un precio para este tipo de ítem.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // ADICIONAL: exigir al menos uno (percent o porcentaje)
        $validator->after(function ($v) use ($request) {
            if ($request->type === 'ADICIONAL'
                && !$request->filled('percent')
                && !$request->filled('porcentaje')) {
                $v->errors()->add('percent', 'Ingresá el porcentaje para el Adicional.');
            }
        });

        // DESCUENTO: exigir price si es monto fijo, o discount_percent si es porcentaje
        $validator->after(function ($v) use ($request) {
            if ($request->type === 'DESCUENTO') {
                $mode = $request->input('discount_mode', 'amount');
                if ($mode === 'percent') {
                    if (!$request->filled('discount_percent')) {
                        $v->errors()->add('discount_percent', 'Ingresá el porcentaje de descuento.');
                    }
                } else { // amount (monto fijo) por defecto
                    if (!$request->filled('price')) {
                        $v->errors()->add('price', 'Ingresá el monto del descuento.');
                    }
                }
            }
        });

        // Si falla validación → registrar y volver
        if ($validator->fails()) {
            Log::error('storeTravelItem VALIDATION FAILED', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }
        // =====================================================================

        // Reglas de negocio adicionales para DESCUENTO
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

            // 2) ¿Está intentando “descontar peajes”? (por descripción)
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
                // Guardamos % en 'percent' y price=0. El monto se calcula en el modelo/vistas.
                $rawPercent    = $request->input('percent', $request->input('porcentaje', 0));
                $item->percent = (float) str_replace(',', '.', $rawPercent);
                $item->price   = 0.0;
                if (empty($item->description)) {
                    $item->description = 'Adicional ' . rtrim(rtrim(number_format($item->percent, 2, ',', '.'), '0'), ',') . '%';
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
                // ─────────────────────────────────────────────────────────────
                // Modo 1: MONTO FIJO → guardamos price (positivo) y percent = 0
                // Modo 2: PORCENTAJE → guardamos percent y price = 0
                // Nota: la resta (y su IVA) se hace en recalcTotals() sobre base gravada
                // ─────────────────────────────────────────────────────────────
                $mode = $request->input('discount_mode', 'amount');

                if ($mode === 'percent') {
                    $raw = (float) str_replace(',', '.', $request->input('discount_percent', 0));
                    $item->percent = max(0, $raw);
                    $item->price   = 0.0;

                    if (empty($item->description)) {
                        $item->description = 'Descuento ' . rtrim(rtrim(number_format($item->percent, 2, ',', '.'), '0'), ',') . '%';
                    }
                } else {
                    $item->price = (float) $request->input('price', 0); // guardar positivo; se descontará
                    $item->percent = 0.0;

                    if (empty($item->description)) {
                        $item->description = 'Descuento $ ' . number_format($item->price, 2, ',', '.');
                    }
                }
                break;

            default:
                // PEAJE, FIJO, MULTIDESTINO, DESCARGA, etc.
                $item->price = (float) $request->input('price', 0);
                break;
        }

        $item->save();

        // Recalcular totales/IVA con la fórmula central del modelo
        // IMPORTANTE: para que el descuento % impacte,
        // recalcTotals() debe aplicar percent de DESCUENTO sobre la base gravada (excluye PEAJE/REMITO).
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
