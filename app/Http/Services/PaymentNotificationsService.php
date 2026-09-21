<?php
namespace App\Http\Services;

use App\Http\Controllers\TravelCertificateController;
use App\Models\Contacto;
use App\Models\Credit;
use App\Models\Debit;
use App\Models\Invoice;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
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

        // destinatarios cargados UNA sola vez, con sus categorias
        $destinatarios = Contacto::where('client_id', $invoices[0]->client->id)
                            ->whereNotNull('mail')
                            ->with('categorias')
                            ->get();

        if ($destinatarios->isEmpty()) {
            return false;
        }

        $TC = new TravelCertificateController();
        $invoicesDpto = $invoices->groupBy('dpto_notificacion');

        foreach ($invoicesDpto as $dpto => $invoicesDelDpto) {
            $mailsDelDpto = $destinatarios->filter(
                fn($contacto) => $contacto->categorias->contains('categoria', $dpto)
            )->pluck('mail')->all();

            if (empty($mailsDelDpto)) {
                continue;
            }
            try 
            {
                Mail::send(
                    'emails.notificacion',
                    [
                    'invoices' => $invoicesDelDpto,
                    'cantidadVencidas' => $cantidadVencidas,
                    'cantidadEnPlazo' => $cantidadEnPlazo,
                    ],
                        function ($message) use ($mailsDelDpto, $cliente, $invoicesDelDpto, $TC) {
                            foreach ($invoicesDelDpto as $invoice) {
                                $tcPDFs = [];
                                $invoiceHtml = view('invoice.pdf', ['invoice' => $invoice])->render();
                                foreach ($invoice->travelCertificates as $travelCertificate) {
                                    $tcPDFs[] = $TC->generateTravelCertificatePdf($travelCertificate->id, true);
                                    }
                                    $allHtml = $invoiceHtml . implode($tcPDFs);
                                    $pdf = Pdf::loadHTML($allHtml);
                                    $message->attachData($pdf->output(), 'resumen_factura_' . $invoice->id . '.pdf', ['mime' => 'application/pdf']);
                                    }
                                    $out = $pdf->output();
                                    $message->attachData($out, 'resumen_factura_' . $invoice->id . '.pdf', ['mime' => 'application/pdf']);
                                    
                                    $message->to($mailsDelDpto)
                                    ->cc([env('MAIL_CC_ONE'), env('MAIL_CC_TWO'), env('MAIL_CC_THREE')])
                                    ->subject('Facturas vencidas y en plazo - ' . $cliente)
                                    ->from(env('MAIL_FROM_ADDRESS'));
                                    }
                );
            }
            catch(Exception $e)
            {
                \Illuminate\Support\Facades\Log::error(
                    "Fallo el envío de mail -> cliente: {$cliente}/ dpto: {$dpto} - {$e->getMessage()}"
                );
                continue;
            }
        }

        return true;
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