<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Client;
use App\Models\PaymentMethod;
use App\Models\Tax;
use App\Http\Requests\StoreReceiptRequest;
use App\Http\Requests\UpdateReceiptRequest;
use App\Models\InvoiceReceipt;
use App\Models\Payments;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function receipts()
    {
        $receipts = Receipt::all();
        $clients = Client::all();
        $paymentMethods = PaymentMethod::all();
        return view('receipt.index', ['receipts' => $receipts, 'clients' => $clients, 'paymentMethods' => $paymentMethods]);
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
        
        $receipt = Receipt::findOrFail($id);
        $paymentMethods = PaymentMethod::all();
        $taxes = Tax::all();
        $pagos = Payments::where('clientId',$receipt->clientId)->get();
        $invoicesToAdd = $receipt->client->invoices()
            ->where('paid', 'NO')
            ->where('invoiced', 'SI')
            ->get();

        $receiptInvoices = InvoiceReceipt::where('receipt_id', $id)
            ->with('invoice') // opcional, para mostrar datos de la factura
            ->orderByDesc('id') // orden descendente por ID
            ->get();

        return view('receipt.show', [
            'receipt' => $receipt,
            'paymentMethods' => $paymentMethods,
            'taxes' => $taxes,
            'receiptInvoices' => $receiptInvoices,
            'invoicesToAdd' => $invoicesToAdd,
            'pagos' => $pagos
        ]);
    }

    public function generateReceiptPdf($id)
    {
        $receipt = Receipt::findOrFail($id);
        
        $receiptInvoices = InvoiceReceipt::where('receipt_id', $id)
            ->with('invoice')
            ->orderByDesc('id')
            ->get();

        if ($receipt->paymentspivot()->exists())
        {
            $pagos = $receipt->paymentspivot()->get();
            $pdf = Pdf::loadView('receipt.newpdf', [
                'receipt' => $receipt,
                'receiptInvoices' => $receiptInvoices,
                'pagos' => $pagos]);
            $pdf->setPaper('A4', 'landscape');
            return $pdf->stream('Recibo-' . $receipt->client->name . '-(' . $receipt->date . ').pdf');
        }
        else
        {
            $taxes = Tax::all();
            $paymentMethods = PaymentMethod::all();

            $invoicesToAdd = $receipt->client->invoices()
                ->where('paid', 'NO')
                ->where('invoiced', 'SI')
                ->get();

            $pdf = Pdf::loadView('receipt.pdf', [
                'receipt' => $receipt, 
                'receiptInvoices' => $receiptInvoices,
                'paymentMethods' => $paymentMethods, 
                'taxes' => $taxes, 
                'invoicesToAdd' => $invoicesToAdd, 
                ]);
            $pdf->setPaper('A4', 'landscape');
            return $pdf->stream('Recibo-' . $receipt->client->name . '-(' . $receipt->date . ').pdf');
        }
    }

    public function paid($id)
    {
        //primero validar que tenga asignada una factura al momento de poder marcar como pagada. 
        $receipt = Receipt::find($id);
        if($receipt->paid == 'SI')
        {
            return redirect(route('showReceipt', $receipt->id));    
        }
        $receipt->paid = 'SI';
        $receipt->save();
        $client = Client::find($receipt->client->id);
        $client->balance -= $receipt->total + $receipt->taxTotal;
        $client->save();
        return redirect(route('showReceipt', $receipt->id));
    }

    public function cancel($id)
    {
        $receipt = Receipt::find($id);
        if($receipt->paid == 'NO')
        {
            return redirect(route('showReceipt', $receipt->id));
        }
        $receipt->paid = 'NO';
        $receipt->save();
        $client = Client::find($receipt->client->id);
        $client->balance += $receipt->total + $receipt->taxTotal;
        $client->save();
        return redirect(route('showReceipt', $receipt->id));
    }
    public function addPaymentToReceipt(Request $request , $id)
    {
        $receipt = Receipt::find($id);
        $payment = Payments::find($request->payment_id);
        if($receipt->paid == 'SI')
        {
            return back()->withErrors(['mensaje' => 'El recibo no puede agregar pagos si esta marcado como pagado.']);
        }

        $monto = $request->monto;
        
        if ($monto > $payment->total) {
            return back()->withErrors(['mensaje' => 'El monto no puede ser mayor al total del pago.']);
        }
        if ($monto > $payment->balance) {
            return back()->withErrors(['mensaje' => 'El monto no puede ser mayor al saldo disponible del pago.']);
        }

        // Crear la relacion pivot asignando el monto
        $receipt->paymentspivot()->attach($payment->id, ['total' => $monto]);

        // Sumar el monto al saldo disponible del recibo
        $receipt->available_balance = ($receipt->available_balance ?? 0) + $monto;
        $receipt->save();

        // Descontar el monto del balance del pago
        $payment->balance -= $monto;
        $payment->save();

        return redirect(route('showReceipt', $receipt->id));
    }
    public function quitPaymentToReceipt(Request $request , $id)
    {
        $receipt = Receipt::find($id);
        $payment = Payments::find($request->payment_id);

        // Obtener el monto desde la relacion pivot
        $monto = $receipt->paymentspivot()->where('paymentId', $payment->id)->first()->pivot->total;

        // Eliminar la relacion pivot
        $receipt->paymentspivot()->detach($payment->id);

        // Restar el monto del saldo disponible del recibo
        $receipt->available_balance = ($receipt->available_balance ?? 0) - $monto;
        $receipt->save();

        // Devolver el monto al balance del pago
        $payment->balance += $monto;
        $payment->save();

        return redirect(route('showReceipt', $receipt->id));
    }
    public function editPaymentFromReceipt(Request $request, $id)
    {
        $receipt = Receipt::find($id);
        $payment = Payments::find($request->payment_id);
        $nuevoMonto = $request->monto;

        // Obtener el monto anterior desde la relacion pivot
        $montoAnterior = $receipt->paymentspivot()->where('paymentId', $payment->id)->first()->pivot->total;

        // Saldo disponible virtual del pago (recuperando el monto anterior)
        $saldoDisponible = $payment->balance + $montoAnterior;

        if ($nuevoMonto > $payment->total) {
            return back()->withErrors(['mensaje' => 'El monto no puede ser mayor al total del pago.']);
        }
        if ($nuevoMonto > $saldoDisponible) {
            return back()->withErrors(['mensaje' => 'El monto no puede ser mayor al saldo disponible del pago.']);
        }

        // Revertir el monto anterior al balance del pago y al available_balance del recibo
        $payment->balance += $montoAnterior;
        $receipt->available_balance = ($receipt->available_balance ?? 0) - $montoAnterior;

        // Actualizar la relacion pivot con el nuevo monto
        $receipt->paymentspivot()->updateExistingPivot($payment->id, ['total' => $nuevoMonto]);

        // Aplicar el nuevo monto
        $payment->balance -= $nuevoMonto;
        $payment->save();

        $receipt->available_balance += $nuevoMonto;
        $receipt->save();

        return redirect(route('showReceipt', $receipt->id));
    }
}
