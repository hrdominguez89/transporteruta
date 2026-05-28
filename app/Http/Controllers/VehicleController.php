<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Driver;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

class VehicleController extends Controller
{
    public function vehicles()
    {
        $vehicles = Vehicle::all();
        $drivers = Driver::orderBy('name', 'asc')->get();

        return view('vehicle.index', ['vehicles'=>$vehicles,'drivers'=>$drivers]);
    }

    public function store(StoreVehicleRequest $request)
    {
        $newVehicle = new Vehicle;
         $newVehicle->name = $request->name;
        $newVehicle->tipo = $request->tipo;
        $newVehicle->marca = $request->marca;
        $fecha = Carbon::createFromDate($request->anio);
        $newVehicle->anio = $fecha;
        $newVehicle->modelo = $request->modelo;
        $newVehicle->save();
        return redirect(route('vehicles'));
    }

    public function update(UpdateVehicleRequest $request, $id)
    {
        $vehicle = Vehicle::find($id);
        $vehicle->name = $request->name;
        $vehicle->tipo = $request->tipo;
        $vehicle->marca = $request->marca;
        $fecha = Carbon::createFromDate($request->anio);
        $vehicle->anio = $fecha;
        $vehicle->modelo = $request->modelo;
        $vehicle->driverId = $request->driverId;
        $vehicle->save();
         $recaudacion = 0;
        $peajes = 0;
        $hst = $request->historico ?? null;
        if($hst)
        {
            $recaudacion = $vehicle->recaudacionHistorica();
            $peajes = $vehicle->peajesHistoricos();
        }
        else
        {
            $desde = $request->desde ?? null;
            $hasta = $request->hasta ?? null;
            if($desde != null & $hasta != null)
            {
                $recaudacion = $vehicle->reacaudacionPeriodo($desde,$hasta);
                $peajes = $vehicle->peajesPeriodo($desde,$hasta);
            }
        }
        $drivers = Driver::orderBy('name', 'asc')->get();

         return view('vehicle.show', [ 
            'vehiculo' => $vehicle,
            'recaudacion' => $recaudacion,
            'peajes' => $peajes,
            'drivers' => $drivers

            ]);
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
        $drivers = Driver::orderBy('name', 'asc')->get();


        return view('vehicle.show', [ 
            'vehiculo' => $vehiculo,
            'recaudacion' => $recaudacion,
            'peajes' => $peajes,
            'drivers' => $drivers
            ]);
    }
}
