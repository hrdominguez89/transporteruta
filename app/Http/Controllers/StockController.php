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
use App\Models\TravelItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StockController extends Controller
{
    //tabla inicial
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
                'prices'            => Price::all(),
                'drivers'           => Driver::all() 
            ]);
        }

        $cargas = Carga::where('client_id', auth()->user()->client_id)->get();

        return view('stock.client.index', [
            'cargas' => $cargas,
            'client' => auth()->user()->client
        ]);
    }
    //vista individual
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
                'drivers' => Driver::all(),
                'precio_bulto' =>Price::where('type','BULTO')->value('price'),
                'precio_pallet' =>Price::where('type','PALLET')->value('price')
                ]);
        }
        return view('stock.client.show', [
            'carga' => $carga,
            'client' => auth()->user()->client,
            'estado_envio' => $estado_envio
            ]);
    }
    //store o generate
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
            'driver_id' =>'nullable'
        ]);

        $data['client_id'] = auth()->user()->isAdmin()
            ? $data['client_id']
            : auth()->user()->client_id;

        $data['bulto_costo']  = Price::where('type', 'BULTO')->value('price');
        $data['pallet_costo'] = Price::where('type', 'PALLET')->value('price');

        $data['precio'] = 0;
        if ($data['cantidad_bulto']) {
            $data['precio'] += $data['cantidad_bulto'] * $data['bulto_costo'];
        }
        if ($data['cantidad_pallet_normal']) {
            $data['precio'] += $data['cantidad_pallet_normal'] * $data['pallet_costo'];
        }
        if ($data['cantidad_pallet_grande']) {
            $data['precio'] += $data['cantidad_pallet_grande'] * ($data['pallet_costo'] * 1.5);
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
            'cantidad_bulto'         => 'sometimes|integer',
            'cantidad_pallet_normal' => 'sometimes|integer',
            'cantidad_pallet_grande' => 'sometimes|integer',
        ]);

        $data['precio'] = 0;
        if ($data['cantidad_bulto']) {
            $data['precio'] += $data['cantidad_bulto'] * $carga->bulto_costo;
        }
        if ($data['cantidad_pallet_normal']) {
            $data['precio'] += $data['cantidad_pallet_normal'] * $carga->pallet_costo;
        }
        if ($data['cantidad_pallet_grande']) {
            $data['precio'] += $data['cantidad_pallet_grande'] * ($carga->pallet_costo * 1.5);
        }
        if ($carga->estadoActual?->estado === 'RECHAZADO') {
            $data['precio'] = $data['precio'] * 1.3;
        }

        $carga->update($data);
        return redirect()->route('showcarga', $carga->id)
            ->with('success', 'Carga actualizada correctamente.');
    }
    public function editpriceoncarga(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $carga = Carga::findOrFail($request->id);

        $carga->bulto_costo  = $request->precio_bulto;
        $carga->pallet_costo = $request->precio_pallet;

        $precio  =  $carga->cantidad_bulto         * $request->precio_bulto;
        $precio  += $carga->cantidad_pallet_normal * $request->precio_pallet;
        $precio  += $carga->cantidad_pallet_grande * ($request->precio_pallet * 1.5);

        if ($carga->estadoActual?->estado === 'RECHAZADO') {
            $precio = $precio * 1.3;
        }

        $carga->precio = $precio;
        $carga->save();

        return redirect()->route('showcarga', $carga->id)
            ->with('success', 'Carga actualizada correctamente.');
    }
    public function editEstadoEnviostock(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'id'              => 'required|exists:cargas,id',
            'horario'         => 'required|date',
            'estado_de_envio' => 'required|in:DEPOSITO,AVISADO,COORDINADO,TRANSITO,ENTREGADO,RECHAZADO',
            'motivo'          => 'nullable|string|max:255',
        ]);

        $carga = Carga::findOrFail($data['id']);

        if ($data['estado_de_envio'] === 'RECHAZADO') {
            $carga->update([
                'motivo' => $data['motivo'],
                'precio' => $carga->precio * 1.30,
            ]);
        }

        $carga->estadoEnvios()->update(['estado_actual' => false]);

        $carga->estadoEnvios()->updateOrCreate(
            ['estado' => $data['estado_de_envio']],
            [
                'horario'       => $data['horario'],
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
        if (!auth()->user()->isAdmin() ) {
            abort(403);
        }
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
        if (!auth()->user()->isAdmin() ) {
            abort(403);
        }

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'fecha'     => 'required|date',
        ]);

        $cargas = Carga::with('estadoActual')
        ->where('client_id', $data['client_id'])
        ->whereNull('travel_certificate_id')
        ->whereDate('fecha_de_recepcion', '<=', $data['fecha'])
        ->whereHas('estadoActual', function ($query) {
            $query->whereIn('estado', ['ENTREGADO', 'RECHAZADO']);
        })
        ->get();
        $cargasagrupadas = $cargas->groupBy(['driver_id', 'fecha_de_recepcion']);
        
        $cargaspararemito = Carga::with('remito:id,numero,valor_declarado')
        ->where('client_id', $data['client_id'])
        ->whereNull('travel_certificate_id')
        ->get();

        $remnovinculados= [];
        foreach($cargaspararemito as $c)
        {
            if ($c->remito) {
                $remnovinculados[] = $c->remito;
            }
        }
        $porcentajes=0;
        return view('stock.admin.operations', 
        [
            'cargasagrupadas'=>$cargasagrupadas,
            'data'=>$data,
            'remitos' => $remnovinculados
        ]);
    }
    public function generartc(Request $request)
    {
        $cargas = Carga::with('estadoActual')
        ->where('client_id', $request->client_id)
        ->where('driver_id', $request->driver_id)
        ->whereNull('travel_certificate_id')
        ->whereDate('fecha_de_recepcion','=', $request->fecha)
        ->whereHas('estadoActual', function ($query) {
            $query->whereIn('estado', ['ENTREGADO', 'RECHAZADO']);
        })
        ->get();
        // dd('cliente :'. $request->client_id, 'driver'.$request->driver_id);
        if ($cargas->isEmpty()) {
            return redirect()->route('stock')->with('error', 'No hay cargas para generar la constancia.');
        }

        $d = Driver::find($request->driver_id);
        
        $newTravelCertificate = new TravelCertificate;
        $newTravelCertificate->date = $cargas[0]->fecha_de_entrega;
        $newTravelCertificate->destiny = "";// $cargas[0]->destino;
        $newTravelCertificate->clientId = $request->client_id;
        $newTravelCertificate->driverId = $d->id;
        $newTravelCertificate->commission_type =  'porcentaje pactado';
        $newTravelCertificate->percent         =  $d->percent;
        
        $newTravelCertificate->total = 0;
        $newTravelCertificate->iva = 0;
        $newTravelCertificate->save();

 
        foreach($cargas as $carga)
        {
            if($carga->cantidad_bulto)
            {
                $descripcion = 'Bultos :'.$carga->cantidad_bulto.' cliente :' .$carga->cliente_tercero->nombre;
                $item = new TravelItem();
                $item->travelCertificateId = $newTravelCertificate->id;
                $item->type        = 'BULTO';
                $item->description = $descripcion;
                $item->price       = $carga->cantidad_bulto *  $carga->bulto_costo;
                $item->distance    = $carga->cantidad_bulto;
                $item->save();
            }
            if($carga->cantidad_pallet_normal)
            {
                $descripcion = 'Pallet estandar :' . $carga->cantidad_pallet_normal .' cliente :' . $carga->cliente_tercero->nombre;
                $itemb = new TravelItem();
                $itemb->travelCertificateId = $newTravelCertificate->id;
                $itemb->type        = 'PALLET';
                $itemb->description = $descripcion;
                $itemb->price       = $carga->cantidad_pallet_normal * $carga->pallet_costo;
                $itemb->distance    = $carga->cantidad_pallet_normal;
                $itemb->save();
            }
            if($carga->cantidad_pallet_grande)
            {
                $descripcion = 'Pallet grandes :' . $carga->cantidad_pallet_grande .' cliente :' . $carga->cliente_tercero->nombre;
                $itemc = new TravelItem();
                $itemc->travelCertificateId = $newTravelCertificate->id;
                $itemc->type        = 'PALLET';
                $itemc->description = $descripcion;
                $itemc->price       = $carga->cantidad_pallet_grande * (1.5 * $carga->pallet_costo);
                $itemc->distance    = $carga->cantidad_pallet_grande;
                $itemc->save();
            }
            $itemd = new TravelItem();
            $itemd->travelCertificateId = $newTravelCertificate->id;
            $itemd->type          = 'REMITO';
            $itemd->description   = 'Remito N° ' . $carga->remito?->numero;
            $itemd->remito_number = $carga->remito?->numero;
            $itemd->price         = 0;
            $itemd->percent       = 0;
            $itemd->save();

            $carga->travel_certificate_id = $newTravelCertificate->id;
            $carga->liquidado = true;
            $carga->save();

            $newTravelCertificate->destiny .= $carga->destino;
        }

        $newTravelCertificate->recalcTotals();
        $newTravelCertificate->save();
        
        return redirect()->route('stock')->with('success', 'Constancia generada correctamente. ID: '.$newTravelCertificate->id);
    }
    public function generartodastc(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'fecha'     => 'required|date',
        ]);

        $cargas = Carga::with('estadoActual')
        ->where('client_id', $data['client_id'])
        ->whereNull('travel_certificate_id')
        ->whereNotNull('driver_id')
        ->whereDate('fecha_de_recepcion', '<=', $data['fecha'])
        ->whereHas('estadoActual', function ($query) {
            $query->whereIn('estado', ['ENTREGADO', 'RECHAZADO']);
        })
        ->get();

        if ($cargas->isEmpty()) {
            return redirect()->route('stock')->with('error', 'No hay cargas para generar constancias.');
        }

        $cargasagrupadas = $cargas->groupBy(['driver_id', 'fecha_de_entrega']);

        $generados = 0;

        foreach ($cargasagrupadas as $driverId => $fechas) {
            $driver = Driver::findOrFail($driverId);

            foreach ($fechas as $fecha => $cargasDelGrupo) {
                
                if ($fecha === '' || $fecha === null) {
                    continue;
                }

                $primera = $cargasDelGrupo->first();
                $newTravelCertificate = new TravelCertificate;
                $newTravelCertificate->date            = $primera?->fecha_de_entrega;
                $newTravelCertificate->destiny         = '';
                $newTravelCertificate->clientId        = $data['client_id'];
                $newTravelCertificate->driverId        = $driverId;
                $newTravelCertificate->commission_type = 'porcentaje pactado';
                $newTravelCertificate->percent         = $driver?->percent;
                $newTravelCertificate->total         = 0;
                $newTravelCertificate->iva         = 0;
                $newTravelCertificate->save();

                foreach ($cargasDelGrupo as $carga) {
                    if ($carga->cantidad_bulto) {
                        $descripcion = 'Bultos :'.$carga->cantidad_bulto.' cliente :' .$carga->cliente_tercero->nombre;
                        $item = new TravelItem();
                        $item->travelCertificateId = $newTravelCertificate->id;
                        $item->type        = 'BULTO';
                        $item->description = $descripcion;
                        $item->price  = $carga->cantidad_bulto * $carga->bulto_costo;
                        $item->distance    = $carga->cantidad_bulto;
                        $item->save();
                    }
                    if ($carga->cantidad_pallet_normal) {
                        $descripcion = 'Pallet estandar :' . $carga->cantidad_pallet_normal .' cliente :' . $carga->cliente_tercero->nombre;
                        $itemb = new TravelItem();
                        $itemb->travelCertificateId = $newTravelCertificate->id;
                        $itemb->type        = 'PALLET';
                        $itemb->description = $descripcion;
                        $itemb->price = $carga->cantidad_pallet_normal * $carga->pallet_costo;
                        $itemb->distance    = $carga->cantidad_pallet_normal;
                        $itemb->save();
                    }
                    if ($carga->cantidad_pallet_grande) {
                        $descripcion = 'Pallet grandes :' . $carga->cantidad_pallet_grande .' cliente :' . $carga->cliente_tercero->nombre;
                        $itemc = new TravelItem();
                        $itemc->travelCertificateId = $newTravelCertificate->id;
                        $itemc->type        = 'PALLET';
                        $itemc->description = $descripcion;
                        $itemc->price = $carga->cantidad_pallet_grande * (1.5 * $carga->pallet_costo);
                        $itemc->distance    = $carga->cantidad_pallet_grande;
                        $itemc->save();
                    }
                    $itemd = new TravelItem();
                    $itemd->travelCertificateId = $newTravelCertificate->id;
                    $itemd->type          = 'REMITO';
                    $itemd->description   = 'Remito N° ' . $carga->remito?->numero;
                    $itemd->remito_number = $carga->remito?->numero;
                    $itemd->price         = 0;
                    $itemd->percent       = 0;
                    $itemd->save();

                    $carga->travel_certificate_id = $newTravelCertificate->id;
                    $carga->liquidado = true;
                    $carga->save();

                    $newTravelCertificate->destiny .= $carga->destino;
                }

                $newTravelCertificate->recalcTotals();
                $newTravelCertificate->save();

                $generados++;
            }
        }

        return redirect()->route('stock')
            ->with('success', "Se generaron {$generados} constancias correctamente.");
    }
}