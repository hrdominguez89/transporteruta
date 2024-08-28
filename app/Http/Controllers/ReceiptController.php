<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Client;
use App\Models\PaymentMethod;
use App\Models\Tax;
use App\Http\Requests\StoreReceiptRequest;
use App\Http\Requests\UpdateReceiptRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    public function receipts()
    {
        $receipts = Receipt::all();
        $clients = Client::all();
        $paymentMethods = PaymentMethod::all();
        return view('receipt.index', ['receipts'=>$receipts, 'clients'=>$clients, 'paymentMethods'=>$paymentMethods]);
    }

    public function generate(StoreReceiptRequest $request)
    {
        $newReceipt = new Receipt;
        $newReceipt->number = $request->number;
        $newReceipt->date = $request->date;
        $newReceipt->total = 0;
        $newReceipt->taxTotal = 0;
        $newReceipt->clientId = $request->clientId;
        $newReceipt->save();
        return redirect(route('showReceipt', $newReceipt->id));
    }

    public function show($id)
    {
        $receipt = Receipt::find($id);
        $paymentMethods = PaymentMethod::all();
        $taxes = Tax::all();
        return view('receipt.show', ['receipt'=>$receipt, 'paymentMethods'=>$paymentMethods, 'taxes'=>$taxes]);
    }

    public function generateReceiptPdf($id)
    {
        $receipt = Receipt::find($id);
        $invoices = $receipt->invoices;
        $pdf = Pdf::loadView('receipt.pdf', ['receipt'=>$receipt, 'invoices'=>$invoices]);
        return $pdf->stream('Recibo-'.$receipt->client->name.'-('.$receipt->date.').pdf');
    }

    public function paid(UpdateReceiptRequest $request, $id)
    {
        $receipt = Receipt::find($id);
        $receipt->paid = 'SI';
        $receipt->save();
        $client = Client::find($receipt->client->id);
        $client->balance -= $receipt->total;
        $client->save();
        return redirect(route('showReceipt', $receipt->id));
    }

    public function cancel($id)
    {
        $receipt = Receipt::find($id);
        $receipt->paid = 'NO';
        $receipt->save();
        $client = Client::find($receipt->client->id);
        $client->balance += $receipt->total;
        $client->save();
        return redirect(route('showReceipt', $receipt->id));
    }
}
