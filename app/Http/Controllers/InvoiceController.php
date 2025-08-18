<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Receipt;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\InvoiceReceipt;
use App\Models\InvoiceReceiptTax;
use App\Models\TravelItem;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

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
        $data['clients'] = $data['invoice']->client;

        foreach ($data['invoice']->travelCertificates as $travelCertificate) {
            // Agregar el total de peajes a cada travelCertificate
            $travelCertificate->peajes = TravelItem::where('type', 'PEAJE')
                ->where('travelCertificateId', $travelCertificate->id)
                ->sum('price');
            $travelCertificate->importeNeto = $travelCertificate->total - $travelCertificate->peajes;
            $travelCertificate->iva = $travelCertificate->importeNeto * 0.21;
        }

        foreach ($data['clients']->travelCertificates as $travelCertificate) {
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
        // $taxAmount = $request->taxAmount;
        $invoice = Invoice::find($id);
        if ($balanceToPay == $invoice->balance) {
            $invoice->paid = 'SI';
        }
        $receiptId = $request->receiptId;
        $receipt = Receipt::find($receiptId);
        $invoice->receipts()->attach($receiptId, ['paymentMethodId' => $request->paymentMethodId, 'total' => $balanceToPay]);
        $invoice->balance -= $balanceToPay;
        $receipt->total += $balanceToPay;
        $invoice->save();
        $receipt->save();
        return redirect(route('showReceipt', $receiptId));
    }

    public function addTaxToReceiptInvoice(UpdateInvoiceRequest $request, $id)
    {
        $invoice_receipt_tax = new InvoiceReceiptTax();
        $invoice_receipt_tax->tax_id = $request->taxId;
        $invoice_receipt_tax->taxAmount = $request->taxAmount;
        $invoice_receipt_tax->created_at = Carbon::now();
        $invoice_receipt_tax->updated_at = Carbon::now();
        $invoice_receipt_tax->invoice_receipt_id = $id;

        $invoiceReceipt = InvoiceReceipt::find($id);
        $invoiceReceipt->taxAmount += $invoice_receipt_tax->taxAmount;
        $invoiceReceipt->receipt->taxTotal += $invoice_receipt_tax->taxAmount;
        $invoiceReceipt->invoice->balance -= $invoice_receipt_tax->taxAmount;

        if ($invoiceReceipt->invoice->balance <= 0) {
            $invoiceReceipt->invoice->paid = 'SI';
        }

        $invoice_receipt_tax->save();
        $invoiceReceipt->save();
        $invoiceReceipt->receipt->save();
        $invoiceReceipt->invoice->save();

        return redirect(route('showReceipt', $invoiceReceipt->receipt_id));
    }


    public function removeTaxFromInvoiceReceipt($taxId)
    {
        $tax = InvoiceReceiptTax::findOrFail($taxId);

        // Obtener relaciones necesarias
        $invoiceReceipt = $tax->invoiceReceipt;
        $invoice = $invoiceReceipt->invoice;
        $receipt = $invoiceReceipt->receipt;

        // Revertir efectos de la retención
        $invoiceReceipt->taxAmount -= $tax->taxAmount;
        $receipt->taxTotal -= $tax->taxAmount;
        $invoice->balance += $tax->taxAmount;

        // Si vuelve a quedar saldo pendiente, desmarcar como pagada
        if ($invoice->balance > 0) {
            $invoice->paid = 'NO';
        }

        // Guardar cambios
        $invoiceReceipt->save();
        $receipt->save();
        $invoice->save();

        // Eliminar la retención
        $tax->delete();

        return redirect()->route('showReceipt', $invoiceReceipt->receipt_id)
            ->with('success', 'Retención eliminada correctamente.');
    }

    public function removeFromReceipt($id)
    {
        $invoiceReceipt = InvoiceReceipt::findOrFail($id);
        $receipt = $invoiceReceipt->receipt;
        $invoice = $invoiceReceipt->invoice;

        // Sumar nuevamente el total de la factura al balance
        $invoice->balance += $invoiceReceipt->total;
        $receipt->total -= $invoiceReceipt->total;

        // Revertir todas las retenciones (InvoiceReceiptTax) asociadas
        foreach ($invoiceReceipt->taxes as $tax) {
            $invoice->balance += $tax->taxAmount;
            $receipt->taxTotal -= $tax->taxAmount;
            $invoiceReceipt->taxAmount -= $tax->taxAmount;
            $tax->delete();
        }

        // Ajustar estado del pago
        $invoice->paid = $invoice->balance > 0 ? 'NO' : 'SI';

        // Guardar los cambios
        $invoice->save();
        $receipt->save();
        $invoiceReceipt->save(); // Por si quedó taxAmount ajustado (aunque se eliminará)

        // Finalmente, eliminar el registro de relación
        $invoiceReceipt->delete();

        return redirect(route('showReceipt', $receipt->id))
            ->with('success', 'Factura y retenciones eliminadas del recibo correctamente.');
    }

    /**
     * Eliminar una factura vía AJAX o petición normal.
     * Solo se permite eliminar si no está facturada y no está pagada.
     */
    public function delete($id)
    {
        $invoice = Invoice::findOrFail($id);

        // Comprobar condiciones
        if ($invoice->invoiced === 'NO' && $invoice->paid === 'NO') {
            try {
                $invoice->delete();
                // Si es petición AJAX, devolver JSON
                if (request()->ajax()) {
                    return response()->json(['success' => true, 'message' => 'Factura eliminada correctamente.']);
                }
                return redirect(route('invoices'))->with('success', 'Factura eliminada correctamente.');
            } catch (\Exception $e) {
                if (request()->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Error al eliminar la factura.'], 500);
                }
                return redirect(route('showInvoice', $invoice->id))->with('error', 'Error al eliminar la factura.');
            }
        }

        // No permitido
        if (request()->ajax()) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar una factura facturada o pagada.'], 403);
        }

        return redirect(route('showInvoice', $invoice->id))->with('error', 'No se puede eliminar una factura facturada o pagada.');
    }
}
