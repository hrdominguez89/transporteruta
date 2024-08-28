<?php

namespace App\Http\Controllers;

use App\Models\TravelCertificate;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\DriverSettlement;
use App\Models\TravelItem;
use App\Http\Requests\StoreTravelCertificateRequest;
use App\Http\Requests\UpdateTravelCertificateRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class TravelCertificateController extends Controller
{
    public function travelCertificates()
    {
        $travelCertificates = TravelCertificate::all();
        $clients = Client::all();
        $drivers = Driver::all();
        return view('travelCertificate.index', ['travelCertificates'=>$travelCertificates, 'clients'=>$clients, 'drivers'=>$drivers]);
    }
  
    public function store(StoreTravelCertificateRequest $request)
    {
        $newTravelCertificate = new TravelCertificate;
        $newTravelCertificate->number = $request->number;
        $newTravelCertificate->date = $request->date;
        $newTravelCertificate->destiny = $request->destiny;
        $newTravelCertificate->clientId = $request->clientId;
        $newTravelCertificate->driverId = $request->driverId;
        $newTravelCertificate->invoiceId = 0;
        $newTravelCertificate->driverSettlementId = 0;
        $newTravelCertificate->save();
        return redirect(route('showTravelCertificate', $newTravelCertificate->id));
    }

    public function show($id)
    {
        $travelCertificate = TravelCertificate::find($id);
        $clients = Client::all();
        $drivers = Driver::all();
        return view('travelCertificate.show', ['travelCertificate'=>$travelCertificate, 'clients'=>$clients, 'drivers'=>$drivers]);
    }

    public function update(UpdateTravelCertificateRequest $request, $id)
    {
        $travelCertificate = TravelCertificate::find($id);
        $travelCertificate->number = $request->number;
        $travelCertificate->date = $request->date;
        $travelCertificate->clientId = $request->clientId;
        $travelCertificate->driverId = $request->driverId;
        $travelCertificate->driverPayment = $request->driverPayment;
        $travelCertificate->destiny = $request->destiny;
        $travelCertificate->save();
        return redirect(route('showTravelCertificate', $travelCertificate->id));
    }

    public function generateTravelCertificatePdf($id)
    {
        $travelCertificate = TravelCertificate::find($id);
        $tolls = TravelItem::where('type', 'PEAJE')->where('travelCertificateId', $id);
        $totalTolls = $tolls->sum('price');
        $pdf = Pdf::loadView('travelCertificate.pdf', ['travelCertificate'=>$travelCertificate, 'totalTolls'=>$totalTolls]);
        return $pdf->stream('Constancia-'.$travelCertificate->client->name.'pdf');
    }

    public function addToInvoice(UpdateTravelCertificateRequest $request, $id)
    {
        $travelCertificate = TravelCertificate::find($id);
        $travelCertificate->invoiceId = $request->invoiceId;
        $invoice = Invoice::find($request->invoiceId);
        $invoice->total += $travelCertificate->total;
        $invoice->iva += $travelCertificate->iva;
        $travelCertificate->invoiced = 'SI';
        $travelCertificate->save();
        $invoice->save();
        return redirect(route('showInvoice', $travelCertificate->invoiceId));
    }

    public function removeFromInvoice(UpdateTravelCertificateRequest $request, $id)
    {
        $travelCertificate = TravelCertificate::find($id);
        $invoice = Invoice::find($request->invoiceId);
        $invoice->total -= $travelCertificate->total;
        $invoice->iva -= $travelCertificate->iva;
        $travelCertificate->invoiceId = 0;
        $travelCertificate->invoiced = 'NO';
        $travelCertificate->save();
        $invoice->save();
        return redirect(route('showInvoice', $invoice->id));
    }

    public function addToDriverSettlement(UpdateTravelCertificateRequest $request, $id)
    {
        $travelCertificate = TravelCertificate::find($id);
        $travelCertificate->driverSettlementId = $request->driverSettlementId;
        $driverSettlement = DriverSettlement::find($request->driverSettlementId);
        $driverSettlement->total += $travelCertificate->driverPayment;
        $travelCertificate->isPaidToDriver = 'SI';
        $travelCertificate->save();
        $driverSettlement->save();
        return redirect(route('showDriverSettlement', $travelCertificate->driverSettlementId));
    }

    public function removeFromDriverSettlement(UpdateTravelCertificateRequest $request, $id)
    {
        $travelCertificate = TravelCertificate::find($id);
        $driverSettlement = DriverSettlement::find($request->driverSettlementId);
        $driverSettlement->total -= $travelCertificate->driverPayment;
        $travelCertificate->driverSettlementId = 0;
        $travelCertificate->isPaidToDriver = 'NO';
        $travelCertificate->save();
        $driverSettlement->save();
        return redirect(route('showDriverSettlement', $driverSettlement->id));
    }
}
