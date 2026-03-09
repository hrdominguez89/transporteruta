<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payments;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payments::all();
        $clients = Client::all();
        return view('payments.index',[
            'payments' => $payments , 
            'clients' => $clients]);
    }
    public function generate(Request $request)
    {
        if($request->filled('clientId')) $client = $request->clientId;
        if($request->filled('metodo'))  $metodo = $request->metodo;
        if($request->filled('tipodecheque'))  $tipodecheque = $request->tipodecheque;
        if($request->filled('fecharecepcion'))  $fechaderecepcion = $request->fecharecepcion;
        if($request->filled('banco'))  $banco = $request->banco;
        if($request->filled('monto'))  $monto = $request->monto;
        if($request->filled('comentario'))  $comentario = $request->comentario;
        $comentario ="";
        $pago = new Payments();
        $pago->clientId = $client;
        $pago->method = $metodo;
        if( $metodo == 'CHEQUE')
        {
            $pago->cheq_type = $tipodecheque;
        }
        if( $metodo == 'TRANSFERENCIA' )
        {
            $pago->acreditation_date = $fechaderecepcion;
            $pago->banco = $banco;
        }
        $pago->note = $comentario;
        $pago->total = $monto;
        $pago->balance = $monto;

        $pago->save();
        return view('payments.show',[ "pago" => $pago ]);
    }
    public function show(Request $request,$id)
    {
        $pago = Payments::find($id);
        return view('payments.show',[ "pago" => $pago ]);

    }
    public function edit(Request $request,$id)
    {
         $pago = Payments::find($id);
        return view('payments.show',[ "pago" => $pago ]);
    }
    public function delete(Request $request)
    {

    }
    public function pdf(Request $request)
    {
        
    }
}