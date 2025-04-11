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
        $data['travelCertificates'] = TravelCertificate::all();
        $data['clients'] = Client::orderBy('name', 'asc')->get();
        $data['drivers'] = Driver::orderBy('name', 'asc')->get();
        return view('travelCertificate.index', $data);
    }

    public function store(StoreTravelCertificateRequest $request)
    {
        // Crear una nueva instancia de TravelCertificate
        $newTravelCertificate = new TravelCertificate;
        $newTravelCertificate->number = $request->number;
        $newTravelCertificate->date = $request->date;
        $newTravelCertificate->destiny = $request->destiny;
        $newTravelCertificate->clientId = $request->clientId;
        $newTravelCertificate->driverId = $request->driverId;
        $newTravelCertificate->invoiceId = 0; // Si necesitas gestionar facturas, ajusta este valor
        $newTravelCertificate->driverSettlementId = 0; // Si necesitas gestionar liquidaciones de choferes, ajusta este valor

        // Lógica para establecer el tipo de comisión
        if ($request->commission_type == "chofer") {
            // Si no se seleccionó un tipo de comisión, lo establecemos por defecto como 'porcentaje'
            $newTravelCertificate->commission_type = 'porcentaje';

            // Obtener el porcentaje del driver seleccionado y asignarlo al campo `percent`
            $driver = Driver::find($request->driverId);
            $newTravelCertificate->percent = $driver->percent; // Asignamos el porcentaje del driver
        } else {
            // Si se selecciona un tipo de comisión, asignamos el valor correspondiente
            $newTravelCertificate->commission_type = $request->commission_type;

            // Almacenar porcentaje o monto fijo dependiendo del tipo de comisión
            if ($request->commission_type == 'porcentaje') {
                $newTravelCertificate->percent = $request->percent;
                $newTravelCertificate->fixed_amount = null; // Asegurarse de que `fixed_amount` sea nulo si no se utiliza
            } else {
                $newTravelCertificate->fixed_amount = $request->fixed_amount;
                $newTravelCertificate->percent = null; // Asegurarse de que `percent` sea nulo si no se utiliza
            }
        }

        // Guardar el nuevo certificado de viaje
        $newTravelCertificate->save();

        // Redirigir al detalle de la constancia de viaje recién guardada
        return redirect(route('showTravelCertificate', $newTravelCertificate->id));
    }


    public function show($id)
    {
        $travelCertificate = TravelCertificate::find($id);
        $clients = Client::all();
        $drivers = Driver::all();
        return view('travelCertificate.show', ['travelCertificate' => $travelCertificate, 'clients' => $clients, 'drivers' => $drivers]);
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
        $pdf = Pdf::loadView('travelCertificate.pdf', ['travelCertificate' => $travelCertificate, 'totalTolls' => $totalTolls]);
        return $pdf->stream('Constancia-' . $travelCertificate->client->name . 'pdf');
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
        $driverSettlement->total += ($travelCertificate->total - $travelCertificate->driverPayment);
        $travelCertificate->isPaidToDriver = 'SI';
        $travelCertificate->save();
        $driverSettlement->save();
        return redirect(route('showDriverSettlement', $travelCertificate->driverSettlementId));
    }

    public function removeFromDriverSettlement(UpdateTravelCertificateRequest $request, $id)
    {
        $travelCertificate = TravelCertificate::find($id);
        $driverSettlement = DriverSettlement::find($request->driverSettlementId);
        $driverSettlement->total -= ($travelCertificate->total - $travelCertificate->driverPayment);
        $travelCertificate->driverSettlementId = 0;
        $travelCertificate->isPaidToDriver = 'NO';
        $travelCertificate->save();
        $driverSettlement->save();
        return redirect(route('showDriverSettlement', $driverSettlement->id));
    }
}
