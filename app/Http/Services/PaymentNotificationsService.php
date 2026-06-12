<?php
namespace App\Http\Services;

use App\Models\Contacto;
use App\Models\Credit;
use App\Models\Debit;
use App\Models\Invoice;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PaymentNotificationsService
{
    public function index(): void
    {
        $facturasPorCliente = $this->validarNotificacion();
        $sinNotificar=[];
        foreach ($facturasPorCliente as $clienteId => $invoices) {
            if(!$this->enviar($invoices))
            {
                $sinNotificar[] = $invoices[0]->client->name;
            }
            break;
        }
    }

    public function enviar($invoices): bool
    {
        $cantidadVencidas = $invoices->filter(
            fn($invoice) => $invoice->date && $invoice->client &&
                $invoice->date->addDays($invoice->client->paymentTermDays)->isPast() && $invoice->paid == 'NO'
        )->count();

        $cantidadEnPlazo = $invoices->filter(
            fn($invoice) => $invoice->date && $invoice->client &&
                $invoice->date->addDays($invoice->client->paymentTermDays) < now()->addDays(6) &&
                $invoice->date->addDays($invoice->client->paymentTermDays)->isFuture()
                && $invoice->paid == 'NO'
        )->count();

        $cliente = $invoices[0]->client->name;
        $destinatario = Contacto::where('client_id',$invoices[0]->client->id)
                                ->where('categoria','Depto. Cobros y Pagos')->first();
        
        if($destinatario?->mail != null)
        {
            Mail::send('emails.notificacion', compact('invoices', 'cantidadVencidas', 'cantidadEnPlazo'), function ($message) use ($destinatario,$cliente,$invoices) {
                foreach($invoices as $invoice)
                {
                    $pdf = Pdf::loadView('invoice.pdf', compact('invoice'));
                    $message->attachData($pdf->output(), 'resumen_de_factura.pdf', ['mime' => 'application/pdf']);
                }
                $message->to('l.e.marguery@gmail.com')
                // ->cc([env('MAIL_CC_ONE'),env('MAIL_CC_TWO')])
                // ->cc(["matiivalin@gmail.com"])
                ->subject($cliente.':notificación de facturas vencidas y a vencer en los proximos dias.')
                ->from(env('MAIL_NOTIFICACION'));
            });
            return true;
        }
        return false;

    }

    public function validarNotificacion()
    {
        return Invoice::with([
                'client',
                'credits',      // notas de crédito (FK invoiceId)
                'debits',       // notas de débito  (FK invoiceId)
                'misrecibos',   // pagos parciales: pivot->total = monto aplicado a la factura
            ])
            ->whereHas('client', fn($q) => $q->whereNotNull('paymentTermDays'))
            ->where('paid', 'NO')
            ->whereBetween(
                DB::raw("DATE_ADD(date, INTERVAL (SELECT paymentTermDays FROM clients WHERE clients.id = invoices.clientId) DAY)"),
                [Carbon::now()->subYears(10), Carbon::now()->addDays(6)]
            )
            ->get()
            ->groupBy('clientId');
    }
}