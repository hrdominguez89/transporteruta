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
</style>

<body>

    <table>
        <tr>
            <td class="title" style="width: 50%;">
                <table>
                    <tr>
                        <td>
                            <img style="width: 100%;"
                                src="https://media.licdn.com/dms/image/C4D1BAQF9AP8K9M-0WQ/company-background_10000/0/1625358131993/transportes_ruta_s_r_l_cover?e=2147483647&v=beta&t=DMcRvoePh7phfXc3qOGVvqPwkBOIDx37opmL1OcJizM">
                        </td>
                    </tr>
            </td>
            <td style="width: 50%;" class="text-top">
                <table style="width: 100%;">
                    <tr>
                        <th class="text-center" style="width: 90%;font-size:18px">
                            Recibo&nbsp;Nro:<br>{{ $receipt->number }}
                        </th>
                        <td class="pl-2 text-center">
                            <strong>Fecha:</strong><br>{{ \Carbon\Carbon::parse($receipt->date)->format('d/m/Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="pl-2">
                            <strong>Cliente:</strong> {{ $receipt->client->name }}
                        </td>

                        <td class="pl-2 text-center">
                            <strong>DNI/CUIT:</strong><br>{{ $receipt->client->dni }}
                        </td>
                    </tr>
                    <tr>
                        <td class="pl-2">
                            <strong>Domicilio:</strong> {{ $receipt->client->address }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="table table-sm table-bordered table-striped" style="width:100%">
        <thead>
            <tr>
                <th class="text-center" colspan="6">
                    <strong>Recibí la suma en pesos:</strong>
                    $&nbsp;{{ number_format($receipt->total + $receipt->taxTotal, 2, ',', '.') }}
                </th>
            </tr>
            <tr>
                <th class="text-center" colspan="6">
                    En concepto de
                </th>
            </tr>

            <tr>
                <th class="text-center">
                    Factura N°
                </th>
                <th>Medio de pago</th>
                <th class="text-center">
                    Valor
                </th>
                <th class="text-center">
                    Saldo Recibido
                </th>
                <th class="text-center">
                    Retenciones
                </th>
                <th class="text-center">
                    Total
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receiptInvoices as $receiptInvoice)
                <tr>
                    <td class="text-center">
                        {{ $receiptInvoice->invoice->number }}
                    </td>
                    <td class="text-left">
                        {{ $receiptInvoice->paymentMethod->name }}
                    </td>
                    <td class="text-right">
                        $&nbsp;{{ number_format($receiptInvoice->invoice->totalWithIva, 2, ',', '.') }}
                    </td>
                    <td class="text-right">
                        $&nbsp;{{ number_format($receiptInvoice->total, 2, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <table style="width:100%" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">
                                        Total&nbsp;$&nbsp;{{ number_format($receiptInvoice->taxAmount, 2, ',', '.') }}
                                    </th>
                                </tr>
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
                                @foreach ($receiptInvoice->taxes as $rit)
                                    <tr>
                                        <td class="text-lefth">
                                            {{ $rit->tax->name }}
                                        </td>
                                        <td class="text-right">
                                            $&nbsp;{{ number_format($rit->taxAmount, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                    <td class="text-right">
                        $&nbsp;{{ number_format($receiptInvoice->total + $receiptInvoice->taxAmount, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table style="width:100%">


        <tr class="text-right">
            <td rowspan="3" class="text-center">
                <img src="{{ public_path('images/firma_matias.png') }}"><br>
                <div
                    style="margin-top:-40px; font-size: 10px;font-weight: bold;font-family: 'Impact';line-height: 1;color:#534f4f">
                    <div>TRANSPORTES RUTA S.R.L.</div>
                    <div>MATIAS C. VALINOTTI</div>
                    <div>APODERADO</div>
                    <div>C.U.I.T. 30-70908352-6</div>
                </div>
            </td>
            <th>
                IMPORTE:
            </th>
            <th style="width:100px">
                $&nbsp;{{ number_format($receipt->total, 2, ',', '.') }}
            </th>
        </tr>

        <tr class="text-right">

            <th>
                RETENCIONES:
            </th>
            <th style="width:100px">
                $&nbsp;{{ number_format($receipt->taxTotal, 2, ',', '.') }}
            </th>
        </tr>

        <tr class="text-right">
            <th>
                TOTAL:
            </th>
            <th style="width:100px">
                $&nbsp;{{ number_format($receipt->total + $receipt->taxTotal, 2, ',', '.') }}
            </th>
        </tr>
    </table>
    <script type="text/php">
        if (isset($pdf)) {
            $x_pagina = 750;  // Posición en X para el número de página
            $y_pagina = 570;  // Posición en Y para el número de página
    
            $x_liquidacion = 50;  // Posición en X para la info extra
            $y_liquidacion = 570; // Posición en Y para la info extra
    
            $size = 10;
            $color = array(0,0,0); // Color negro
    
            // Texto de la numeración de páginas
            $pdf->page_text($x_pagina, $y_pagina, "Página {PAGE_NUM} de {PAGE_COUNT}", null, $size, $color);
    
            // Texto con número de liquidación y chofer
            $pdf->page_text($x_liquidacion, $y_liquidacion, "Recibo N° {{ $receipt->number }}", null, $size, $color);
        }
    </script>
</body>

</html>
