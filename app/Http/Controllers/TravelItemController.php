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
        $newTravelItem = new TravelItem;
        $newTravelItem->type = $request->type;
        $price = $request->price;
        if($request->type == 'HORA')
        {
            $newTravelItem->totalTime = $request->totalTime;
            $newTravelItem->price = $newTravelItem->totalTime * $price;
        }
        elseif($request->type == 'KILOMETRO')
        {
            $newTravelItem->distance = $request->distance;
            $newTravelItem->price = $newTravelItem->distance * $price;
        }
        else
        {
            $newTravelItem->price = $price;

        }
        $travelCertificate = TravelCertificate::find($travelCertificateId);
        $travelCertificate->total += $newTravelItem->price;
        if($request->type != 'PEAJE')
        {
            $travelCertificate->iva += $newTravelItem->price * 0.21;
        }
        $newTravelItem->travelCertificateId = $travelCertificateId;
        $newTravelItem->save();
        $driver = Driver::find($travelCertificate->driverId);
        $travelCertificate->driverPayment += ($newTravelItem->price * $driver->percent) / 100;
        $travelCertificate->save();
        return redirect(route('showTravelCertificate', $travelCertificateId));
    }

    public function delete($id, $travelCertificateId)
    {
        $travelItem = TravelItem::find($id);
        $travelCertificate = TravelCertificate::find($travelCertificateId);
        $travelCertificate->total -= $travelItem->price;
        if($travelItem->type != 'PEAJE')
        {
            $travelCertificate->iva -= $travelItem->price * 0.21;
        }
        $driver = Driver::find($travelCertificate->driverId);
        $travelCertificate->driverPayment -= ($travelItem->price * $driver->percent) / 100;
        $travelCertificate->save();
        $travelItem->delete();
        return redirect(route('showTravelCertificate', $travelCertificateId));
    }
}
