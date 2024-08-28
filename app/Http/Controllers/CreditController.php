<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Client;
use App\Models\Invoice;
use App\Http\Requests\StoreCreditRequest;
use App\Http\Requests\UpdateCreditRequest;

class CreditController extends Controller
{
    public function credits()
    {
        $credits = Credit::all();
        $clients = Client::all();
        return view('credit.index', ['credits'=>$credits, 'clients'=>$clients]);
    }

    public function generate(StoreCreditRequest $request)
    {
        $newCredit = new Credit;
        $newCredit->number = $request->number;
        $newCredit->date = $request->date;
        $newCredit->total = 0;
        $newCredit->clientId = $request->clientId;
        $newCredit->invoiceId = 0;
        $newCredit->save();
        return redirect(route('showCredit', $newCredit->id));
    }

    public function show($id)
    {
        $credit = Credit::find($id);
        return view('credit.show', ['credit'=>$credit]);
    }

    public function addInvoice(UpdateCreditRequest $request, $id)
    {
        $credit = Credit::find($id);
        $invoice = Invoice::find($request->invoiceId);
        $creditTotal = $request->total;
        $credit->invoiceId = $invoice->id;
        $invoice->balance -= $creditTotal;
        $credit->total = $creditTotal;
        if($invoice->invoiced == 'SI')
        {
            $client = Client::find($invoice->client->id);
            $client->balance -= $creditTotal;
            $client->save();
        }
        $credit->save();
        $invoice->save();
        return redirect(route('showCredit', $credit->id));
    }

    public function removeInvoice($id)
    {
        $credit = Credit::find($id);
        $invoice = Invoice::find($credit->invoiceId);
        $invoice->balance += $credit->total;
        $credit->invoiceId = 0;
        $credit->total = 0;
        $credit->save();
        $invoice->save();
        return redirect(route('showCredit', $credit->id));
    }
}
