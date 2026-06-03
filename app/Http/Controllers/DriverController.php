<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function drivers(Request $request)
    {
        $vehicles = Vehicle::all();
        
        $query = Driver::query();

        if ($request->filled('vehicleId')) {
            $query->whereHas('vehicles', fn($q) => $q->where('id', $request->vehicleId));
        }

        if ($request->filled('tipo')) {
            $query->whereHas('vehicles', fn($q) => $q->where('tipo', $request->tipo));
        }

        if ($request->filled('modelo')) {
            $query->whereHas('vehicles', fn($q) => $q->where('modelo', $request->modelo));
        }
        $drivers = $query->get();
        $types = [];
        $models=[];

        foreach($vehicles as $v)
        {
            if($v->tipo!=null)
            {

                if(!in_array($v->tipo,$types))
                    {
                        $types[]=$v->tipo;
                    }
            }
            if($v->modelo!=null)
            {
                if(!in_array($v->modelo,$models))
                {
                    $models[]=$v->modelo;
                }
            }
        }
        return view('driver.index', ['drivers'=>$drivers,'types'=>$types,'vehicles'=>$vehicles,'models'=>$models]);
    }


    public function store(StoreDriverRequest $request)
    {
        $driver              = new Driver();
        $driver->name        = $request->name;
        $driver->dni         = $request->dni;
        $driver->address     = $request->address;
        $driver->city        = $request->city;
        $driver->phone       = $request->phone;
        $driver->type        = $request->type;
        if( $driver->type == 'TERCERO')
        {
            $driver->percent = $request->percent;
            $driver->subtipo = $request->hab_ev;
        }
        else
        {
            $driver->percent = null;
            $driver->subtipo = null;
        }
        
        $driver->save();

        return redirect()->route('drivers')->with('success', 'Chofer creado correctamente.');
    }

    public function show($id)
    {
        $driver = Driver::find($id);
        $vehicles = Vehicle::all();
        return view('driver.show', ['driver' => $driver, 'vehicles' => $vehicles]);
    }

    public function setVehicleToDriver(Request $request,$id)
    {
        $vehicle = Vehicle::find($request->vehicleId);
        $vehicle->driverId = $id;
        $vehicle->save();
        $driver = Driver::find($id);
        $vehicles = Vehicle::all();
        return view('driver.show', ['driver' => $driver, 'vehicles' => $vehicles]);
    }

    public function unsetVehicleToDriver(Request $request, $id)
    {
        $vehicle = Vehicle::find($request->id_vehicle);
        $vehicle->driverId = null;
        $vehicle->save();

        $driver = Driver::find($id);
        $vehicles = Vehicle::all();

        return view('driver.show', ['driver' => $driver, 'vehicles' => $vehicles]);
    }
    public function update(\App\Http\Requests\UpdateDriverRequest $request, $id)
    {
        $driver = \App\Models\Driver::findOrFail($id);

        $driver->name    = $request->name;
        $driver->dni     = $request->dni;
        $driver->address = $request->address;
        $driver->city    = $request->city;
        $driver->phone   = $request->phone;
        $driver->type    = $request->type;
        $driver->percent = $request->percent;
        if( $driver->type == 'TERCERO')
        {
            $driver->subtipo = $request->hab_ev;
        }
        else
        {
            $driver->subtipo = null;
        }

        $driver->save();

        return redirect()->route('showDriver', $driver->id)
            ->with('success', 'Chofer actualizado correctamente.');
    }
}

// Explicación:  
// 1. La clase DriverController maneja las operaciones relacionadas con los choferes.
// 2. El método store() utiliza StoreDriverRequest para validar y almacenar un nuevo chofer.        
// 3. El método update() utiliza UpdateDriverRequest para validar y actualizar un chofer existente.
// 4. Se asegura que los datos sean consistentes y maneja correctamente los valores nulos para vehicleId y percent.                                                                                                               