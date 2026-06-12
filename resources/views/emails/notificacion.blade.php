<p>Buenos días, ¿qué tal?, esperamos que se encuentren muy bien.
Les comentamos que contamos con {{ $cantidadVencidas }} facturas que se encuentran vencidas y {{ $cantidadEnPlazo }} facturas en plazo. Si pudieran cancelarlas a la fecha de vencimiento de cada una, según sus posibilidades, lo agradeceríamos mucho.
A continuación, adjuntamos el detalle, según nuestra cuenta corriente actualizada al día de la fecha:</p>

@foreach ($invoices as $invoice)
    @if ($invoice->date && $invoice->client)
        @php
            $vence   = $invoice->date->copy()->addDays($invoice->client->paymentTermDays);
            $vencida = $vence->isPast();
            $enPlazo = !$vencida && $vence < now()->addDays(6);
        @endphp

        @if ($vencida || $enPlazo)
            <hr>
            <p><strong>{{ $vencida ? 'Vencida' : 'En plazo' }}</strong></p>
            <p>Factura N° {{ $invoice->number }}, punto de venta: {{ $invoice->point_of_sale }}, fecha de emisión: {{ $invoice->date->format('d/m/Y') }}.</p>
            <p>Total con IVA: ${{ number_format($invoice->totalWithIva, 2, ',', '.') }}.</p>
            <p>Falta pagar (saldo): ${{ number_format($invoice->balance, 2, ',', '.') }}.</p>

            @if ($invoice->credits->count())
                <p>Notas de crédito:</p>
                @foreach ($invoice->credits as $credit)
                    <p>&nbsp;&nbsp;N° {{ $credit->number }} — ${{ number_format($credit->total, 2, ',', '.') }}</p>
                @endforeach
            @endif

            @if ($invoice->debits->count())
                <p>Notas de débito:</p>
                @foreach ($invoice->debits as $debit)
                    <p>&nbsp;&nbsp;N° {{ $debit->number }} — ${{ number_format($debit->total, 2, ',', '.') }}</p>
                @endforeach
            @endif

            @if ($invoice->misrecibos->count())
                <p>Pagos parciales:</p>
                @foreach ($invoice->misrecibos as $recibo)
                    <p>&nbsp;&nbsp;Recibo #{{ $recibo->id }} ({{ \Carbon\Carbon::parse($recibo->date)->format('d/m/Y') }}) — aplicado a esta factura: ${{ number_format($recibo->pivot->total, 2, ',', '.') }}</p>
                @endforeach
            @endif
        @endif
    @endif
@endforeach

<hr>
<p>Aguardamos comentarios, desde ya muchas gracias.</p>
<p>Saludos!</p>
<p>Milagros Sofía Valinotti</p>
<p>Dept. pagos, cobros y finanzas</p>
<img style="width: 300px; height: auto;" src="{{ $message->embed(resource_path('img/transportes_ruta_s_r_l_cover.jpg')) }}">