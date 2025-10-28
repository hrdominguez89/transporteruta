<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class ClientController extends Controller

{
    public function clients()
    {
        $clients = Client::all();
        return view('client.index', ['clients' => $clients]);
    }


    public function store(StoreClientRequest $request)
    {
        $newClient = new Client;
        $newClient->name = $request->name;
        $newClient->dni = $request->dni;
        $newClient->address = $request->address;
        $newClient->city = $request->city;
        $newClient->phone = $request->phone;
        $newClient->ivaType = $request->ivaType;
        $newClient->balance = 0;
        $newClient->observations = $request->observations;
        $newClient->save();
        return redirect(route('showClient', $newClient->id));
    }

    public function show($id)
    {
        $client = Client::find($id);
        return view('client.show', ['client' => $client]);
    }

    public function generateDebtorsPdf()
    {
        $clients = Client::where('balance', '>', 0.0)
            ->orderBy('balance', 'desc')
            ->get();
        $saldos = []; 
        foreach ($clients as $client) {
            $saldos[$client->id] = 0;
            foreach($client->invoices as $invoice) 
            {
                if($invoice->paid=='NO')
                {
                    $saldos[$client->id] += $invoice->totalWithIva;
                }
            }
        }
        $total = Client::all()->sum('balance');
        $date = now();
        $clients = $clients->sortBy('name');
        $pdf = Pdf::loadView('client.report', ['clients' => $clients, 'total' => $total, 'date' => $date,'saldos' => $saldos]);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('Reporte-cuenta-corriente-general.pdf');
    }

    public function update(UpdateClientRequest $request, $id)
    {
        $client = Client::find($id);
        $client->name = $request->name;
        $client->dni = $request->dni;
        $client->address = $request->address;
        $client->city = $request->city;
        $client->phone = $request->phone;
        $client->ivaType = $request->ivaType;
        $client->observations = $request->observations;
        $client->save();
        return redirect(route('showClient', $client->id));
    }
}
