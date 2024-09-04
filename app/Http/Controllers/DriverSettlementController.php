<?php

namespace App\Http\Controllers;

use App\Models\DriverSettlement;
use App\Models\Driver;
use App\Models\PaymentMethod;
use App\Http\Requests\StoreDriverSettlementRequest;
use App\Http\Requests\UpdateDriverSettlementRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class DriverSettlementController extends Controller
{
    public function driverSettlements()
    {
        $driverSettlements = DriverSettlement::all();
        $drivers = Driver::all();
        $paymentMethods = PaymentMethod::all();
        return view('driverSettlement.index', ['driverSettlements'=>$driverSettlements, 'drivers'=>$drivers, 'paymentMethods'=>$paymentMethods]);
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
       return view('driverSettlement.show', ['driverSettlement'=>$driverSettlement, 'paymentMethods'=>$paymentMethods]);
    }

    public function generateDriverSettlementPdf($id)
    {
        $driverSettlement = DriverSettlement::find($id);
        $totalAgency = 0;
        foreach($driverSettlement->travelCertificates as $travelCertificate)
        {
            $totalAgency += $travelCertificate->driverPayment;
        }
        $totalAgencyFormat = number_format($number = $totalAgency, $decimals = 2);
        $pdf = Pdf::loadView('driverSettlement.pdf', ['driverSettlement'=>$driverSettlement, 'totalAgency'=>$totalAgencyFormat]);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Liquidacion-'.$driverSettlement->driver->name.'pdf');
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
