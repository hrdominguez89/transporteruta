<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Carga;
use App\Models\Client;
use App\Models\ClienteTercero;
use App\Models\Contacto;
use App\Models\Driver;
use App\Models\EstadoEnvio;
use App\Models\Price;
use App\Models\Remito;
use App\Models\TravelCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StockController extends Controller
{
    public function index(Request $request)
    {
       if (auth()->user()->isAdmin()) {
            $cargas = Carga::when($request->client_id, function ($query, $clientId) {
                $query->where('client_id', $clientId);
            })->get();
            return view('stock.admin.index', [
                'cargas'            => $cargas,
                'clients'           => Client::all(),
                'clientes_terceros' => ClienteTercero::all(),
                'prices'            => Price::all()
            ]);
        }

        $cargas = Carga::where('client_id', auth()->user()->client_id)->get();

        return view('stock.client.index', [
            'cargas' => $cargas,
            'client' => auth()->user()->client
        ]);
    }
    public function show(Request $request)
    {
        $carga = Carga::findOrFail($request->id);

        if (!auth()->user()->isAdmin() && $carga->client_id !== auth()->user()->client_id) {
            abort(403);
        }

       $itemsConstraint = function ($q) use ($carga) {
            $q->where(function ($inner) use ($carga) {
                $inner->whereIn('type', ['PALLET', 'BULTO'])
                    ->orWhere(function ($q2) use ($carga) {
                        $q2->where('type', 'REMITO')
                            ->where('remito_number', $carga->remito?->numero);
                    });
            });
        };

        $travelCertificates = TravelCertificate::where('clientId', $carga->client_id)
            ->where('invoiced', 'NO')
            ->whereHas('travelItems', $itemsConstraint)
            ->with(['travelItems' => $itemsConstraint])
            ->get();

        $estado_envio = $carga?->estadoActual;

        if (auth()->user()->isAdmin()) {
            $clientes_tercero =  ClienteTercero::where('client_id', $carga->client_id)->get();
            return view('stock.admin.show', [
                'carga' => $carga,
                'clientes_terceros' => $clientes_tercero,
                'travel_certificates' => $travelCertificates,
                'estado_envio' => $estado_envio,
                'drivers' => Driver::all() ]);
        }
        return view('stock.client.show', [
            'carga' => $carga,
            'client' => auth()->user()->client,
            'estado_envio' => $estado_envio
            ]);
    }
    public function generate(Request $request)
    {
        if (!auth()->user()->isAdmin() ) {  
            abort(403);
        }
        $data = $request->validate([
            'nombre'             => 'required|string|max:255',
            'fecha_de_recepcion' => 'required|date',
            'cantidad_bulto'     => 'nullable|integer',
            'cantidad_pallet_normal'   => 'nullable|integer',
            'cantidad_pallet_grande'   => 'nullable|integer',
            'destino'            => 'nullable|string|max:255',
            'client_id'          => auth()->user()->isAdmin() ? 'required|exists:clients,id' : 'nullable',
            'cliente_tercero_id' => ['nullable'],
        ]);

        $data['client_id'] = auth()->user()->isAdmin()
            ? $data['client_id']
            : auth()->user()->client_id;

        if($data['cantidad_bulto'])
        {
            $data['precio'] = $data['cantidad_bulto'] * Price::where('type', 'BULTO')->value('price');
        }
         if($data['cantidad_pallet_normal'])
        {
            $data['precio'] += $data['cantidad_pallet_normal'] * Price::where('type', 'PALLET')->value('price');
        }
         if($data['cantidad_pallet_grande'])
        {
            $data['precio'] += $data['cantidad_pallet_grande'] * (Price::where('type', 'PALLET')->value('price') * 1.5);
        }
        
        $c = Carga::create($data);

        $estadoEnvio = new EstadoEnvio();
        $estadoEnvio->horario = $data['fecha_de_recepcion'];
        $estadoEnvio->estado_actual = true;
        $estadoEnvio->carga_id = $c->id;
        $estadoEnvio->save();
      
        return redirect()->route('stock')->with('success', 'Carga generada correctamente.');
    }
    public function edit(Request $request)
    {      
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $carga = Carga::findOrFail($request->id);

        $data = $request->validate([
            'nombre'                    => 'sometimes|string|max:255',
            'fecha_de_recepcion'        => 'sometimes|date',
            'fecha_de_entrega'          => 'nullable|date',
            'destino'                   => 'nullable|string|max:255',
            'notificacion_de_recepcion' => 'sometimes|boolean',
            'notificacion_de_entrega'   => 'sometimes|boolean',
            'cliente_tercero_id'        => 'nullable|integer',
            'motivo'                => 'nullable|string|max:255',
            'liquidado'             => 'nullable|boolean',
            'travel_certificate_id' => 'nullable',
            'driver_id'             => 'nullable'
        ]);
        
        $carga->update($data);
        
        return redirect()->route('showcarga', $carga->id)
            ->with('success', 'Carga actualizada correctamente.');
    }
    public function edittypeofcarga(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $carga = Carga::findOrFail($request->id);
        
        $data = $request->validate([
            'cantidad_bulto'                  => 'sometimes|integer',
            'cantidad_pallet_normal'          => 'sometimes|integer',
            'cantidad_pallet_grande'          => 'sometimes|integer',
            ]);
            
        if($data['cantidad_bulto'])
        {
            $data['precio'] = $data['cantidad_bulto'] * Price::where('type', 'BULTO')->value('price');
        }
         if($data['cantidad_pallet_normal'])
        {
            $data['precio'] += $data['cantidad_pallet_normal'] * Price::where('type', 'PALLET')->value('price');
        }
         if($data['cantidad_pallet_grande'])
        {
            $data['precio'] += $data['cantidad_pallet_grande'] * (Price::where('type', 'PALLET')->value('price') * 1.5);
        }

        $carga->update($data);
        return redirect()->route('showcarga', $carga->id)
            ->with('success', 'Carga actualizada correctamente.');
    }
    public function editpriceoncarga(Request $request )
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $carga = Carga::findOrFail($request->id);
        $data = $request->validate(['precio' => 'required|decimal:0,2']);
        $carga->update($data);

        return redirect()->route('showcarga', $carga->id)
            ->with('success', 'Carga actualizada correctamente.');
    }
    public function editEstadoEnviostock(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $carga = Carga::findOrFail($request->id);

        // desmarco el actual anterior (si había)
        $carga->estadoEnvios()->update(['estado_actual' => false]);

        // busco un estado con ese enum; si existe actualizo horario, si no lo creo
        $carga->estadoEnvios()->updateOrCreate(
            ['estado' => $request->estado_de_envio],
            [
                'horario'       => $request->horario,
                'estado_actual' => true,
            ]
        );

        return redirect()->route('showcarga', $carga->id)
            ->with('success', 'Carga actualizada correctamente.');
    }
    public function updateClienteTercero(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string',
            'numero_cliente'  => 'nullable|string',
            'cuit'            => 'nullable|string',
            'condicion_venta' => 'nullable|string',
            'codigo_postal'   => 'nullable|string',
            'direccion'       => 'nullable|string',
            'horario_entrega' => 'nullable|string',
        ]);

        $tercero = ClienteTercero::findOrFail($request->id);
        $tercero->update($request->except(['_token', 'id']));

        return redirect()->route('stock')->with('success', 'Cliente 3ro actualizado.');
    }
    public function delete(Request $request,$id)
    {
        $carga = Carga::findOrFail($id);

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $carga->delete();

        return redirect()->route('stock')->with('success', 'Carga eliminada correctamente.');
    }
    public function updatePrice(Request $request)
    {
         if (!auth()->user()->isAdmin() ) {
            abort(403);
        }
        $data = $request->validate([
            'type'   => 'required|in:PALLET,BULTO',
            'price' => 'required|numeric',
        ]);

        Price::where('type', $data['type'])->update(['price' => $data['price']]);

        return redirect()->route('stock')->with('success', 'Precio actualizado correctamente.');
    }
    public function storeRemito(Request $request)
    {
         if (!auth()->user()->isAdmin() ) {
            abort(403);
        }
        $data = $request->validate([
            'numero' => 'required|string|max:50',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'carga_id' => 'required|exists:cargas,id',
            'valor_declarado' => 'nullable|string'
        ]);
        if($request->hasFile('image'))
        {
            $data['path'] = $request->file('image')->store('remitos', 'public');
        }
        $c = Carga::find($data["carga_id"]);
        $data['client_id'] = $c->client->id;
        $r = Remito::create($data);
        $c->remito_id = $r->id;
        $c->save();
        return redirect()->back()->with('success', 'Remito cargado correctamente.');
    }
    public function updateRemito(Request $request)
    {
        $request->validate([
            'id'              => 'required|exists:remitos,id',
            'numero'          => 'required|string',
            'valor_declarado' => 'nullable|numeric',
            'image'           => 'nullable|image|max:4096',
        ]);

        $remito = Remito::findOrFail($request->id);

        $remito->numero          = $request->numero;
        $remito->valor_declarado = $request->valor_declarado;

        // Solo reemplaza la imagen si subieron una nueva
        if ($request->hasFile('image')) {
            // borra la anterior si existía
            if ($remito->path && Storage::disk('public')->exists($remito->path)) {
                Storage::disk('public')->delete($remito->path);
            }
            $remito->path = $request->file('image')->store('remitos', 'public');
        }

        $remito->save();

        return back()->with('success', 'Remito actualizado.');
    }
    public function storeClientTercero(Request $request)
    {
         if (!auth()->user()->isAdmin() ) {
            abort(403);
        }
        $data = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'nombre'          => 'required|string',
            'numero_cliente'  => 'nullable|string',
            'cuit'            => 'nullable|string',
            'condicion_venta' => 'nullable|string',
            'codigo_postal'   => 'nullable|string',
            'direccion'       => 'nullable|string',
            'horario_entrega' => 'nullable|string',
        ]);

        ClienteTercero::create($data);

        return redirect()->back()->with('success', 'Cliente tercero creado correctamente.');
    }
    public function crearContactoTercero(Request $request, $terceroId)
    {
         if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $tercero = ClienteTercero::findOrFail($terceroId);
        if ($tercero->contactos()->count() >= 10) {
            return redirect()->back()->withErrors(['contacto' => 'Este cliente ya alcanzó el máximo de 10 contactos.']);
        }
       
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'lastname'    => 'nullable|string|max:255',
            'category'    => 'nullable|string|max:255',
            'mail'        => 'nullable|email|max:255',
            'telefono'    => 'nullable|string|max:50',
            'comentarios' => 'nullable|string|max:500',
        ]);

        Contacto::create([
            'nombre'     => $data['name'],
            'apellido'   => $data['lastname'] ?? null,
            'categoria'  => $data['category'] !== '-' ? $data['category'] : null,
            'mail'       => $data['mail'] ?? null,
            'telefono'   => $data['telefono'] ?? null,
            'comentario' => $data['comentarios'] ?? null,
            'cliente_tercero_id' => $tercero->id
        ]);

        return redirect()->back()->with('success', 'Contacto creado correctamente.');
    }
    public function editarContactoTercero(Request $request, $contactoId, $terceroId)
    {
         if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $contacto = Contacto::findOrFail($contactoId);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'lastname'    => 'nullable|string|max:255',
            'category'    => 'nullable|string|max:255',
            'mail'        => 'nullable|email|max:255',
            'telefono'    => 'nullable|string|max:50',
            'comentarios' => 'nullable|string|max:500',
        ]);

        $contacto->update([
            'nombre'     => $data['name'],
            'apellido'   => $data['lastname'] ?? null,
            'categoria'  => $data['category'] !== '-' ? $data['category'] : null,
            'mail'       => $data['mail'] ?? null,
            'telefono'   => $data['telefono'] ?? null,
            'comentario' => $data['comentarios'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Contacto actualizado correctamente.');
    }
    public function eliminarContactoTercero(Request $request, $contactoId, $terceroId)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $contacto = Contacto::findOrFail($contactoId);

        $contacto->delete();

        return redirect()->back()->with('success', 'Contacto eliminado correctamente.');
    }
    public function remitoPdf(Remito $remito)
    {
        $path = Storage::disk('public')->path($remito->path);

        $pdf = Pdf::loadView('stock.admin.remitopdf', [
            'remito' => $remito,
            'imagen' => $path,   
        ]);

        return $pdf->download('remito-' . $remito->numero . '.pdf');
    }
    public function deleteClienteTercero(Request $request)
    {
        if (auth()->user()->isAdmin()) {
        $clienteTercero = ClienteTercero::findOrFail($request->cliente_tercero_id);
        $clienteTercero->delete();
            $cargas = Carga::when($request->client_id, function ($query, $clientId) {
                $query->where('client_id', $clientId);
            })->get();

            return view('stock.admin.index', [
                'cargas'            => $cargas,
                'clients'           => Client::all(),
                'clientes_terceros' => ClienteTercero::all(),
                'prices'            => Price::all()
            ])->with('success', 'Eliminado');;
        }
    }
    public function corteDeOperaciones(Request $request)
    {
        // if (!auth()->user()->isAdmin() ) {
        //     abort(403);
        // }

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'fecha'     => 'required|date',
        ]);

        $cargas = Carga::where('client_id', $data['client_id'])
        ->where('estado_de_envio', ['ENTREGADO', 'RECHAZADO'])// busco estados de entregado y rechazado anteriores a la fecha ingresada
        ->where('travel_certificate_id', null)
        ->get();   
        $cargasagrupadas = $cargas->groupBy('driver_id');
        $preconstancias=[];
        foreach($cargasagrupadas as $driver_id => $grupo)
        {
            foreach($grupo as $e)
            {
                $fecha = $e->fecha_de_recepcion?->format('Y-m-d') ?? 'sin_fecha';
                $preconstancias[$driver_id][$fecha][] = $e;
                //remito
                //bultos
                //bultos_monto
                //pallet_normal
                //pallet_normal_monto
                //pallet_grande
                //pallet_grande_monto
                //total
            }
        }
                
        dd($preconstancias);
        return view('stock.admin.operations', ['cargas'=>$preconstancias]);
    }
    public function generartc(Request $request,$id_carga)
    {
        $carga = Carga::findOrFail($id_carga);

        $date = $carga->fecha_de_recepcion;// QUE FECHA? NOW|RECEPCION|ENTREGA|PERSONALIZADA
        $destiny = $carga->destino;
        $clientId = $carga->client_id;
        $driverId = $carga->driver_id;
        $TC = TravelCertificate::where([
                ['date', '=', $date],
                ['destiny', '=', $destiny],
                ['driverId', '=', $driverId],
                ['clientId', '=', $clientId],
        ])->first();
        if($TC)
        {
            return redirect()->route('stock')->with('warning', 'Ya se registró esta constancia de viaje. El id de la constancia es: ' . $TC->id);
            return redirect()->back()->with('warning', 'Ya se registró esta constancia de viaje. El id de la constancia es: ' . $TC->id);
        }
        if(!$carga->driver)
        {
            return redirect()->route('stock')->with('warning', 'Ingresachofer');
            // return redirect()->back()->with('warning', 'Debe asignar un chofer antes');
        }
        // Crear una nueva instancia de TravelCertificate
        $newTravelCertificate = new TravelCertificate;
        $newTravelCertificate->date = $date;
        $newTravelCertificate->destiny = $destiny;
        $newTravelCertificate->clientId = $clientId;
        $newTravelCertificate->driverId = $driverId;
        $newTravelCertificate->commission_type =  'porcentaje pactado';
        $newTravelCertificate->percent         =  $carga->driver->percent;
        
        $newTravelCertificate->total = 0.00; //$carga->price;
        $newTravelCertificate->iva = 0.00; //$carga->price * 0.21;
        // ahora hay que asignarle los items de la carga.

        $newTravelCertificate->save();
        //asignarle a la carga la tc.
        return redirect()->route('stock')->with('success', 'Constancia generada correctamente. ID: '.$newTravelCertificate->id);
        return redirect()->back()->with('success', 'Operación realizada correctamente.');
    }
}