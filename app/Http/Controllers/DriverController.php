<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;

class DriverController extends Controller
{
    public function drivers()
    {
        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        return view('driver.index', ['drivers' => $drivers, 'vehicles' => $vehicles]);
    }


 public function store(StoreDriverRequest $request) // 👈 usa la request tipada
    {
        $driver              = new Driver();
        $driver->name        = $request->name;
        $driver->dni         = $request->dni;
        $driver->address     = $request->address;
        $driver->city        = $request->city;
        $driver->phone       = $request->phone;
        $driver->type        = $request->type;
        // si es TERCERO pedimos porcentaje, si es PROPIO lo dejamos NULL (o 0 si queremos)
        $driver->percent     = $request->type === 'TERCERO' ? (float) $request->percent : null;
        // si no selecciona vehículo, guardamos NULL para no romper la FK
        $driver->vehicleId   = $request->filled('vehicleId') ? (int) $request->vehicleId : null;

        $driver->save();

        return redirect()->route('drivers')->with('success', 'Chofer creado correctamente.');
    }

    public function show($id)
    {
        $driver = Driver::find($id);
        $vehicles = Vehicle::all();
        return view('driver.show', ['driver' => $driver, 'vehicles' => $vehicles]);
    }

    // refactorizacion nueva método update()
    public function update(\App\Http\Requests\UpdateDriverRequest $request, $id)
    {
    $driver = \App\Models\Driver::findOrFail($id);

    $driver->name    = $request->name;
    $driver->dni     = $request->dni;
    $driver->address = $request->address;
    $driver->city    = $request->city;
    $driver->phone   = $request->phone;
    $driver->type    = $request->type;

    $driver->percent   = $request->type === 'TERCERO' ? (float)$request->percent : null;
    $driver->vehicleId = $request->filled('vehicleId') ? (int)$request->vehicleId : null;

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