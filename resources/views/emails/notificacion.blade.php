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
            <p>Total: ${{ number_format($invoice->totalWithIva, 2, ',', '.') }}.</p>
            <p>Saldo pendiente: ${{ number_format($invoice->balance, 2, ',', '.') }}.</p>

            @if ($invoice->credits->count())
                <i>Notas de crédito:</i>
                @foreach ($invoice->credits as $credit)
                <i>&nbsp;&nbsp;N° {{ $credit->number }},Punto de venta: {{ $invoice->point_of_sale }},Fecha de emisión: {{ $credit->date }},</i>
                <i>&nbsp;&nbsp;Total: ${{ number_format($credit->total, 2, ',', '.') }}</i>
                {{-- <i>&nbsp;&nbsp;Punto de venta: {{ $credit->point_of_sale }}</i> FALTA AGREGAR AL SISTEMA --}}
                @endforeach
            @endif

            @if ($invoice->debits->count())
                <i>Notas de débito:</i>
                @foreach ($invoice->debits as $debit)
                <i>&nbsp;&nbsp;N° {{ $debit->number }}, Punto de venta: {{ $invoice->point_of_sale }} ,Fecha de emisión: {{ $credit->date }},</i>
                <i>&nbsp;&nbsp;Total: ${{ number_format($debit->balance, 2, ',', '.') }}</i>
                @endforeach
            @endif

            @if ($invoice->misrecibos->count())
                <i>Pagos parciales:</i>
                @foreach ($invoice->misrecibos as $recibo)
                <i>&nbsp;&nbsp;Recibo #{{ $recibo->id }} ({{ \Carbon\Carbon::parse($recibo->date)->format('d/m/Y') }}) — aplicado a esta factura: ${{ number_format($recibo->pivot->total, 2, ',', '.') }}</i>
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