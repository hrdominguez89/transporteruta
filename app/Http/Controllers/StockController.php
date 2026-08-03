<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Carga;
use App\Models\Client;
use App\Models\ClienteTercero;
use App\Models\Contacto;
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
            ]);
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
        $travelCertificates = TravelCertificate::where('clientId', $carga->client_id)
        ->where('invoiced', 'NO')
        ->get();

        if (auth()->user()->isAdmin()) {
            $clientes_tercero =  ClienteTercero::where('client_id', $carga->client_id)->get();
            return view('stock.admin.show', ['carga' => $carga,'clientes_terceros' => $clientes_tercero,'travel_certificates' => $travelCertificates ]);
        }
        return view('stock.client.show', [
            'carga' => $carga,
            'client' => auth()->user()->client
            ]);
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
            'estado_de_envio'           => 'sometimes',
            'notificacion_de_recepcion' => 'sometimes|boolean',
            'notificacion_de_entrega'   => 'sometimes|boolean',
            'cliente_tercero_id'        => 'nullable|integer',
            'motivo'                => 'nullable|string|max:255',
            'liquidado'             => 'nullable|boolean',
            'travel_certificate_id' => 'nullable|exists:travel_certificates,id',
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
            'cantidad'                  => 'sometimes|integer',
            'tipo'                      => 'sometimes|in:PALLET,BULTO',
            'espacio'                   => 'nullable|string|max:255',
            ]);
            
            $price = Price::where('type',$data['tipo'])->value('price');
            
        if($data['tipo'] == 'PALLET' & $data['espacio'] == 'EXTRA')
        {
            $price = $price * 1.5;
        }

        $data['precio'] = $price * $data['cantidad'];
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
    public function generate(Request $request)
    {
        if (!auth()->user()->isAdmin() ) {  
            abort(403);
        }
        $data = $request->validate([
            'nombre'             => 'required|string|max:255',
            'cantidad'           => 'required|integer',
            'fecha_de_recepcion' => 'required|date',
            'fecha_de_entrega'   => 'nullable|date',
            'espacio'            => 'nullable|string|max:255',
            'tipo'               => 'required|in:PALLET,BULTO',
            'destino'            => 'nullable|string|max:255',
            'client_id'          => auth()->user()->isAdmin() ? 'required|exists:clients,id' : 'nullable',
            'cliente_tercero_id' => ['nullable'],
        ]);

        $data['client_id'] = auth()->user()->isAdmin()
            ? $data['client_id']
            : auth()->user()->client_id;

        $price = Price::where('type', $data['tipo'])->value('price');

        if ($data['tipo'] === 'PALLET' && ($data['espacio'] ?? null) === 'EXTRA') {
            $price = $price * 1.5;
        }

        $data['precio'] = $price * $data['cantidad'];

        Carga::create($data);

        return redirect()->route('stock')->with('success', 'Carga generada correctamente.');
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
            'numero' => 'required|string|max:50|unique:remitos,numero',
            'image'  => 'required|image|mimes:jpg,jpeg,png|max:4096',
            'carga_id' => 'required|exists:cargas,id',
            'valor_declarado' => 'nullable|string'
        ]);

        $data['path'] = $request->file('image')->store('remitos', 'public');
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
    public function corteDeOperaciones(Request $request)
    {
         if (!auth()->user()->isAdmin() ) {
            abort(403);
        }
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'fecha'     => 'required|date',
        ]);

       $cargas = Carga::where('client_id', $data['client_id'])
        ->whereIn('estado_de_envio', ['ENTREGADO', 'RECHAZADO'])
        ->whereDate('fecha_de_entrega', $data['fecha'])
        ->get();
        $bultos  = $cargas->where('tipo', 'BULTO');
        $pallets = $cargas->where('tipo', 'PALLET');
        $remitos = $cargas->pluck('remito')->filter();
        $corte = [
            'cliente'          => Client::find($data['client_id'])->name,
            'fecha'            => $data['fecha'],
            'bultos_cantidad'  => $bultos->sum('cantidad'),
            'bultos_total'     => $bultos->sum('precio'),
            'pallets_cantidad' => $pallets->sum('cantidad'),
            'pallets_total'    => $pallets->sum('precio'),
            'total'            => $cargas->sum('precio'),
            'remitos'          => $remitos
        ];

        $clientes = Client::all();
        $cargasIndex = Carga::all();


        return view('stock.admin.index', [
            'cargas'  => $cargasIndex,
            'clients' => $clientes,
            'corte'   => $corte,
            'clientes_terceros' => ClienteTercero::all()
        ]);
    }
    public function storeClientTercero(Request $request)
    {
         if (!auth()->user()->isAdmin() ) {
            abort(403);
        }
        $data = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'nombre'        => 'required|string|max:255',
            'codigo_postal' => 'nullable|string|max:20',
            'direccion'     => 'nullable|string|max:255',
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

       
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'lastname'    => 'nullable|string|max:255',
            'category'    => 'nullable|string|max:255',
            'mail'        => 'nullable|email|max:255',
            'telefono'    => 'nullable|string|max:50',
            'comentarios' => 'nullable|string|max:500',
        ]);

        $contacto = Contacto::create([
            'nombre'     => $data['name'],
            'apellido'   => $data['lastname'] ?? null,
            'categoria'  => $data['category'] !== '-' ? $data['category'] : null,
            'mail'       => $data['mail'] ?? null,
            'telefono'   => $data['telefono'] ?? null,
            'comentario' => $data['comentarios'] ?? null,
        ]);

        $tercero->contacto_id = $contacto->id;
        $tercero->save();

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
        $tercero  = ClienteTercero::findOrFail($terceroId);

        // Desvincular del tercero antes de borrar, para no dejar contacto_id colgado
        if ($tercero->contacto_id == $contacto->id) {
            $tercero->contacto_id = null;
            $tercero->save();
        }

        $contacto->delete();

        return redirect()->back()->with('success', 'Contacto eliminado correctamente.');
    }
    public function remitoPdf(Remito $remito)
    {
        $path = Storage::disk('public')->path($remito->path);

        $pdf = Pdf::loadView('stock.admin.remitopdf', [
            'remito' => $remito,
            'imagen' => $path,   // ruta absoluta para embeber en el PDF
        ]);

        return $pdf->download('remito-' . $remito->numero . '.pdf');
    }
}
