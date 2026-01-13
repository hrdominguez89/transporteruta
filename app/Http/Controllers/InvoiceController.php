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

    // Nueva refactorización de la función generate() NO PERMITIR FACTURAS REPETIDAS
    public function generate(StoreInvoiceRequest $request)
    {
        try {
            $invoice = new Invoice();

            // ==================== REFACT PV (muy importante) ====================
            // Guardamos en la columna real de la BD => point_of_sale (snake_case).
            // Aceptamos ambos nombres desde el form para no romper compatibilidad.
            // ¡NO usar $invoice->pointOfSale acá si la columna no existe en la BD!
            $invoice->point_of_sale = (int) $request->input('point_of_sale', $request->input('pointOfSale'));
            // ===================================================================

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
        // // Traemos todo lo necesario para que el recálculo sea correcto
        // $invoice = Invoice::with(['client', 'travelCertificates.travelItems'])->findOrFail($id);

        // // ====================== TOTALES ======================
        // // Antes de marcar como "facturada" recalculamos los totales
        // // a partir de las constancias (fuente única de verdad).
        // // Esto evita desfasajes como balances negativos o totalWithIva erróneo
        // // cuando la BD quedó con valores viejos.
        // $this->recomputeInvoiceTotals($invoice);
        // // ============================================================

        // // Congelamos en BD los mismos importes que ve la UI
        // $invoice->invoiced     = 'SI';
        // $invoice->totalWithIva = round(($invoice->total ?? 0) + ($invoice->iva ?? 0), 2);
        // // Al facturar, el saldo inicial = total con IVA (luego se descuenta con recibos/retenciones)
        // $invoice->balance      = $invoice->totalWithIva;
        // $invoice->save();

        // // Actualizamos la CC del cliente con el total facturado
        // $client = $invoice->client; // ya viene cargado por el with()
        // $client->balance += $invoice->totalWithIva;
        // $client->save();
        $invoice = Invoice::find($id);
        $invoice->invoiced = 'SI';
        $invoice->totalWithIva = ($invoice->total + $invoice->iva);
        $invoice->balance = $invoice->totalWithIva;
        $invoice->save();
        $client = Client::find($invoice->client->id);
        $client->balance += $invoice->totalWithIva;
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
        $invoice = Invoice::find($id);
        if($credits->isNotEmpty())
        {
            return redirect(route('showInvoice', $invoice->id))->with(['flag' => true, 'message' => 'Debe quitar las notas de credito antes de anular una factura.']);
        }
        $invoice->invoiced     = 'NO';
        $invoice->balance      = 0;
        $client = Client::find($invoice->client->id);
        $client->balance      -= $invoice->totalWithIva;
        $invoice->totalWithIva = 0;
        $client->save();
        $invoice->save();
        return redirect(route('showInvoice', $invoice->id));
    }

    // ======================= REFACT: Asociación de constancias a factura =======================
    /**
     * Helper interno para recalcular totales de la factura a partir de sus constancias:
     *  - total (Neto + Peajes)  [SIN IVA]
     *  - iva
     *  - totalWithIva (si ya está facturada; si NO, lo dejamos en 0 para no confundir)
     *
     * No toca balance/paid, eso se resuelve cuando se marca como facturada y al registrar pagos.
     */
    // IMPORTANTE: arriba ya tenés use App\Models\InvoiceReceipt; correcto.
    // Si no está, asegurate de tenerlo:
    // use App\Models\InvoiceReceipt;

// ======================= REFACTORIZACION: Recalcular totales y balance =======================
// private function recomputeInvoiceTotals(Invoice $invoice): void
// {
//     $invoice->loadMissing('client');

//     // --- Condición IVA del cliente ---
//     $condIva  = strtoupper($invoice->client->ivaCondition ?? $invoice->client->iva_condition ?? $invoice->client->ivaType ?? '');
//     $esExento = strpos($condIva, 'EXENTO') !== false;

//     // --- Sumas desde constancias (fuente de verdad) ---
//     $items = TravelCertificate::where('invoiceId', $invoice->id)->get();

//     $sumNeto   = 0.0; // sin peajes
//     $sumIva    = 0.0;
//     $sumPeajes = 0.0;

//     foreach ($items as $tc) {
//         $peajes = TravelItem::where('type', 'PEAJE')
//             ->where('travelCertificateId', $tc->id)
//             ->sum('price');

//         // === NUEVO: asumimos que el total de la constancia YA incluye IVA + peajes ===
//         // Si alguna vez querés volver al cálculo anterior, poné este flag en false.
//         $constanciaIncluyeIva = true;

//         // Base sin peajes (lo que queda es neto+iva o solo neto si exento)
//         $subtotalSinPeajes = max(0, (float)$tc->total - (float)$peajes);

//         if ($constanciaIncluyeIva) {
//             if (!$esExento) {
//                 // separar neto e IVA de una base que ya viene con IVA 21%
//                 $neto = round($subtotalSinPeajes / 1.21, 2);
//                 $iva  = round($subtotalSinPeajes - $neto, 2);
//             } else {
//                 // exento: todo es neto, IVA=0
//                 $neto = round($subtotalSinPeajes, 2);
//                 $iva  = 0.0;
//             }
//         } else {
//             // ===== CÁLCULO ANTERIOR (lo dejamos como fallback, NO se ejecuta con el flag en true) =====
//             $descuento = (float)($tc->descuento_aplicable ?? 0);
//             if (!$descuento && isset($tc->descuento_porcentaje)) {
//                 $descuento = round($subtotalSinPeajes * ((float)$tc->descuento_porcentaje) / 100, 2);
//             }
//             $montoAdic = (float)($tc->monto_adicional ?? 0);
//             $netoCalc  = ($subtotalSinPeajes - $descuento) + $montoAdic;
//             $neto = $netoCalc;
//             $iva  = $esExento ? 0.0 : round($netoCalc * 0.21, 2);
//             // ==========================================================================================
//         }

//         $sumNeto   += $neto;
//         $sumIva    += $iva;
//         $sumPeajes += (float)$peajes;
//     }

//     // --- Persistimos totales de la factura ---
//     $invoice->total = round($sumNeto + $sumPeajes, 2);  // SIN IVA (neto + peajes)
//     $invoice->iva   = round($sumIva, 2);

//     // Congelar totalWithIva solo si está facturada (si NO, lo dejamos en 0 como venías manejando)
//     $totalConIva = $invoice->total + $invoice->iva;
//     $invoice->totalWithIva = $invoice->invoiced === 'SI' ? $totalConIva : 0.0;

//     // --- NUEVO: Normalizar pagos/retenciones SIN reventar si la pivot no existe ---
//     $pagos = 0.0;
//     $retenciones = 0.0;

//     try {
//         $pivotCandidates = ['invoice_receipts', 'invoice_receipt', 'invoice_receipt_pivot', 'invoices_receipts'];
//         $pivotTable = null;

//         foreach ($pivotCandidates as $cand) {
//             if (Schema::hasTable($cand)) { $pivotTable = $cand; break; }
//         }

//         if ($pivotTable) {
//             // Detectar posibles nombres de columnas
//             $invoiceCol = Schema::hasColumn($pivotTable, 'invoice_id') ? 'invoice_id'
//                          : (Schema::hasColumn($pivotTable, 'invoiceId') ? 'invoiceId' : null);

//             $totalCol   = Schema::hasColumn($pivotTable, 'total') ? 'total'
//                          : (Schema::hasColumn($pivotTable, 'amount') ? 'amount' : null);

//             $taxCol     = Schema::hasColumn($pivotTable, 'taxAmount') ? 'taxAmount'
//                          : (Schema::hasColumn($pivotTable, 'tax_amount') ? 'tax_amount' : null);

//             if ($invoiceCol) {
//                 if ($totalCol) {
//                     $pagos = (float) DB::table($pivotTable)->where($invoiceCol, $invoice->id)->sum($totalCol);
//                 }
//                 if ($taxCol) {
//                     $retenciones = (float) DB::table($pivotTable)->where($invoiceCol, $invoice->id)->sum($taxCol);
//                 }
//             }
//         }
//     } catch (\Throwable $e) {
//         $pagos = 0.0;
//         $retenciones = 0.0;
//     }

//     // --- Balance y estado de pago ---
//     $expectedBalance = round($totalConIva - $pagos - $retenciones, 2);

//     if ($invoice->invoiced === 'SI') {
//         $invoice->balance = $expectedBalance;
//         $invoice->paid    = $expectedBalance <= 0 ? 'SI' : 'NO';
//     } else {
//         $invoice->balance = 0.0;
//         $invoice->paid    = 'NO';
//     }

//     $invoice->save();
// }


    /**
     * Wrapper público para reparar una factura puntual desde la UI.
     * Útil para normalizar saldos viejos que hayan quedado mal grabados.
     */
    // public function normalizeInvoice($id)
    // {
    //     $invoice = Invoice::with(['client'])->findOrFail($id);
    //     $this->recomputeInvoiceTotals($invoice);
    //     return back()->with('success', 'Factura recalculada y balance normalizado.');
    // }

    // Agregar UNA constancia a la factura (usa el botón "Agregar a la Factura")
    public function addToInvoice(Request $request, $travelCertificateId)
    {
        $travelCertificate = TravelCertificate::find($travelCertificateId);
        if($travelCertificate->invoiced =='NO')
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
            session()->flash('error', 'Este certificado ya esta facturado.');
        }
        return redirect(route('showInvoice', $travelCertificate->invoiceId));
    }

    // Agregar VARIAS constancias (usa el botón masivo)
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
    // ===================== /REFACTORIZACION asociación de constancias =====================

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