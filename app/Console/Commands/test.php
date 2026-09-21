<?php

namespace App\Console\Commands;

use App\Models\Contacto;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Mail::send('emails.test', [], function ($message) {
            $message->to('l.e.marguery@gmail.com')
                    ->subject('Facturas vencidas y en plazo')
                    ->from(env('MAIL_FROM_ADDRESS'));
        });
        $this->testeodedireccionesdemail();
        return Command::SUCCESS;
    }
    public function testeodedireccionesdemail()
{
    $facturasPorCliente = Invoice::with([
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

    foreach ($facturasPorCliente as $clienteId => $invoices) {
        $cliente = $invoices[0]->client->name;

        $destinatarios = Contacto::where('client_id', $invoices[0]->client->id)
                            ->whereNotNull('mail')
                            ->with('categorias')
                            ->get();

        $invoicesDpto = $invoices->groupBy('dpto_notificacion');

        foreach ($invoicesDpto as $dpto => $invoicesDelDpto) {
            $mailsDelDpto = $destinatarios->filter(
                fn($contacto) => $contacto->categorias->contains('categoria', $dpto)
            )->pluck('mail')->all();

            $facturas = $invoicesDelDpto->pluck('id')->implode(', ');

            echo "Cliente: {$cliente} | Dpto: {$dpto}\n";
            echo "  Facturas: {$facturas}\n";
            echo "  Mails: " . (empty($mailsDelDpto) ? '(ninguno)' : implode(', ', $mailsDelDpto)) . "\n\n";
        }
    }
}

}
