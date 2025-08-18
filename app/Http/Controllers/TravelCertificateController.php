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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $newTravelCertificate->commission_type = $request->commission_type;

        // Lógica para establecer el tipo de comisión
        if ($request->commission_type == "porcentaje pactado") {
            // Obtener el porcentaje del driver seleccionado y asignarlo al campo `percent`
            $driver = Driver::find($request->driverId);
            $newTravelCertificate->percent = $driver->percent; // Asignamos el porcentaje del driver
        } else {
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
        $data['travelCertificate'] = TravelCertificate::find($id);
        $data['tarifa_fija'] = TravelItem::where('travelCertificateId', $id)->where('type', 'FIJO')->value('price');
        $data['tiene_tarifa_adicional'] = TravelItem::where('travelCertificateId', $id)
            ->where('type', 'ADICIONAL')
            ->exists();
        $data['clients'] = Client::orderBy('name', 'asc')->get();
        $data['drivers'] = Driver::orderBy('name', 'asc')->get();
        return view('travelCertificate.show', $data);
    }

    public function update(UpdateTravelCertificateRequest $request, $id)
    {
        $travelCertificate = TravelCertificate::find($id);
        $travelCertificate->number = $request->number;
        $travelCertificate->date = $request->date;
        $travelCertificate->destiny = $request->destiny;
        $travelCertificate->clientId = $request->clientId;
        $travelCertificate->driverId = $request->driverId;
        $travelCertificate->commission_type = $request->commission_type;

        // Lógica para establecer el tipo de comisión
        if ($request->commission_type == "porcentaje pactado") {
            // Obtener el porcentaje del driver seleccionado y asignarlo al campo `percent`
            $driver = Driver::find($request->driverId);
            $travelCertificate->percent = $driver->percent; // Asignamos el porcentaje del driver
        } else {
            // Almacenar porcentaje o monto fijo dependiendo del tipo de comisión
            if ($request->commission_type == 'porcentaje') {
                $travelCertificate->percent = $request->percent;
                $travelCertificate->fixed_amount = null; // Asegurarse de que `fixed_amount` sea nulo si no se utiliza
            } else {
                $travelCertificate->fixed_amount = $request->fixed_amount;
                $travelCertificate->percent = null; // Asegurarse de que `percent` sea nulo si no se utiliza
            }
        }
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

    public function addToInvoice(Request $request, $id)
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

    public function removeFromInvoice(Request $request, $id)
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

    public function addToDriverSettlement(Request $request, $id)
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

    public function removeFromDriverSettlement(Request $request, $id)
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

    /**
     * Delete a travel certificate only if it's not invoiced and clean relations.
     */
    public function destroy($id)
    {
        $travelCertificate = TravelCertificate::find($id);
        if (!$travelCertificate) {
            return redirect()->route('travelCertificates');
        }

        // Only allow deletion when not invoiced
        if ($travelCertificate->invoiced !== 'NO') {
            return redirect()->route('travelCertificates');
        }

        DB::transaction(function () use ($travelCertificate) {
            // If linked to an invoice (defensive), subtract totals
            if ($travelCertificate->invoiceId && $travelCertificate->invoiceId != 0) {
                $invoice = Invoice::find($travelCertificate->invoiceId);
                if ($invoice) {
                    $invoice->total -= $travelCertificate->total;
                    $invoice->iva -= $travelCertificate->iva;
                    $invoice->save();
                }
            }

            // If linked to a driver settlement (defensive), subtract totals
            if ($travelCertificate->driverSettlementId && $travelCertificate->driverSettlementId != 0) {
                $driverSettlement = DriverSettlement::find($travelCertificate->driverSettlementId);
                if ($driverSettlement) {
                    $driverSettlement->total -= ($travelCertificate->total - $travelCertificate->driverPayment);
                    $driverSettlement->save();
                }
            }

            // Delete travel items (migration has onDelete cascade, but explicitly delete to ensure app-level hooks)
            foreach ($travelCertificate->travelItems as $item) {
                $item->delete();
            }

            // Finally delete the travel certificate
            $travelCertificate->delete();
        });

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Constancia de viaje eliminada correctamente.']);
        }

        return redirect()->route('travelCertificates');
    }

    /**
     * Check if a travel certificate number already exists (AJAX)
     */
    public function checkNumberExists()
    {
        $number = request('number');
        $id = request('id'); // optional: id to ignore (for update)
        if (empty($number)) {
            return response()->json(['exists' => false]);
        }
        $query = TravelCertificate::where('number', $number);
        if (!empty($id)) {
            $query->where('id', '!=', $id);
        }
        $exists = $query->exists();
        return response()->json(['exists' => $exists]);
    }
}
