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
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Else_;

class TravelCertificateController extends Controller
{
    public function travelCertificates()
    {
        $data['travelCertificates'] = TravelCertificate::all();
        $data['clients'] = Client::orderBy('name', 'asc')->get();
        $data['drivers'] = Driver::orderBy('name', 'asc')->get();
        $data['vehicles'] = Vehicle::orderBy('name', 'asc')->get();
        return view('travelCertificate.index', $data);
    }

     public function store(StoreTravelCertificateRequest $request)
    {
        $number = $request->number;
        $date = $request->date;
        $destiny = $request->destiny;
        $clientId = $request->clientId;
        $driverId = $request->driverId;
        $vehicleId = $request->vehicleId;
        $horaSalida = $request->horaSalida;
        $horaLlegada = $request->horaLlegada;
        $TC = TravelCertificate::where([
            ['number', '=', $number],
            ['date', '=', $date],
            ['destiny', '=', $destiny],
            ['driverId', '=', $driverId],
            ['clientId', '=', $clientId],
        ])->first();
            
        if($TC)
        {
            session()->flash('warning', 'Ya se registro esta constancia de viaje.El id de la constancia es :'.$TC->id);
            
            return redirect()->route('travelCertificates');

        }
        // Crear una nueva instancia de TravelCertificate
        $newTravelCertificate = new TravelCertificate;
        $newTravelCertificate->number = $number;
        $newTravelCertificate->date = $request->date;
        $newTravelCertificate->destiny = $request->destiny;
        $newTravelCertificate->clientId = $request->clientId;
        $newTravelCertificate->driverId = $request->driverId;
        $newTravelCertificate->invoiceId = 0; // Si necesitas gestionar facturas, ajusta este valor
        $newTravelCertificate->driverSettlementId = 0; // Si necesitas gestionar liquidaciones de choferes, ajusta este valor
        $newTravelCertificate->total = 0.00;
        $newTravelCertificate->iva = 0.00;
        $newTravelCertificate->commission_type = $request->commission_type;
        $newTravelCertificate->vehicleId = $vehicleId;
        $newTravelCertificate->horaLLegada = $horaLlegada;
        $newTravelCertificate->horaSalida = $horaSalida;
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
                $newTravelCertificate->percent = 0; // Asegurarse de que `percent` sea nulo si no se utiliza
            }
        }
        
        // Guardar el nuevo certificado de viaje
        $newTravelCertificate->save();

        // Redirigir al detalle de la constancia de viaje recién guardada
        return redirect(route('showTravelCertificate', $newTravelCertificate->id));
    }


    public function show($id)
    {
        $data['travelCertificate'] = TravelCertificate::with('vehicle')->find($id);
        $data['tarifa_fija'] = TravelItem::where('travelCertificateId', $id)->where('type', 'FIJO')->value('price');
        $data['tiene_tarifa_adicional'] = TravelItem::where('travelCertificateId', $id)
            ->where('type', 'ADICIONAL')
            ->exists();
        $data['clients'] = Client::orderBy('name', 'asc')->get();
        $data['drivers'] = Driver::orderBy('name', 'asc')->get();
        $data['vehicles'] = Vehicle::orderBy('name', 'asc')->get();
        return view('travelCertificate.show', $data);
    }

    public function update(Request $request, $id)
    {
        $travelCertificate = TravelCertificate::find($id);
        if($travelCertificate->invoiced == 'NO')
        {
            $travelCertificate->number = $request->number;
            $travelCertificate->date = $request->date;
            $travelCertificate->destiny = $request->destiny;
            $travelCertificate->clientId = $request->clientId;
            $travelCertificate->driverId = $request->driverId;
            $travelCertificate->commission_type = $request->commission_type;
            $travelCertificate->vehicleId = $request->vehicleId;
            $travelCertificate->horaLLegada = $request->horaLlegada;
            $travelCertificate->horaSalida = $request->horaSalida;
            // Lógica para establecer el tip= o de comisn
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
        }
        else
        {
            session()->flash('error', 'Este certificado ya esta facturado.');
        }            
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
        if($travelCertificate->invoiced =='NO')
        {

            $travelCertificate->invoiceId = $request->invoiceId;
            $invoice = Invoice::find($request->invoiceId);
            $invoice->total += $travelCertificate->total;
            $invoice->iva += $travelCertificate->iva;
            $travelCertificate->invoiced = 'SI';
            $travelCertificate->save();
            $invoice->save();
        }
        else
        {
            session()->flash('error', 'Este certificado ya esta facturado.');
        }
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
     * Add multiple travel certificates to an invoice.
     */
    public function addMultipleToInvoice(Request $request)
    {
        $ids = $request->input('ids', []);
        $invoiceId = $request->input('invoiceId');
        if (empty($ids) || empty($invoiceId)) {
            return redirect()->back();
        }

        DB::transaction(function () use ($ids, $invoiceId) {
            $invoice = Invoice::find($invoiceId);
            foreach ($ids as $id) {
                $tc = TravelCertificate::find($id);
                if (!$tc) continue;
                // skip if already invoiced
                if ($tc->invoiced === 'SI') continue;
                if(!$this->validarHorarios($tc)) continue;
                $tc->invoiceId = $invoiceId;
                $tc->invoiced = 'SI';
                $tc->save();
                $invoice->total += $tc->total;
                $invoice->iva += $tc->iva;
            }
            $invoice->save();
        });

        return redirect(route('showInvoice', $invoiceId));
    }
     public function validarHorarios($travel_certificate)
    {
        $items = TravelItem::where('type','HORA')
        ->where('travelCertificateId',$travel_certificate->id)->get();
        if(!$items->isNotEmpty())
        {
            return true;
        }
        if($travel_certificate->horaLLegada == null || $travel_certificate->horaSalida == null)
        {
            return false;
        }
        return true;
    }
    /**
     * Remove multiple travel certificates from an invoice.
     */
    public function removeMultipleFromInvoice(Request $request)
    {
        $ids = $request->input('ids', []);
        $invoiceId = $request->input('invoiceId');
        if (empty($ids) || empty($invoiceId)) {
            return redirect()->back();
        }

        DB::transaction(function () use ($ids, $invoiceId) {
            $invoice = Invoice::find($invoiceId);
            foreach ($ids as $id) {
                $tc = TravelCertificate::find($id);
                if (!$tc) continue;
                // skip if not invoiced
                if ($tc->invoiced !== 'SI') continue;
                $invoice->total -= $tc->total;
                $invoice->iva -= $tc->iva;
                $tc->invoiceId = 0;
                $tc->invoiced = 'NO';
                $tc->save();
            }
            $invoice->save();
        });

        return redirect(route('showInvoice', $invoiceId));
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

    public function addMultipleToDriverSettlement(Request $request)
    {
        $ids = $request->ids;
        $dsId = $request->driverSettlementId;
        $driverSettlement = DriverSettlement::find(  $dsId );
        foreach ($ids as $id) {
            $travelCertificate = TravelCertificate::find($id);
            $travelCertificate->driverSettlementId =   $dsId ;
            $driverSettlement->total += ($travelCertificate->total - $travelCertificate->driverPayment);
            $travelCertificate->isPaidToDriver = 'SI';
            $travelCertificate->save();
            $driverSettlement->save();
        }

        return response()->json([
            'success' => true,
            'redirect' => route('showDriverSettlement', $dsId)
        ]);
    }
    
}
