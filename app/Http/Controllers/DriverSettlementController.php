<?php

namespace App\Http\Controllers;

use App\Models\DriverSettlement;
use App\Models\Driver;
use App\Models\PaymentMethod;
use App\Http\Requests\StoreDriverSettlementRequest;
use App\Http\Requests\UpdateDriverSettlementRequest;
use App\Models\TravelItem;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\DB;

class DriverSettlementController extends Controller
{
    public function driverSettlements(Request $request)
    {
        $query = DriverSettlement::query();
        
        if($request->filled('driver_id')) $query->where("driverId",$request->driver_id);
        if($request->filled('desde')) $query->where('dateFrom', '>=', $request->desde);
        if($request->filled('hasta')) $query->where('dateTo', '<=', $request->hasta);
        if($request->filled('tipo'))    $query->whereHas('driver', fn($q) => $q->where('type', $request->tipo));
        if($request->filled('subtipo')) $query->whereHas('driver', fn($q) => $q->where('subtipo', $request->subtipo));
        
        $data['driverSettlements'] = $query->get();
        $data['drivers'] = Driver::orderBy('name', 'asc')->get();
        return view('driverSettlement.index', $data);
    }

    public function generate(Request $request)
    {
        $modo = $request->modo;

        switch ($modo) {
            case 'propios':
                $driverIds = Driver::where('type', 'PROPIO')->pluck('id');
                break;
            case 'eventuales':
                $driverIds = Driver::where('type', 'TERCERO')->pluck('id');
                break;
            case 'algunos':
                $driverIds = collect($request->driverId);
                break;
            case 'todos':
            default:
                $driverIds = Driver::pluck('id');
                break;
        }

        $number = (DriverSettlement::max('number') ?? 0);
        $lastId = null;
        $from = $request->dateFrom;
        $to = $request->dateTo; 
        $driverIdsPurgados = $this->purgarPorTcPeriodo($driverIds,$from,$to);
        foreach ($driverIdsPurgados as $driverId) {
            $newDriverSettlement = new DriverSettlement;
            $newDriverSettlement->number = ++$number;
            $newDriverSettlement->date = Carbon::now()->format('Y-m-d');
            $newDriverSettlement->total = 0;
            $newDriverSettlement->driverId = $driverId;
            $newDriverSettlement->paymentMethodId = 0;
            $newDriverSettlement->dateFrom = $from;
            $newDriverSettlement->dateTo = $to;
            if ($request->filled('tipo')) $newDriverSettlement->tipo = $request->tipo;
            $newDriverSettlement->save();

            $lastId = $newDriverSettlement->id;
        }
        if($lastId==null)
        {
            session()->flash('flag', true);
            session()->flash('message', 'No se encontraron constancias de viajes para este/os choferes por lo que no se genero ninguna liquidacion.');    
            $data['driverSettlements'] = DriverSettlement::all();
            $data['drivers'] = Driver::orderBy('name', 'asc')->get();
            return view('driverSettlement.index', $data);
        }
        return redirect(route('showDriverSettlement', $lastId));
    }
    private function purgarPorTcPeriodo($driverIds,$from,$to)
    {
        return Driver::whereIn('id',$driverIds)
                ->whereHas('travelCertificates',function ($q) use ($from,$to){
                    $q->whereBetween('date',[$from,$to]);
                })
                ->pluck('id');
    }

    public function show($id)
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
        $data['paymentMethods'] = PaymentMethod::all();
        return view('driverSettlement.show', $data);
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
        $data['estacionamientos'] = 0;
        // Calculamos el total de agency y sumamos los peajes
        foreach ($data['driverSettlement']->travelCertificates as $travelCertificate) {
            $data['totalAgency'] += $travelCertificate->driverPayment;
            $data['estacionamientos'] += $travelCertificate->totalestacionamiento ;
            // Agregar el total de peajes a cada travelCertificate
            $travelCertificate->totalTolls = TravelItem::where('type', 'PEAJE')
                ->where('travelCertificateId', $travelCertificate->id)
                ->sum('price');
            $data['totalTolls'] += $travelCertificate->totalTolls;
        }

        $pdf = Pdf::loadView('driverSettlement.pdf', $data);

        $pdf->setPaper('A4', 'landscape');

        // Definir márgenes personalizados
        $options = $pdf->getDomPDF()->getOptions();
        $options->set('defaultPaperSize', 'a4');
        $options->set('defaultPaperOrientation', 'landscape');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        $pdf->getDomPDF()->setOptions($options);

        return $pdf->stream('Liquidacion Nro ' . $data['driverSettlement']->id . ' - ' . $data['driverSettlement']->driver->name . '.pdf');
    }

    public function edit(Request $request)
    {
        $DS = DriverSettlement::find($request->id);

        if($request->filled('tipo')) $DS->tipo = $request->tipo;
        if($request->filled('desde'))$DS->dateFrom = $request->desde;
        if($request->filled('hasta')) $DS->dateTo= $request->hasta;
        $DS->save();
        return $this->show($DS->id);
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

    public function delete($id)
    {
        // Iniciar una transacción para asegurarse de que todas las operaciones se realicen correctamente.
        DB::beginTransaction();

        try {
            $driverSettlement = DriverSettlement::find($id);

            // Si no se encuentra el DriverSettlement, redirigir con mensaje
            if (!$driverSettlement) {
                return redirect(route('driverSettlements'))->with('error', 'Liquidación no encontrada');
            }

            // Verificar si la liquidación ya ha sido liquidada
            if ($driverSettlement->liquidated == 'SI') {
                return redirect(route('driverSettlements'))->with('error', 'No se puede eliminar una liquidación ya liquidada');
            }

            // Obtener y actualizar los certificados de viaje
            $travelsCertificates = $driverSettlement->travelCertificates;
            foreach ($travelsCertificates as $travelCertificate) {
                $travelCertificate->driverSettlementId = 0;
                $travelCertificate->isPaidToDriver = 'NO';
                $travelCertificate->save();
            }

            // Eliminar el DriverSettlement
            $driverSettlement->delete();

            // Confirmar transacción
            DB::commit();

            return redirect(route('driverSettlements'))->with('success', 'Liquidación eliminada correctamente');
        } catch (\Exception $e) {
            // Si ocurre un error, hacer rollback
            DB::rollBack();

            return redirect(route('driverSettlements'))->with('error', 'Hubo un problema al eliminar la liquidación');
        }
    }
}
