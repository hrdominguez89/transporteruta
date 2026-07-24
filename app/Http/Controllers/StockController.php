<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Carga;
use App\Models\Client;
use App\Models\Price;
use App\Models\Remito;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        // aca tengo que validar que tipo de usuario es 
        // que permiso tiene 
        // si tiene permiso cliente 
        // si tiene permiso admin
       // si es cliente tengo que buscarle las que le corresponden al client_id del usuario y si es admin simplemente todas. 
        //mas adelante implementaremos los filtros. 
        // depende del tipo de permiso que tenga el usuario se retorna a la vista correspondiente
        // client -> stock -> client -> index 
        // admin -> stock -> admin -> index 
        
        if (auth()->user()->isAdmin()) {
            $clientes = Client::all();
            $cargas = Carga::all(); 
            return view('stock.admin.index',['cargas' => $cargas,'clients'=>$clientes]);     
        }

    $cargas = Carga::where('client_id', auth()->user()->client_id)->get();

        return view('stock.client.index', [
            'cargas' => $cargas,
            'client' => auth()->user()->client,
        ]);
    }
    public function show(Request $request)
    {
        $carga = Carga::findOrFail($request->id);

        if (!auth()->user()->isAdmin() && $carga->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        if (auth()->user()->isAdmin()) {
            return view('stock.admin.show', ['carga' => $carga]);
        }
        return view('stock.client.show', ['carga' => $carga, 'client' => auth()->user()->client]);
    }
    public function edit(Request $request)
    {
        //edito la carga
        $carga = Carga::findOrFail($request->id);

        if (!auth()->user()->isAdmin() && $carga->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $data = $request->validate([
            'nombre'                    => 'sometimes|string|max:255',
            'cantidad'                  => 'sometimes|integer',
            'fecha_de_recepcion'        => 'sometimes|date',
            'fecha_de_entrega'          => 'nullable|date',
            'espacio'                   => 'nullable|string',
            'tipo'                      => 'sometimes|in:PALLET,BULTO',
            'destino'                   => 'nullable|string|max:255',
            'estado_de_envio'           => 'sometimes|in:ALMACEN,VIAJE,ENTREGADO,RECHAZADO',
            'notificacion_de_recepcion' => 'sometimes|boolean',
            'notificacion_de_entrega'   => 'sometimes|boolean',
            'espacio'                   => 'nullable|string|max:255',
        ]);
        $price = Price::where('type',$data['tipo'])->value('price');
        if($data['tipo'] == 'PALLET' & $data['espacio'] == 'EXTRA')
        {
            $price = $price * 1.5;
        }

        $data['precio'] = $price * $data['cantidad'];
        $carga->update($data);
        return view('stock.admin.show', ['carga' => $carga]);
    }
    public function generate(Request $request)
    {
        $data = $request->validate([
            'nombre'             => 'required|string|max:255',
            'cantidad'           => 'required|integer',
            'fecha_de_recepcion' => 'required|date',
            'fecha_de_entrega'   => 'nullable|date',
            'espacio'            => 'nullable|numeric',
            'tipo'               => 'required|in:PALLET,BULTO',
            'destino'            => 'nullable|string|max:255',
            'client_id'          => auth()->user()->isAdmin() ? 'required|exists:clients,id' : 'nullable',
            'espacio'      => 'nullable|string|max:255',
        ]);

        $data['client_id'] = auth()->user()->isAdmin()
            ? $data['client_id']
            : auth()->user()->client_id;
        $price = Price::where('type',$data['tipo'])->value('price');
        if($data['tipo'] == 'PALLET' & $data['espacio'] == 'EXTRA')
        {
            $price = $price * 1.5;
        }

        $data['precio'] = $price * $data['cantidad'];
        Carga::create($data);

        return redirect()->route('stock')->with('success', 'Carga generada correctamente.');
    }
    public function delete(Request $request,$id)
    {
        $carga = Carga::findOrFail($id);

        if (!auth()->user()->isAdmin() && $carga->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $carga->delete();

        return redirect()->route('stock')->with('success', 'Carga eliminada correctamente.');
    }
    public function updatePrice(Request $request)
    {
        $data = $request->validate([
            'type'   => 'required|in:PALLET,BULTO',
            'price' => 'required|numeric',
        ]);

        Price::where('type', $data['type'])->update(['price' => $data['price']]);

        return redirect()->route('stock')->with('success', 'Precio actualizado correctamente.');
    }
    public function storeRemito(Request $request)
    {
        $data = $request->validate([
            'numero' => 'required|string|max:50|unique:remitos,numero',
            'image'  => 'required|image|mimes:jpg,jpeg,png|max:4096',
            'carga_id' => 'required|exists:cargas,id',
        ]);

        $data['path'] = $request->file('image')->store('remitos', 'public');
        $c = Carga::find($data["carga_id"]);
        $data['client_id'] = $c->client->id;
        $r = Remito::create($data);
        $c->remito_id = $r->id;
        $c->save();
        return redirect()->back()->with('success', 'Remito cargado correctamente.');
    }
}
