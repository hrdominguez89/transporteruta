<?php

namespace App\Http\Controllers;

use App\Models\TravelItem;
use App\Models\TravelCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;


class TravelItemController extends Controller
{
    public function store(Request $request, $travelCertificateId)
    {
        $travelCertificate = TravelCertificate::findOrFail($travelCertificateId);
        
        if($travelCertificate->invoiced =='SI')
        {
            return redirect()->route('showTravelCertificate', $travelCertificate->id)
            ->with('error', 'Ya esta facturada.');
        }


        // Reglas de negocio adicionales para DESCUENTO
        // esta validacion tiene sentido. la 1ra la 2da ya no
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
    public function storeMultipleRemitos(Request $request, $id)
    {
        $travelCertificate = TravelCertificate::findOrFail($id);
        $remitos = $request->get('remitos');
        $remitos = array_values(array_unique($remitos));

        if (empty($remitos)) {
            return back()->with('error', 'No se detectaron números de remito válidos.');
        }
        $creados = 0;
        $duplicados = [];

        DB::transaction(function () use ($remitos, $travelCertificate, &$creados, &$duplicados) {
            foreach ($remitos as $nro) {
                try {
                    $item = new TravelItem();
                    $item->travelCertificateId = $travelCertificate->id;
                    $item->type = 'REMITO';
                    $item->description = 'Remito N° ' . $nro;
                    $item->remito_number = $nro;
                    // Política decidida: remitos no afectan importes
                    $item->price = 0;
                    $item->percent = 0;
                    $item->save();
                    $creados++;
                } catch (QueryException $e) {
                    // Duplicado por índice único (SQLSTATE 23000) => lo marcamos y seguimos
                    if (($e->errorInfo[0] ?? '') === '23000') {
                        $duplicados[] = $nro;
                    } else {
                        throw $e;
                    }
                }
            }
        });

        if ($creados && empty($duplicados)) {
            return back()->with('success', "Se agregaron {$creados} remito(s) correctamente.");
        }

        if ($creados && $duplicados) {
            return back()->with('success', "Se agregaron {$creados} remito(s). Se ignoraron por duplicado: " . implode(', ', $duplicados));
        }
        return back()->with('warning', 'Todos los remitos ingresados ya existían para esta constancia.');
    }
}
