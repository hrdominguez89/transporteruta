<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <title>Recibos</title>
</head>

<style>
    .container-fluid {
        margin: 0 100px 0 10px;
    }
    .section-box {
        border: 1px solid #000;
        padding: 8px 12px;
        margin-bottom: 1px;
    }
    .header-img { max-width: 100%; height: auto; display: block; margin: 3px 0 3px; }
    td{
        font-size: 12px;
    }
    .aux{
        font-size: 9px;
    }
</style>

<body>

    <?php
        $numberToWords = new \NumberToWords\NumberToWords();
        $transformer = $numberToWords->getNumberTransformer('es');

        $totalPagos = $pagos->sum('total');
        $totalEnLetras = $transformer->toWords((int) round($totalPagos));

        $nroFacturas = $receiptInvoices->map(fn($ri) =>( $ri->invoice->pointOfSale."-" . $ri->invoice->number))->join(' - ');
    ?>
    <div class="container text-center"  id ="div-gral-body"style="position: relative;" >

    <div class="table-bordered" style="border: 1px solid #000;margin-bottom:1px ">
        <p style="
            width: 10px;
            height: 10px;
            border: 2px solid #000;
            font-size: 7px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;">X</p>
        <div>
            <div style="display: inline-block; width: 48%; vertical-align: top; margin-right: 0.5%;">
                <img  class="header-img" src="data:image/png;base64,{{ base64_encode(file_get_contents(resource_path('img/Logo_de_TR.png'))) }}">
                <p style="font-size: 8px;margin-bottom: 1%;">CUIT:30-70908352-5</p>
                <p style="font-size: 8px;margin-bottom: 1%;">Ing. Brutos C.M.: 902-829006-8</p>
                <p style="font-size: 8px;margin-bottom: 1%;">Inicio de actividades: 01-04-05</p>
                <p style="font-size: 8px;margin-bottom: 1%;">Santa Maria de Oro 1020</p>
                <p style="font-size: 8px;margin-bottom: 1%;">B1646AZB San Fernando-PCIA. Bs. As.</p>
                <p style="font-size: 8px;margin-bottom: 1%;">info@transportesruta.com.ar</p>
                <p style="font-size: 8px;margin-bottom: 1%;">Teléfono 4745-1515/4744-7999 / Wpp. 1154033940;</p>
            </div>
            <div style="display: inline-block; width: 48%; vertical-align: top;">
                <div style="font-size:12px;"><strong>RECIBO</strong></div>
                <div style="font-size:10px;"><strong>N°&nbsp;{{ $receipt->number }}</strong></div>
                <p style="font-size:12px;"><strong >Fecha:</strong> {{ \Carbon\Carbon::parse($receipt->date)->format('d/m/Y') }}</p>
                <img  class="header-img" src="data:image/png;base64,{{ base64_encode(file_get_contents(resource_path('img/logo_camion.png'))) }}">
            </div>
        </div>
    </div>

    {{-- CLIENTE --}}
    <div class="section-box">
        <table style="width:100%;">
            <tr>
                <td style="width:60%;font-size:11px;">
                    <strong>Señor(es):</strong> {{ $receipt->client->name }}
                </td>
                <td style="width:40%;font-size:11px;">
                    <strong>C.U.I.T.:</strong> {{ $receipt->client->dni }}
                </td>
            </tr>
            <tr>
                <td style="width:60%;font-size:11px;">
                    <strong>Domicilio:</strong> {{ $receipt->client->address }}
                </td>
                <td style="width:40%;font-size:11px;">
                    <strong>I.V.A:</strong> {{ $receipt->client->ivaType }}
                </td>
            </tr>
        </table>
    </div>

    {{-- CUERPO RECIBO --}}
    <div class="section-box aux text-left" style="font-size:8px;margin-bottom:1%;margin-top:1%">
        <p style="margin-bottom:1%;margin-top:1%">
            <strong>Recibí la suma de pesos</strong>
            {{ ucfirst($totalEnLetras) }}
            &nbsp;($&nbsp;{{ number_format($totalPagos, 2, ',', '.') }})
        </p>
        <p style="margin-bottom:1%;margin-top:1%">
            <strong>En concepto de las factura(s):</strong> {{ $nroFacturas }}
        </p>
        
        <p><strong>En los siguientes valores:</strong></p>
        <table class="table table-sm table-bordered" style="width:100% ">
            <thead>
                <tr>
                    <th class="text-center">Método</th>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Banco</th>
                    <th class="text-center">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pagos as $pago)
                    <tr>
                        <td class="text-center" style="font-size:8px;">{{ $pago->method }}</td>
                        <td class="text-center" style="font-size:8px;">{{ \Carbon\Carbon::parse($pago->acreditation_date ?? $pago->received_date)->format('d/m/Y') }}</td>
                        <td class="text-center" style="font-size:8px;">{{ $pago->banco ?? '-' }}</td>
                        <td class="text-center" style="font-size:8px;">$&nbsp;{{ number_format($pago->total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{-- Retenciones --}}
            @php
                $totalTaxes = 0;
                foreach($receiptInvoices as $ri) {
                    $totalTaxes += $ri->taxAmount;
                }
            @endphp
            <p style="margin-bottom:1%;margin-top:1%">
                <strong>Retenciones</strong> (total $&nbsp;{{ number_format($totalTaxes, 2, ',', '.') }})
            </p>
            <table style="width:100%" class="table table-sm table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="text-center">
                            Impuesto
                        </th>
                        <th class="text-center">
                            Monto
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($receiptInvoices as $receiptInvoice)
                    
                    @foreach ($receiptInvoice->taxes as $rit)
                        <tr>
                            <td class="text-lefth" style="font-size:8px;">
                                {{ $rit->tax->name }}
                            </td>
                            <td class="text-right" style="font-size:8px;">
                                $&nbsp;{{ number_format($rit->taxAmount, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
            
    </div>

    {{-- TOTALES Y FIRMA --}}
    <table style="width:100%; margin-top:8px;border: 1px solid #000">
        <tr>
            <td style="width:30%; vertical-align:bottom; text-align:center;">
                <img style="width:40%; " src="{{ public_path('images/firma_matias.png') }}"><br>
                <div style="margin-top:-40px; font-size:10px; font-weight:bold; font-family:'Impact'; line-height:1; color:#534f4f;">
                    <div>TRANSPORTES RUTA S.R.L.</div>
                    <div>APODERADO</div>
                    <div>C.U.I.T. 30-70908352-6</div>
                </div>
                <div style="margin-top:5px;"><strong>FIRMA</strong></div>
            </td>
            <td style="width:35%; vertical-align:top; ">
                <table class="table table-sm table-bordered" style="width:90%;margin-top:7px;margin-right:5px;">
                    <tr>
                        <th style="font-size:10px;">IMPORTE</th>
                        <td class="text-center"style="font-size:10px;">$&nbsp;{{ number_format($totalPagos, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th style="font-size:10px;">RETENCIONES (total)</th>
                        <td class="text-center"style="font-size:10px;">$&nbsp;{{ number_format($receipt->taxTotal, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th style="font-size:10px;">TOTAL $</th>
                        <td class="text-center"style="font-size:10px;">$&nbsp;{{ number_format($receipt->total + $receipt->taxTotal, 2, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $x_pagina = 750;
            $y_pagina = 570;
            $x_liquidacion = 50;
            $y_liquidacion = 570;
            $size = 10;
            $color = array(0,0,0);
            $pdf->page_text($x_pagina, $y_pagina, "Página {PAGE_NUM} de {PAGE_COUNT}", null, $size, $color);
            $pdf->page_text($x_liquidacion, $y_liquidacion, "Recibo N° {{ $receipt->number }}", null, $size, $color);
        }
    </script>
            </div>

</body>

</html>