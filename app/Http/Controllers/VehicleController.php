<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use Symfony\Component\HttpFoundation\Request;

class VehicleController extends Controller
{
    public function vehicles()
    {
        $vehicles = Vehicle::all();
        return view('vehicle.index', ['vehicles'=>$vehicles]);
    }

    public function store(StoreVehicleRequest $request)
    {
        $newVehicle = new Vehicle;
        $newVehicle->name = $request->name;
        $newVehicle->save();
        return redirect(route('vehicles'));
    }

    public function update(UpdateVehicleRequest $request, $id)
    {
        $vehicle = Vehicle::find($id);
        $vehicle->name = $request->name;
        $vehicle->save();
        return redirect(route('vehicles'));
    }
    public function show(Request $request,$id)
    {
        $vehiculo = Vehicle::find($id);
        $recaudacion = 0;
        $peajes = 0;
        $hst = $request->historico ?? null;
        if($hst)
        {
            $recaudacion = $vehiculo->recaudacionHistorica();
            $peajes = $vehiculo->peajesHistoricos();
        }
        else
        {
            $desde = $request->desde ?? null;
            $hasta = $request->hasta ?? null;
            if($desde != null & $hasta != null)
            {
                $recaudacion = $vehiculo->reacaudacionPeriodo($desde,$hasta);
                $peajes = $vehiculo->peajesPeriodo($desde,$hasta);
            }
        }


        return view('vehicle.show', [ 
            'vehiculo' => $vehiculo,
            'recaudacion' => $recaudacion,
            'peajes' => $peajes
            ]);
    }
}
