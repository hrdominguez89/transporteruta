<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\Debit;
use App\Models\Invoice;

use function PHPUnit\Framework\isEmpty;

class DebitController extends Controller
{
    public function index(Request $request)
    {
        $debits = Debit::all();
        $clients = Client::all();
        return view('debit.index', ['debits'=>$debits, 'clients'=>$clients]);
    }
    public function show(Request $request,$id)
    {
        $debit = Debit::find($id);
        return view('debit.show',['debit'=>$debit]);
    }
    public function generate(Request $request)
    {
        $debit = new Debit();
        $balance =  $request->balance;
        $client = $request->client;
        $reason = $request->reason;
        $emission_date = $request->emissionDate;
        $referenceNumber =  $request->referenceNumber;
        $debit->balance = $balance;
        $debit->clientId = $client;
        $debit->reason = $reason;
        $debit->emission_date = $emission_date;
        $debit->referenceNumber = $referenceNumber;
        $debit->save();
        return view('debit.show',['debit'=>$debit]);
    }
    public function edit(Request $request,$id)
    {
        $debit = Debit::find($id);
        $debit->balance = $request->balance;
        $debit->reason = $request->reason;
        $debit->emissionDate = $request->emissionDate;
        //$debit->clientId = $request->client;
        //$debit->invoiceId = $request->invoice;
        //$debit->referenceNumber = $request->referenceNumber;
        $debit->save();
        return view('debit.show',['debit'=>$debit]);
    }
    public function delete(Request $request,$id)
    {
        $debit = Debit::find($id);
        if($debit?->invoiceId == null)
        {
            $debit->delete();
            return redirect()->route('debitos')->with('mensaje', 'Nota de debito eliminada correctamente.');
        }
        else
        {
            return redirect()->route('debitos')->with('mensaje', 'No es posible eliminar una nota de debito que esta asignada a una factura. ');
        }
    }
    public function generatePdf(Request $request)
    {
        return;
    }
    public function addToInvoice(Request $request,$id)
    {
        $debit = Debit::find($id);
        if($debit->invoiceId != null)
        {
            return view('debit.show',['debit'=>$debit]);            
        }
        $invoiceId = $request->invoiceId;
        $debit->invoiceId = $invoiceId;
        $debit->save();
        $debit->load('invoice');
        $debit->invoice->balance += $debit->balance;
        $debit->client->balance += $debit->balance;
        $debit->invoice->save();
        $debit->client->save();
        return view('debit.show',['debit'=>$debit]);        
    }
    public function remove(Request $request,$id)
    {
        $debit = Debit::find($id);
        $debit->load('invoice');
        $debit->invoice->balance -= $debit->balance;
        $debit->client->balance -= $debit->balance;
        $debit->invoice->save();
        $debit->client->save();
        $debit->invoiceId = null;
        $debit->save();
        return view('debit.show',['debit'=>$debit]);        
          
    }

}