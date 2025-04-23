<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Receipt;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\TravelItem;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function invoices()
    {
        $invoices = Invoice::all();
        $clients = Client::orderBy('name', 'asc')->get();
        return view('invoice.index', ['clients' => $clients, 'invoices' => $invoices]);
    }

    public function generate(StoreInvoiceRequest $request)
    {
        $newInvoice = new Invoice;
        $newInvoice->number = $request->number;
        $newInvoice->date = $request->date;
        $newInvoice->pointOfSale = $request->pointOfSale ?? 3;
        $newInvoice->total = 0;
        $newInvoice->iva = 0;
        $newInvoice->totalWithIva = 0;
        $newInvoice->balance = 0;
        $newInvoice->clientId = $request->clientId;
        $newInvoice->receiptId = 0;
        $newInvoice->save();
        return redirect(route('showInvoice', $newInvoice->id));
    }

    public function show($id)
    {
        $data['invoice'] = Invoice::find($id);

        foreach ($data['invoice']->travelCertificates as $travelCertificate) {
            // Agregar el total de peajes a cada travelCertificate
            $travelCertificate->peajes = TravelItem::where('type', 'PEAJE')
                ->where('travelCertificateId', $travelCertificate->id)
                ->sum('price');
            $travelCertificate->importeNeto = $travelCertificate->total - $travelCertificate->peajes;
            $travelCertificate->iva = $travelCertificate->importeNeto * 0.21;
        }

        return view('invoice.show', $data);
    }

    public function generateInvoicePdf($id)
    {

        $data['invoice'] = Invoice::find($id);

        $data['invoice']->travelCertificates = $data['invoice']->travelCertificates
            ->sortBy([
                ['date', 'asc'],   // Ordenar por fecha (ascendente)
                ['number', 'asc']  // Ordenar por número (ascendente)
            ]);

        $data['totalTolls'] = 0;
        $data['totalImporteNeto'] = 0;

        // Calculamos el total de agency y sumamos los peajes
        foreach ($data['invoice']->travelCertificates as $travelCertificate) {
            // Agregar el total de peajes a cada travelCertificate
            $travelCertificate->totalTolls = TravelItem::where('type', 'PEAJE')
                ->where('travelCertificateId', $travelCertificate->id)
                ->sum('price');

            $data['totalImporteNeto'] += $travelCertificate->total - $travelCertificate->totalTolls;

            $data['totalTolls'] += $travelCertificate->totalTolls;
        }

        $pdf = Pdf::loadView('invoice.pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        // Definir márgenes personalizados
        $options = $pdf->getDomPDF()->getOptions();
        $options->set('defaultPaperSize', 'a4');
        $options->set('defaultPaperOrientation', 'portrait');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        $pdf->getDomPDF()->setOptions($options);


        return $pdf->stream('Factura-N°-' . $data['invoice']->number . 'pdf');
    }

    public function invoiced($id)
    {
        $invoice = Invoice::find($id);
        $invoice->invoiced = 'SI';
        $invoice->totalWithIva = ($invoice->total + $invoice->iva);
        $invoice->balance = $invoice->totalWithIva;
        $invoice->save();
        $client = Client::find($invoice->client->id);
        $client->balance += $invoice->totalWithIva;
        $client->save();
        return redirect(route('showInvoice', $invoice->id));
    }

    public function cancel($id)
    {
        $invoice = Invoice::find($id);
        $invoice->invoiced = 'NO';
        $invoice->balance = 0;
        $client = Client::find($invoice->client->id);
        $client->balance -= $invoice->totalWithIva;
        $invoice->totalWithIva = 0;
        $client->save();
        $invoice->save();
        return redirect(route('showInvoice', $invoice->id));
    }

    public function addToReceipt(UpdateInvoiceRequest $request, $id)
    {
        $balanceToPay = $request->balanceToPay;
        $taxAmount = $request->taxAmount;
        $invoice = Invoice::find($id);
        if ($balanceToPay == $invoice->balance) {
            $invoice->paid = 'SI';
        }
        $receiptId = $request->receiptId;
        $receipt = Receipt::find($receiptId);
        $invoice->receipts()->attach($receiptId, ['paymentMethodId' => $request->paymentMethodId, 'taxId' => $request->taxId, 'total' => $balanceToPay, 'taxAmount' => $taxAmount]);
        $invoice->balance -= $balanceToPay;
        $receipt->total += $balanceToPay;
        $receipt->taxTotal += $taxAmount;
        $invoice->save();
        $receipt->save();
        return redirect(route('showReceipt', $receiptId));
    }

    public function removeFromReceipt(UpdateInvoiceRequest $request, $id)
    {
        $invoice = Invoice::find($id);
        $receiptId = $request->receiptId;
        $receipt = Receipt::find($receiptId);
        $pivot = DB::table('invoice_receipt')
            ->where('invoice_id', $id)
            ->where('receipt_id', $receiptId)
            ->first();

        if ($pivot) {
            $taxAmount = $pivot->taxAmount;
            $total = $pivot->total;
            $invoice->receipts()->detach($receiptId);
            $invoice->balance += $total;
            $receipt->total -= $total;
            $receipt->taxTotal -= $taxAmount;
            if ($invoice->balance > 0) {
                $invoice->paid = 'NO';
            } else {
                $invoice->paid = 'SI';
            }
            $invoice->save();
            $receipt->save();
        } else {
            return redirect()->back()->withErrors('Registro de pivote no encontrado.');
        }
        return redirect(route('showReceipt', $receiptId));
    }
}
