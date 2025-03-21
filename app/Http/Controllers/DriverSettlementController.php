<?php

namespace App\Http\Controllers;

use App\Models\DriverSettlement;
use App\Models\Driver;
use App\Models\PaymentMethod;
use App\Http\Requests\StoreDriverSettlementRequest;
use App\Http\Requests\UpdateDriverSettlementRequest;
use App\Models\TravelItem;
use Barryvdh\DomPDF\Facade\Pdf;

class DriverSettlementController extends Controller
{
    public function driverSettlements()
    {
        $driverSettlements = DriverSettlement::all();
        $drivers = Driver::all();
        $paymentMethods = PaymentMethod::all();
        return view('driverSettlement.index', ['driverSettlements' => $driverSettlements, 'drivers' => $drivers, 'paymentMethods' => $paymentMethods]);
    }

    public function generate(StoreDriverSettlementRequest $request)
    {
        $newDriverSettlement = new DriverSettlement;
        $newDriverSettlement->number = $request->number;
        $newDriverSettlement->date = $request->date;
        $newDriverSettlement->total = 0;
        $newDriverSettlement->driverId = $request->driverId;
        $newDriverSettlement->paymentMethodId = 0;
        $newDriverSettlement->dateFrom = $request->dateFrom;
        $newDriverSettlement->dateTo = $request->dateTo;
        $newDriverSettlement->save();
        return redirect(route('showDriverSettlement', $newDriverSettlement->id));
    }

    public function show($id)
    {
        $driverSettlement = DriverSettlement::find($id);
        $paymentMethods = PaymentMethod::all();
        return view('driverSettlement.show', ['driverSettlement' => $driverSettlement, 'paymentMethods' => $paymentMethods]);
    }

    public function generateDriverSettlementPdf($id)
    {
        $data['driverSettlement'] = DriverSettlement::find($id);

        // Ordenamos los travelCertificates por date y luego por number
        $data['driverSettlement']->travelCertificates = $data['driverSettlement']->travelCertificates
            ->sortBy([
                ['date', 'asc'],   // Ordenar por fecha (ascendente)
                ['number', 'asc']  // Ordenar por número (ascendente)
            ]);

        $data['totalAgency'] = 0;

        $data['totalTolls'] = 0;

        // Calculamos el total de agency y sumamos los peajes
        foreach ($data['driverSettlement']->travelCertificates as $travelCertificate) {
            $data['totalAgency'] += $travelCertificate->driverPayment;

            // Agregar el total de peajes a cada travelCertificate
            $travelCertificate->totalTolls = TravelItem::where('type', 'PEAJE')
                ->where('travelCertificateId', $travelCertificate->id)
                ->sum('price');
                $data['totalTolls'] += $travelCertificate->totalTolls;
        }

        $pdf = Pdf::loadView('driverSettlement.pdf', $data);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('Liquidacion-' . $data['driverSettlement']->driver->name . '.pdf');
    }


    public function liquidated(UpdateDriverSettlementRequest $request, $id)
    {
        $driverSettlement = DriverSettlement::find($id);
        $driverSettlement->liquidated = 'SI';
        $driverSettlement->paymentMethodId = $request->paymentMethodId;
        $driverSettlement->save();
        return redirect(route('showDriverSettlement', $driverSettlement->id));
    }

    public function cancel($id)
    {
        $driverSettlement = DriverSettlement::find($id);
        $driverSettlement->liquidated = 'NO';
        $driverSettlement->paymentMethodId = 0;
        $driverSettlement->save();
        return redirect(route('showDriverSettlement', $driverSettlement->id));
    }
}
