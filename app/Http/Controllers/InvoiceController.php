<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Receipt;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Credit;
use App\Models\Debit;
use App\Models\InvoiceReceipt;
use App\Models\InvoiceReceiptTax;
use App\Models\TravelItem;
use App\Models\TravelCertificate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // TEAM: agregado para detectar nombre real de tabla/columnas pivot
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
    public function edit(Request $request,$id)
    {
        $invoice = Invoice::find($id);
        $invoice->reference = $request->reference;
        $invoice->date = $request->date;
        $invoice->save();
        return redirect(route('showInvoice', $invoice->id));
    }   
    // Nueva refactorización de la función generate() NO PERMITIR FACTURAS REPETIDAS
    public function generate(StoreInvoiceRequest $request)
    {
        try {
            $invoice = new Invoice();
            $invoice->point_of_sale = (int) $request->input('point_of_sale', $request->input('pointOfSale'));
            $invoice->number        = (int) $request->number;
            $invoice->date          = $request->date; // o Carbon::parse($request->date)
            $invoice->clientId      = (int) $request->clientId;

            // Valores iniciales
            $invoice->total        = 0;
            $invoice->iva          = 0;
            $invoice->totalWithIva = 0;
            $invoice->balance      = 0;
            $invoice->paid         = 'NO';
            $invoice->invoiced     = 'NO';

            $invoice->receiptId    = 0;
            $invoice->reference = $request->reference;
            $invoice->save();

            return redirect()->route('showInvoice', $invoice->id);

        } catch (\Illuminate\Database\QueryException $e) {
            // si salta duplicado PV+Número, lo devolvemos al modal con error
            if (($e->errorInfo[1] ?? null) === 1062) {
                return back()
                    ->withErrors(['number' => 'Ya existe una factura con ese Punto de Venta y Número.'])
                    ->withInput();
            }
            throw $e;
        }
    }

    public function show($id)
    {
        /* REFACTORIZACIÓN (show):
         * - Eager load: client, travelCertificates.travelItems y también las del cliente.
         * - Usamos accessors del modelo (total_peajes, total_calculado, iva_calculado)
         *   para que ADICIONAL (%) y DESCUENTO (%) impacten sin tocar la DB.
         * - Exento de IVA: seteamos IVA=0 si el cliente es EXENTO.
         * - Exponemos en cada constancia los campos que la vista espera: peajes, importeNeto, iva.
         */
        $invoice = Invoice::with([
            'client',
            'travelCertificates.travelItems',
            'client.travelCertificates.travelItems',
        ])->findOrFail($id);

        $client = $invoice->client;

        // Detectar condición IVA del cliente (EXENTO => IVA=0)
        $condIva  = strtoupper($client->ivaCondition ?? $client->iva_condition ?? $client->ivaType ?? '');
        $esExento = strpos($condIva, 'EXENTO') !== false;

        $totalTolls = 0.0;

        // Enriquecer constancias incluidas en la factura
        foreach ($invoice->travelCertificates as $tc) {
            $tc->peajes      = (float) $tc->total_peajes;                                // solo peajes
            $tc->importeNeto = max(0, (float) $tc->total_calculado - $tc->total_peajes); // neto SIN peajes
            $tc->iva         = $esExento ? 0.0 : (float) $tc->iva_calculado;             // IVA gravado

            $totalTolls += $tc->peajes;
        }

        // Enriquecer constancias del cliente (sección "sin liquidar")
        foreach ($client->travelCertificates as $tc) {
            $tc->peajes      = (float) $tc->total_peajes;
            $tc->importeNeto = max(0, (float) $tc->total_calculado - $tc->total_peajes);
            $tc->iva         = $esExento ? 0.0 : (float) $tc->iva_calculado;
        }

        return view('invoice.show', [
            'invoice'    => $invoice,
            'clients'    => $client,     // <- la vista espera "clients" para el bloque de "sin liquidar"
            'totalTolls' => $totalTolls, // peajes totales de las constancias incluidas
        ]);
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
        if($invoice->invoiced == 'SI')
        {
            return redirect(route('showInvoice', $invoice->id));    
        }
        $invoice->invoiced = 'SI';
        $invoice->totalWithIva = ($invoice->total + $invoice->iva);
        $invoice->balance = $invoice->totalWithIva;
        $invoice->save();
        $client = Client::find($invoice->client->id);
        $client->balance += $invoice->balance;
        $client->save();
        $travel_certificate_array = TravelCertificate::where('invoiceId', $invoice->id)->get();
        foreach( $travel_certificate_array as $travel_certificate )
        {
            $travel_certificate->invoiced='SI';
            $travel_certificate->save();
        }
        return redirect(route('showInvoice', $invoice->id));
    }

    public function cancel($id)
    {
        $credits = Credit::where('invoiceId',$id)->get();
        $debits = Debit::where('invoiceId',$id)->get();
        $invoice = Invoice::find($id);
        if($credits->isNotEmpty() || $debits->isNotEmpty())
        {
            return redirect(route('showInvoice', $invoice->id))->with(['flag' => true, 'message' => 'Debe quitar las notas de credito y debito antes de anular una factura.']);
        }
        $invoice->invoiced     = 'NO';
        $client = Client::find($invoice->client->id);
        $client->balance      -= $invoice->balance;
        $invoice->balance      = 0;
        $invoice->totalWithIva = 0;
        $client->save();
        $invoice->save();
        return redirect(route('showInvoice', $invoice->id));
    }
    public function validarHorarios($travel_certificate)
    {
        // $items = TravelItem::where('type','HORA')
        // ->where('travelCertificateId',$travel_certificate->id)->get();
        // if(!$items->isNotEmpty())
        // {
        //     return true;
        // }
        // if($travel_certificate->horaLLegada == null || $travel_certificate->horaSalida == null)
        // {
        //     return false;
        // }
        return true;
    }
    public function addToInvoice(Request $request, $travelCertificateId)
    {
        $travelCertificate = TravelCertificate::find($travelCertificateId);
        if($travelCertificate->invoiced =='NO')
        {
            if($this->validarHorarios($travelCertificate))
            {
                $travelCertificate->invoiceId = $request->invoiceId;
                $invoice = Invoice::find($request->invoiceId);
                $invoice->total += $travelCertificate->total;
                $invoice->iva += $travelCertificate->iva;
                $travelCertificate->invoiced = 'SI';
                $travelCertificate->save();
                $invoice->save();
            }
            else
            {
                session()->flash('flag', true);
                session()->flash('message', 'Este certificado tiene valor por hora y necesita que se le asigne horario de salida y llegada para ser agregado a la factura.');    
            }
        }
        else
        {
            session()->flash('flag', true);
            session()->flash('message', 'Este certificado ya esta facturado.');
        }
        return redirect(route('showInvoice',  $request->invoiceId));
    }
    public function addMultipleToInvoice(Request $request)
    {
        $invoiceId = (int) $request->input('invoiceId');
        $ids       = (array) $request->input('ids', []);

        $invoice = Invoice::with('client')->findOrFail($invoiceId);
        // if ($invoice->invoiced === 'SI') {
        //     return back()->with('error', 'No se puede modificar una factura ya facturada.');
        // }

        DB::transaction(function () use ($ids, $invoice) {
            foreach ($ids as $travelCertificateId) {
                $tc = TravelCertificate::findOrFail($travelCertificateId);

                if ((int)$tc->clientId !== (int)$invoice->clientId) {
                    // Si querés continuar en lugar de abortar, reemplazá por "continue;"
                    throw new \RuntimeException("La constancia {$tc->id} pertenece a otro cliente.");
                }
                //TODO:agregar validacion de que no este ya facturado el ceritificado de viaje. 
                $invoice->total += $tc->total;
                $invoice->iva += $tc->iva;
                $tc->invoiced = 'SI';
                $tc->save();
                $invoice->save();
            }
        });

        return redirect()->route('showInvoice', $invoice->id)->with('success', 'Constancias agregadas a la factura.');
    }

    public function removeFromInvoice($travelCertificateId)
    {
        $tc = TravelCertificate::findOrFail($travelCertificateId);
        $invoiceId = $tc->invoiceId;

        $invoice = Invoice::findOrFail($invoiceId);
        if ($invoice->invoiced === 'SI') {
            return back()->with('error', 'No se puede modificar una factura ya facturada.');
        }

        $tc->invoiceId = 0;
        $tc->invoiced = 'NO';
        $invoice->total -= $tc->total;
        $invoice->iva -= $tc->iva;
        $invoice->save();
        $tc->save();

        return redirect()->route('showInvoice', $invoiceId)->with('success', 'Constancia quitada de la factura.');
    }

    public function removeMultipleFromInvoice(Request $request)
    {
        $invoiceId = (int) $request->input('invoiceId');
        $ids       = (array) $request->input('ids', []);
        
        DB::transaction(function () use ($ids, $invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice->invoiced === 'SI') {
                    return back()->with('error', 'No se puede modificar una factura ya facturada.');
            }
            foreach ($ids as $travelCertificateId) {
                $tc = TravelCertificate::findOrFail($travelCertificateId);
                
                $invoice->total -= $tc->total;
                $invoice->iva -= $tc->iva;
                $tc->invoiceId = 0;
                $tc->invoiced = 'NO';
                $tc->save();
                $invoice->save();
            }
        });

        return redirect()->route('showInvoice', $invoiceId)->with('success', 'Constancias quitadas de la factura.');
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
                $this->limpiarFacturaAntesDeEliminar($invoice->id);
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
    public function limpiarFacturaAntesDeEliminar($id)
    {
        TravelCertificate::where('invoiceId',$id)
        ->update([
            'invoiceId' => 0,
            'invoiced' => 'NO'
        ]);
    }
}