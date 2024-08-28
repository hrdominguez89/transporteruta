<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;

class DriverController extends Controller
{
    public function drivers()
    {
        $drivers = Driver::all();
        return view('driver.index', ['drivers'=>$drivers]);
    }

   
    public function store(StoreDriverRequest $request)
    {
        $newDriver = new Driver;
        $newDriver->name = $request->name;
        $newDriver->dni = $request->dni;
        $newDriver->address = $request->address;
        $newDriver->city = $request->city;
        $newDriver->phone = $request->phone;
        $newDriver->percent = $request->percent;
        $newDriver->type = $request->type;
        $newDriver->save();
        return redirect(route('showDriver', $newDriver->id));
    }

    public function show($id)
    {
        $driver = Driver::find($id);
        return view('driver.show', ['driver'=>$driver]);
    }

    public function update(UpdateDriverRequest $request, $id)
    {
        $driver = Driver::find($id);
        $driver->name = $request->name;
        $driver->dni = $request->dni;
        $driver->address = $request->address;
        $driver->city = $request->city;
        $driver->phone = $request->phone;
        $driver->percent = $request->percent;
        $driver->type = $request->type;
        $driver->save();
        return redirect(route('showDriver', $driver->id));
    }
}
