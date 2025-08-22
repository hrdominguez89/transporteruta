<?php

namespace App\Http\Controllers;

use App\Models\TravelItem;
use App\Models\TravelCertificate;
use App\Models\Driver;
use App\Http\Requests\StoreTravelItemRequest;
use App\Http\Requests\UpdateTravelItemRequest;

class TravelItemController extends Controller
{
    public function store(\App\Http\Requests\StoreTravelItemRequest $request, $travelCertificateId)
{
    $tarifa_fija = \App\Models\TravelItem::where('travelCertificateId', $travelCertificateId)
        ->where('type', 'FIJO')
        ->value('price');

    $travelCertificate = \App\Models\TravelCertificate::findOrFail($travelCertificateId);
    $client = $travelCertificate->client;

    $newTravelItem = new \App\Models\TravelItem;
    $newTravelItem->travelCertificateId = $travelCertificateId;
    $newTravelItem->type = $request->type;

    // === CASOS ESPECIALES PRIMERO ===
    if ($request->type === 'REMITO') {
        // SOLO guarda el remito, no afecta importes
        $remito = $request->remito_number;
        $newTravelItem->description = 'Remito N° ' . $remito;
        $newTravelItem->price = 0; // <- CLAVE
        // Si agregaste la columna:
        // $newTravelItem->remito_number = $remito;

    } elseif ($request->type === 'DESCUENTO') {
        $newTravelItem->description = $request->description;
        $newTravelItem->price = -abs((float)$request->price);

    } elseif ($request->type === 'HORA') {
        $newTravelItem->description = $request->description;
        $newTravelItem->totalTime = round($request->totalHours + ($request->totalMinutes / 60), 2);
        $newTravelItem->price = $newTravelItem->totalTime * $request->price;
        $newTravelItem->description .= ' (' . $request->totalHours . ':' . $request->totalMinutes . ' Hs. x $ ' . number_format($request->price, 2, ',', '.') . ')';

    } elseif ($request->type === 'KILOMETRO') {
        $newTravelItem->description = $request->description;
        $newTravelItem->distance = $request->distance;
        $newTravelItem->price = $newTravelItem->distance * $request->price;
        $newTravelItem->description .= ' (' . $request->distance . ' Kms. x $ ' . number_format($request->price, 2, ',', '.') . ')';

    } elseif ($request->type === 'ADICIONAL') {
        $newTravelItem->description = $request->description;
        $newTravelItem->percent = $request->porcentaje;
        $newTravelItem->price = ($tarifa_fija / 100) *  $request->porcentaje;
        $newTravelItem->description .= ' (' . number_format($request->porcentaje, 2, ',', '.') . ' % de $ ' . number_format($tarifa_fija, 2, ',', '.') . ')';

    } else { // PEAJE, FIJO, MULTIDESTINO, DESCARGA, etc.
        $newTravelItem->description = $request->description;
        $newTravelItem->price = (float)$request->price; // nunca null acá (lo valida el FormRequest)
    }

    // Totales e IVA
    $travelCertificate->total += $newTravelItem->price;
    if ($request->type !== 'PEAJE' && $client->ivaType !== "EXENTO") {
        $travelCertificate->iva += $newTravelItem->price * 0.21;
    }

    $newTravelItem->save();
    $travelCertificate->save();

    return redirect(route('showTravelCertificate', $travelCertificateId));
}

    public function delete($id, $travelCertificateId)
    {
        $travelItem = TravelItem::find($id);
        $travelCertificate = TravelCertificate::find($travelCertificateId);
        $travelCertificate->total -= $travelItem->price;
        if ($travelItem->type != 'PEAJE') {
            $travelCertificate->iva -= $travelItem->price * 0.21;
        }
        // no se porque hacia esto, pero lo comento
        // $driver = Driver::find($travelCertificate->driverId);
        // $travelCertificate->driverPayment -= ($travelItem->price * $driver->percent) / 100;
        $travelCertificate->save();
        $travelItem->delete();
        return redirect(route('showTravelCertificate', $travelCertificateId));
    }
}
