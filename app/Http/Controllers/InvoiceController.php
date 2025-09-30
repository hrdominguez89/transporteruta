<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Receipt;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
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

            // Si invoices.receiptId es nullable y con FK, conviene NULL (no 0).
            // $invoice->receiptId    = 0;
            // Recomendado:
            $invoice->receiptId    = null;

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
        // Traemos todo lo necesario para que el recálculo sea correcto
        $invoice = Invoice::with(['client', 'travelCertificates.travelItems'])->findOrFail($id);

        // ====================== TOTALES ======================
        // Antes de marcar como "facturada" recalculamos los totales
        // a partir de las constancias (fuente única de verdad).
        // Esto evita desfasajes como balances negativos o totalWithIva erróneo
        // cuando la BD quedó con valores viejos.
        $this->recomputeInvoiceTotals($invoice);
        // ============================================================

        // Congelamos en BD los mismos importes que ve la UI
        $invoice->invoiced     = 'SI';
        $invoice->totalWithIva = round(($invoice->total ?? 0) + ($invoice->iva ?? 0), 2);
        // Al facturar, el saldo inicial = total con IVA (luego se descuenta con recibos/retenciones)
        $invoice->balance      = $invoice->totalWithIva;
        $invoice->save();

        // Actualizamos la CC del cliente con el total facturado
        $client = $invoice->client; // ya viene cargado por el with()
        $client->balance += $invoice->totalWithIva;
        $client->save();

        return redirect(route('showInvoice', $invoice->id));
    }

    public function cancel($id)
    {
        $invoice = Invoice::find($id);
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
private function recomputeInvoiceTotals(Invoice $invoice): void
{
    $invoice->loadMissing('client');

    // --- Condición IVA del cliente ---
    $condIva  = strtoupper($invoice->client->ivaCondition ?? $invoice->client->iva_condition ?? $invoice->client->ivaType ?? '');
    $esExento = strpos($condIva, 'EXENTO') !== false;

    // --- Sumas desde constancias (fuente de verdad) ---
    $items = TravelCertificate::where('invoiceId', $invoice->id)->get();

    $sumNeto   = 0.0; // sin peajes (con descuentos/adicionales)
    $sumIva    = 0.0;
    $sumPeajes = 0.0;

    foreach ($items as $tc) {
        $peajes = TravelItem::where('type', 'PEAJE')
            ->where('travelCertificateId', $tc->id)
            ->sum('price');

        $subtotalSinPeajes = max(0, (float)$tc->total - (float)$peajes);

        $descuento = (float)($tc->descuento_aplicable ?? 0);
        if (!$descuento && isset($tc->descuento_porcentaje)) {
            $descuento = round($subtotalSinPeajes * ((float)$tc->descuento_porcentaje) / 100, 2);
        }

        $montoAdic = (float)($tc->monto_adicional ?? 0);
        $neto      = ($subtotalSinPeajes - $descuento) + $montoAdic;
        $iva       = $esExento ? 0.0 : round($neto * 0.21, 2);

        $sumNeto   += $neto;
        $sumIva    += $iva;
        $sumPeajes += (float)$peajes;
    }

    // --- Persistimos totales de la factura ---
    $invoice->total = round($sumNeto + $sumPeajes, 2);  // SIN IVA
    $invoice->iva   = round($sumIva, 2);

    // Congelar totalWithIva solo si está facturada (si NO, lo dejamos en 0 como venías manejando)
    $totalConIva = $invoice->total + $invoice->iva;
    $invoice->totalWithIva = $invoice->invoiced === 'SI' ? $totalConIva : 0.0;

    // --- NUEVO: Normalizar pagos/retenciones SIN reventar si la pivot no existe ---
    // Intentamos detectar la tabla/columnas reales de la pivot. Si no existen, asumimos 0.
    $pagos = 0.0;
    $retenciones = 0.0;

    try {
        $pivotCandidates = ['invoice_receipts', 'invoice_receipt', 'invoice_receipt_pivot', 'invoices_receipts'];
        $pivotTable = null;

        foreach ($pivotCandidates as $cand) {
            if (Schema::hasTable($cand)) { $pivotTable = $cand; break; }
        }

        if ($pivotTable) {
            // Detectar posibles nombres de columnas
            $invoiceCol = Schema::hasColumn($pivotTable, 'invoice_id') ? 'invoice_id'
                         : (Schema::hasColumn($pivotTable, 'invoiceId') ? 'invoiceId' : null);

            $totalCol   = Schema::hasColumn($pivotTable, 'total') ? 'total'
                         : (Schema::hasColumn($pivotTable, 'amount') ? 'amount' : null);

            $taxCol     = Schema::hasColumn($pivotTable, 'taxAmount') ? 'taxAmount'
                         : (Schema::hasColumn($pivotTable, 'tax_amount') ? 'tax_amount' : null);

            if ($invoiceCol) {
                if ($totalCol) {
                    $pagos = (float) DB::table($pivotTable)->where($invoiceCol, $invoice->id)->sum($totalCol);
                }
                if ($taxCol) {
                    $retenciones = (float) DB::table($pivotTable)->where($invoiceCol, $invoice->id)->sum($taxCol);
                }
            }
        }

        // IMPORTANTE:
        // No hacemos fallback al modelo InvoiceReceipt ni a la relación belongsToMany
        // para evitar SQLSTATE cuando el esquema difiere entre entornos.
    } catch (\Throwable $e) {
        // Ante cualquier excepción, dejamos pagos/retenciones en 0 para no romper el flujo.
        $pagos = 0.0;
        $retenciones = 0.0;
    }

    // --- Balance y estado de pago ---
    $expectedBalance = round($totalConIva - $pagos - $retenciones, 2);

    if ($invoice->invoiced === 'SI') {
        // Facturada: el saldo refleja total - pagos - retenciones
        $invoice->balance = $expectedBalance;
        $invoice->paid    = $expectedBalance <= 0 ? 'SI' : 'NO';
    } else {
        // NO facturada: saldo en 0 para no confundir en la UI
        $invoice->balance = 0.0;
        $invoice->paid    = 'NO';
    }

    $invoice->save();
}

    /**
     * Wrapper público para reparar una factura puntual desde la UI.
     * Útil para normalizar saldos viejos que hayan quedado mal grabados.
     */
    public function normalizeInvoice($id)
    {
        $invoice = Invoice::with(['client'])->findOrFail($id);
        $this->recomputeInvoiceTotals($invoice);
        return back()->with('success', 'Factura recalculada y balance normalizado.');
    }

    // Agregar UNA constancia a la factura (usa el botón "Agregar a la Factura")
    public function addToInvoice(Request $request, $travelCertificateId)
    {
        $invoiceId = (int) $request->input('invoiceId');
        $invoice   = Invoice::with('client')->findOrFail($invoiceId);

        if ($invoice->invoiced === 'SI') {
            return back()->with('error', 'No se puede modificar una factura ya facturada.');
        }

        $tc = TravelCertificate::with('driver')->findOrFail($travelCertificateId);

        // Validar que la constancia sea del mismo cliente
        if ((int)$tc->clientId !== (int)$invoice->clientId) {
            return back()->with('error', "La constancia {$tc->id} pertenece a otro cliente.");
        }

        // ----- REFACT: cálculos SOLO en memoria (no persistimos columnas inexistentes) -----
        // (ANTES) Se intentaba escribir total_peajes / subtotal_sin_peajes / iva_calculado y fallaba.
        // $tc->total_peajes = ...
        // $tc->subtotal_sin_peajes = ...
        // $tc->iva_calculado = ...

        // Peajes desde TravelItem
        $totalPeajes = TravelItem::where('type', 'PEAJE')
            ->where('travelCertificateId', $tc->id)
            ->sum('price');

        // Subtotal sin peajes
        $subtotalSinPeajes = max(0, (float)$tc->total - (float)$totalPeajes);

        // Descuento: si no hay monto pero sí porcentaje, lo calculamos
        $descuento = (float)($tc->descuento_aplicable ?? 0);
        if (!$descuento && isset($tc->descuento_porcentaje)) {
            $descuento = round($subtotalSinPeajes * ((float)$tc->descuento_porcentaje) / 100, 2);
            // NOTA: no lo persistimos para evitar columnas faltantes
        }

        // Adicional (monto)
        $montoAdic = (float)($tc->monto_adicional ?? 0);

        // Neto refactor = (subtotal_sin_peajes - descuento_aplicable) + monto_adicional
        $neto = ($subtotalSinPeajes - $descuento) + $montoAdic;

        // IVA: 0 si EXENTO (solo cálculo, no guardamos)
        $condIva  = strtoupper($invoice->client->ivaCondition ?? $invoice->client->iva_condition ?? $invoice->client->ivaType ?? '');
        $esExento = strpos($condIva, 'EXENTO') !== false;
        $ivaCalculado = $esExento ? 0 : round($neto * 0.21, 2);
        // -----------------------------------------------------------------------------------

        // Vincular a la factura (único cambio persistente)
        $tc->invoiceId = $invoice->id;
        $tc->save();

        // Recalcular totales de la factura
        $this->recomputeInvoiceTotals($invoice);

        return redirect()->route('showInvoice', $invoice->id)->with('success', 'Constancia agregada a la factura.');
    }

    // Agregar VARIAS constancias (usa el botón masivo)
    public function addMultipleToInvoice(Request $request)
    {
        $invoiceId = (int) $request->input('invoiceId');
        $ids       = (array) $request->input('ids', []);

        $invoice = Invoice::with('client')->findOrFail($invoiceId);
        if ($invoice->invoiced === 'SI') {
            return back()->with('error', 'No se puede modificar una factura ya facturada.');
        }

        DB::transaction(function () use ($ids, $invoice) {
            foreach ($ids as $travelCertificateId) {
                $tc = TravelCertificate::findOrFail($travelCertificateId);

                if ((int)$tc->clientId !== (int)$invoice->clientId) {
                    // Si querés continuar en lugar de abortar, reemplazá por "continue;"
                    throw new \RuntimeException("La constancia {$tc->id} pertenece a otro cliente.");
                }

                // ----- REFACT: cálculos SOLO en memoria (idénticos a addToInvoice) -----
                $totalPeajes = TravelItem::where('type', 'PEAJE')
                    ->where('travelCertificateId', $tc->id)
                    ->sum('price');

                $subtotalSinPeajes = max(0, (float)$tc->total - (float)$totalPeajes);

                $descuento = (float)($tc->descuento_aplicable ?? 0);
                if (!$descuento && isset($tc->descuento_porcentaje)) {
                    $descuento = round($subtotalSinPeajes * ((float)$tc->descuento_porcentaje) / 100, 2);
                }

                $montoAdic = (float)($tc->monto_adicional ?? 0);
                $neto      = ($subtotalSinPeajes - $descuento) + $montoAdic;

                $condIva  = strtoupper($invoice->client->ivaCondition ?? $invoice->client->iva_condition ?? $invoice->client->ivaType ?? '');
                $esExento = strpos($condIva, 'EXENTO') !== false;
                $ivaCalculado = $esExento ? 0 : round($neto * 0.21, 2);
                // -----------------------------------------------------------------------

                // ÚNICO campo que persistimos
                $tc->invoiceId = $invoice->id;
                $tc->save();
            }
        });

        // Recalcular totales de la factura
        $this->recomputeInvoiceTotals($invoice);

        return redirect()->route('showInvoice', $invoice->id)->with('success', 'Constancias agregadas a la factura.');
    }

    // Quitar UNA constancia de la factura
    public function removeFromInvoice($travelCertificateId)
    {
        $tc = TravelCertificate::findOrFail($travelCertificateId);
        $invoiceId = $tc->invoiceId;

        $invoice = Invoice::findOrFail($invoiceId);
        if ($invoice->invoiced === 'SI') {
            return back()->with('error', 'No se puede modificar una factura ya facturada.');
        }

        $tc->invoiceId = null;
        // Dejamos los importes en la constancia como histórico (no se borran)
        $tc->save();

        // Recalcular totales de la factura
        $this->recomputeInvoiceTotals($invoice);

        return redirect()->route('showInvoice', $invoiceId)->with('success', 'Constancia quitada de la factura.');
    }

    // Quitar VARIAS constancias (botón masivo)
    public function removeMultipleFromInvoice(Request $request)
    {
        $invoiceId = (int) $request->input('invoiceId');
        $ids       = (array) $request->input('ids', []);

        $invoice = Invoice::findOrFail($invoiceId);
        if ($invoice->invoiced === 'SI') {
            return back()->with('error', 'No se puede modificar una factura ya facturada.');
        }

        DB::transaction(function () use ($ids, $invoiceId) {
            foreach ($ids as $travelCertificateId) {
                $tc = TravelCertificate::findOrFail($travelCertificateId);
                if ((int)$tc->invoiceId !== (int)$invoiceId) {
                    continue; // no pertenece a esta factura
                }
                $tc->invoiceId = null;
                $tc->save();
            }
        });

        // Recalcular totales de la factura
        $this->recomputeInvoiceTotals($invoice);

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