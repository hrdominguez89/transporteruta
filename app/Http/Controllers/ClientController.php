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

        // NUEVO: guardar días de vencimiento (null o número)
        $newClient->paymentTermDays = $request->input('paymentTermDays');

        $newClient->save();
        return redirect(route('showClient', $newClient->id));
    }

    public function show($id)
    {
        $client = Client::find($id);
        return view('client.show', ['client' => $client]);
    }

    /**
     * Reporte de deudores
     * Versión LEGACY restaurada (usa balance y totalWithIva)
     * Dejamos abajo, comentada, la versión nueva "al vuelo" para reactivarla cuando queramos.
     */
    public function generateDebtorsPdf()
    {
        // LEGACY (RESTABLECIDO) 
        // Trae clientes con balance > 0 y calcula saldos con totalWithIva.
        $clients = Client::where('balance', '>', 0.0)
            ->orderBy('balance', 'desc')
            ->get();

        $saldos = [];
        foreach ($clients as $client) {
            $saldos[$client->id] = 0;
            foreach ($client->invoices as $invoice) {
                if ($invoice->paid == 'NO') {
                    // Usamos el campo persistido totalWithIva como antes.
                    $saldos[$client->id] += (float)($invoice->totalWithIva ?? 0);
                }
            }
        }

        // Total general como se calculaba originalmente (por balance)
        $total = Client::all()->sum('balance');
        $date = now();
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

        // NUEVO: actualizar días de vencimiento (null o número)
        $client->paymentTermDays = $request->input('paymentTermDays');

        $client->save();
        return redirect(route('showClient', $client->id));
    }
}

