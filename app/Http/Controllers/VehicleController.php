<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;

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
}
