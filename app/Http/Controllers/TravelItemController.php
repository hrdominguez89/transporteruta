<?php

namespace App\Http\Controllers;

use App\Models\TravelItem;
use App\Models\TravelCertificate;
use App\Models\Driver;
use App\Http\Requests\StoreTravelItemRequest;
use App\Http\Requests\UpdateTravelItemRequest;

class TravelItemController extends Controller
{
    public function store(StoreTravelItemRequest $request, $travelCertificateId)
    {
        $tarifa_fija = TravelItem::where('travelCertificateId', $travelCertificateId)->where('type', 'FIJO')->value('price');

        $newTravelItem = new TravelItem;
        $newTravelItem->type = $request->type;
        $newTravelItem->description = $request->description;
        // $price = $request->price;s
        if ($request->type == 'HORA') {
            // Convertir minutos a decimal
            $newTravelItem->totalTime = round($request->totalHours + ($request->totalMinutes / 60), 2);
            $newTravelItem->price = $newTravelItem->totalTime * $request->price;
            $newTravelItem->description .= ' (' . $request->totalHours . ':' . $request->totalMinutes . ' Hs. x $ ' . number_format($request->price, 2, ',', '.') . ')';
        } elseif ($request->type == 'KILOMETRO') {
            $newTravelItem->distance = $request->distance;
            $newTravelItem->price = $newTravelItem->distance * $request->price;
            $newTravelItem->description .= ' (' . $request->distance . ' Kms. x $ ' . number_format($request->price, 2, ',', '.') . ')';
        } elseif ($request->type == 'ADICIONAL') {
            $newTravelItem->percent = $request->porcentaje;
            $newTravelItem->price = ($tarifa_fija / 100) *  $request->porcentaje;
            $newTravelItem->description .= ' (' . number_format($request->porcentaje, 2, ',', '.') . ' % de $ ' . number_format($tarifa_fija, 2, ',', '.') . ')';
        } else {
            $newTravelItem->price = $request->price;
        }
        $travelCertificate = TravelCertificate::find($travelCertificateId);
        $travelCertificate->total += $newTravelItem->price;
        if ($request->type != 'PEAJE') {
            $travelCertificate->iva += $newTravelItem->price * 0.21;
        }
        $newTravelItem->travelCertificateId = $travelCertificateId;
        $newTravelItem->save();
        // no se porque hacia esto, pero lo comento
        // $driver = Driver::find($travelCertificate->driverId);
        // $travelCertificate->driverPayment += ($newTravelItem->price * $driver->percent) / 100;
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
