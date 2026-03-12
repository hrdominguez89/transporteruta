<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Facturas</title>

    <style>
        .text-top {
            vertical-align: top;
        }

        .invoice-box {
            padding: 10px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-family: Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            text-align: left;
        }

        .invoice-box table title td {
            padding: 5px;
            vertical-align: top;
        }

        .information-empresa table th {
            font-size: 10px;
        }

        .information-cliente table th,
        .information-cliente table td {
            font-size: 12px;
            line-height: 12px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .conceptos td {
            font-size: 10px
        }

        .conceptos tbody tr:nth-child(even) {
            background-color: #e9ecef;
        }

        .totales tr:nth-child(even) {
            background-color: #FFFFFF;
        }

        .conceptos thead {
            background-color: #dc3546;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }

        body {
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        {{-- CABECERA --}}

        <table>
            <tr>
                <td class="title" style="width: 50%;">
                    <table>
                        <tr>
                            <td>
                                <img style="width: 100%;"  class="header-img" src="data:image/png;base64,{{ base64_encode(file_get_contents(resource_path('img/transportes_ruta_s_r_l_cover.jpg'))) }}">
                            </td>
                        </tr>
                        <tr class="information-empresa">
                            <td colspan="2">
                                <table>
                                    <tr>
                                        <th class="text-center" style="padding:0;margin:0;line-height:14px;">
                                            TRANSPORTES RUTA S.R.L.
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" style="padding:0;margin:0;line-height:14px;">
                                            30-70908352-6
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" style="padding:0;margin:0;line-height:14px;">
                                            Fray Justo Sta. María de Oro 1020
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" style="padding:0;margin:0;line-height:14px;">
                                            San Fernando - Buenos Aires
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" style="padding:0;margin:0;line-height:14px;">
                                            I.V.A. Responsable Inscripto
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" style="padding:0;margin:0;line-height:14px;">
                                            Inicio de actividades: 01/11/2004
                                        </th>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                </td>

                <td style="width: 50%;" class="text-top">
                    <table style="width: 100%;">
                        <tr>
                            <th class="text-right" style="width: 90%;font-size:18px">
                                <strong>Resumen de factura</strong>
                            </th>
                            <td style="width: 90%;font-size:17px;text-align:left">
                               <strong>{{ sprintf('%05d', $invoice->pointOfSale) }}-{{  sprintf('%08d',$invoice->number) }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-right" style="width: 90%;">
                                Fecha:
                            </th>
                            <td style="width:10%;">
                                {{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <hr>

        {{-- DATA CLIENTE --}}
        <table>
            <tr class="information-cliente">
                <table>
                    <tr>
                        <th style="text-align:left">
                            Apellido y Nombre / Razón Social:
                        </th>
                        <td style="text-align:left">
                            {{ $invoice->client->name }}
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align:left">
                            DNI/CUIT:
                        </th>
                        <td style="text-align:left">
                            {{ $invoice->client->dni }}
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align:left">
                            Domicilio:
                        </th>
                        <td style="text-align:left">
                            {{ $invoice->client->address }}
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align:left">
                            Condición frente al IVA:
                        </th>
                        <td style="text-align:left">
                            {{ $invoice->client->ivaType }}
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align:left">
                            Referencia:
                        </th>
                        <td style="text-align:left">
                            {{ $invoice->reference ? $invoice->reference :'-' }}
                        </td>
                    </tr>
                </table>
            </tr>
        </table>

        {{-- ===================== REFACT (totales de factura vía modelo) =====================
             Calculamos por suma de constancias para que apliquen:
             - Descuentos (monto y %) sobre lo gravado (sin peajes)
             - Adicionales % sobre FIJO
             - Peajes por separado
             Fórmula por constancia:
               NETO = (subtotal_sin_peajes - descuento_aplicable) + monto_adicional
               IVA  = iva_calculado (0 si cliente EXENTO)
               PEAJ = total_peajes
               TOTAL = NETO + IVA + PEAJ
        ---------------------------------------------------------------------------- --}}
        @php
            $items = $invoice->travelCertificates ?? collect();

            // Detectar condición IVA del cliente (acepta ivaType / ivaCondition)
            $condIva  = strtoupper($invoice->client->ivaType ?? $invoice->client->ivaCondition ?? $invoice->client->iva_condition ?? '');
            $esExento = strpos($condIva, 'EXENTO') !== false;

            // Totales por factura (sumando constancias)
            $totalNeto   = (float) $items->sum(fn ($tc) => (($tc->subtotal_sin_peajes - $tc->descuento_aplicable) + $tc->monto_adicional));
            $totalIva    = (float) $items->sum(fn ($tc) => $esExento ? 0 : $tc->iva_calculado);
            $totalPeajes = (float) $items->sum(fn ($tc) => $tc->total_peajes);
            $totalConIva = $totalNeto + $totalIva + $totalPeajes;
            $estacionamiento =(float) $items->sum(fn ($tc) => $tc->total_estacionamiento);
        @endphp
        <hr>
        <table class="conceptos">
            <thead>
                <tr class="heading" style="font-size:10px;">
                    <td style="text-align:center;width:10%">Nro<br>Nuevo/Antiguo</td>
                    <td style="text-align:center;width:10%">Fecha</td>
                    <td style="text-align:center;width:50%">Servicios</td>
                    <td style="text-align:center;width:30%">Importe Neto</td>
                    <td style="text-align:center;width:30%">I.V.A.</td>
                    <td style="text-align:center;width:30%">Peajes</td>
                    @if ($estacionamiento > 0)
                    <td style="text-align:center;width:30%">Estacionamientos</td>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->travelCertificates as $travelCertificate)
                    @php
                        $neto   = (($travelCertificate->subtotal_sin_peajes - $travelCertificate->descuento_aplicable) + $travelCertificate->monto_adicional);
                        $iva    = $esExento ? 0 : $travelCertificate->iva_calculado;
                        $peajes = $travelCertificate->total_peajes;
                    @endphp
                    <tr style="font-size:14px;">
                        <td style="padding: 2px 8px;text-align:center">
                            {{ number_format($travelCertificate->id, 0, ',', '.') }} /
                            {{ $travelCertificate->number ? number_format($travelCertificate->number, 0, ',', '.') : ' - ' }}
                        </td>
                        <td style="padding: 2px 8px;text-align:center">
                            {{ \Carbon\Carbon::parse($travelCertificate->date)->format('d/m/Y') }}
                        </td>
                        <td style="padding: 2px 8px;text-align:left">{{ $travelCertificate->destiny }}</td>

                        <td style="padding: 2px 8px;text-align:right">
                            $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->total_peajes - $travelCertificate->total_estacionamiento , 2, ',', '.') }}
                        </td>
                        <td style="padding: 2px 8px;text-align:right">
                            $&nbsp;{{ number_format($travelCertificate->iva, 2, ',', '.') }}
                        </td>
                        <td style="padding: 2px 8px;text-align:right">
                            $&nbsp;{{ number_format($travelCertificate->total_peajes, 2, ',', '.') }}
                        </td>
                        @if ($estacionamiento > 0)
                            <td style="padding: 2px 8px;text-align:right">
                                $&nbsp;{{ number_format($travelCertificate->total_estacionamiento, 2, ',', '.') }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <hr>
        <table>
            <tr style="background-color:#FFFFFF">
                <th colspan="2" style="width:10%;padding: 2px 8px;text-align:right">
                    Subtotal
                </th>
                <th style="padding: 2px 8px;text-align:right">
                    $&nbsp;{{ number_format($totalNeto  , 2, ',', '.') }}
                </th>
            </tr>
            
            <tr style="background-color:#FFFFFF">
                <th colspan="2" style="width:10%;padding: 2px 8px;text-align:right">
                    I.V.A.
                </th>
                <th style="padding: 2px 8px;text-align:right">
                    $&nbsp;{{ number_format($totalIva, 2, ',', '.') }}
                </th>
            </tr>
            <tr style="background-color:#FFFFFF">
                <th colspan="2" style="width:10%;padding: 2px 8px;text-align:right">
                    Peajes
                </th>
                <th style="padding: 2px 8px;text-align:right">
                    $&nbsp;{{ number_format($totalPeajes, 2, ',', '.') }}
                </th>
            </tr>
            @if ($estacionamiento > 0)
                <tr style="background-color:#FFFFFF">
                    <th colspan="2" style="width:10%;padding: 2px 8px;text-align:right">
                        Estacionamientos
                    </th>
                    <th style="padding: 2px 8px;text-align:right">
                        $&nbsp;{{ number_format($estacionamiento, 2, ',', '.') }}
                    </th>
                </tr>
            @endif
            <tr style="background-color:#FFFFFF">
                <th style="width:90%;text-align:center">
                    ___________________________________<br>
                    FIRMA
                </th>
                <th style="width:10%;padding: 2px 8px;text-align:right">
                    Total
                </th>
                <th style="padding: 2px 8px;text-align:right">
                      $&nbsp;{{ number_format($invoice->getTotalWithIvaAttribute(), 2, ',', '.') }}
                </th>
            </tr>
        </table>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $x_pagina = 500;  // Posición en X para el número de página
            $y_pagina = 810;  // Posición en Y para el número de página

            $x_factura = 50;  // Posición en X para la info extra
            $y_factura = 810; // Posición en Y para la info extra

            $size = 10;
            $color = array(0,0,0); // Color negro

            // Texto de la numeración de páginas
            $pdf->page_text($x_pagina, $y_pagina, "Página {PAGE_NUM} de {PAGE_COUNT}", null, $size, $color);

            // Texto con número de liquidación y cliente
            $pdf->page_text($x_factura, $y_factura, "Factura N° {{ number_format($invoice->number,0,',','.') }} - Cliente: {{ $invoice->client->name }}", null, $size, $color);
        }
    </script>
</body>

</html>

