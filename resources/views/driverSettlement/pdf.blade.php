<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        {{ file_get_contents(public_path('bootstrap/css/bootstrap.min.css')) }}
    </style>
    <title>Liquidaciones</title>
    <style>
        .table-cabecera {
            width: 100%;
            border-radius: 8px;
        }

        .table-cabecera th,
        .table-cabecera td {
            padding: 0px 12px;
            border: 1px solid #ccc;
        }

        .table-resumen {
            /* width: 100%; */
            border-radius: 8px;
        }

        .table-resumen th,
        .table-resumen td {
            padding: 0px 12px;
            border: 1px solid #ccc;
        }

        .table-resumen tr:nth-child(even) {
            background-color: #e9ecef;
        }

        .table-constancias {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }

        .table-constancias th,
        .table-constancias td {
            font-size: 10px;
            padding: 5px;
            border: 1px solid #ccc;
        }

        .table-constancias thead {
            background-color: #dc3546;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }

        .table-constancias tbody tr:nth-child(even) {
            background-color: #e9ecef;
            /* Gris un poco más oscuro */
        }


        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }


        body {
            font-family: Arial, sans-serif;
        }
    </style>

</head>

<body>
    <div class="container">
        <div class="row">
            {{-- CABECERA --}}
            <table class="table-cabecera" style="width:100%; border: 1px solid #000;">
                <tr>

                    <td style=" width:40%; padding:10px">
                        <img style="width: 100%;"
                            src="https://media.licdn.com/dms/image/C4D1BAQF9AP8K9M-0WQ/company-background_10000/0/1625358131993/transportes_ruta_s_r_l_cover?e=2147483647&v=beta&t=DMcRvoePh7phfXc3qOGVvqPwkBOIDx37opmL1OcJizM">
                    </td>
                    <td width="20%">
                        <table class="text-center" width="100%">
                            <tr style="background-color:#f8f9fa;">
                                <th colspan="2" class="text-center" style="font-size:20px">
                                    Liq
                                </th>
                            </tr>
                            <tr style="background-color:#e9ecef;">
                                <td style="font-size:12px;">Nro. Nuevo
                                </td>
                                <td style="font-size:12px;">Nro. Antiguo
                                </td>
                            </tr>
                            <tr style="background-color:#f8f9fa;">
                                <td style="font-size:30px;">{{ number_format($driverSettlement->id, 0, ',', '.') }}
                                </td>
                                <td style="font-size:30px;">
                                    {{ $driverSettlement->number ? number_format($driverSettlement->number, 0, ',', '.') : ' - ' }}
                                </td>
                            </tr>
                        </table>
                    </td>r
                    <td class="40%">
                        <table style="width:100%;font-size:16px;">
                            <tr style="background-color:#f8f9fa;">
                                <th class="text-right">
                                    Fecha:&nbsp;
                                </th>
                                <td class="text-lefth">
                                    {{ \Carbon\Carbon::parse($driverSettlement->date)->format('d/m/Y') }}
                                </td>

                            </tr>
                            <tr style="background-color:#e9ecef;">
                                <th class="text-right">
                                    Chofer:&nbsp;
                                </th>
                                <td class="text-lefth">
                                    {{ $driverSettlement->driver->name }}
                                </td>

                            </tr>
                            <tr style="background-color:#f8f9fa;">
                                <th class="text-right">
                                    Período:&nbsp;
                                </th>
                                <td class="text-lefth">
                                    {{ \Carbon\Carbon::parse($driverSettlement->dateFrom)->format('d/m/Y') }} -
                                    {{ \Carbon\Carbon::parse($driverSettlement->dateTo)->format('d/m/Y') }}
                                </td>

                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table style="width:100%">
                <tr>
                    <th style="font-size:20px;padding: 8px 0" class="text-center">
                        Constancias de viajes
                    </th>
                </tr>

            </table>
            <table class="table-constancias" style="width:100%; font-size: 12px;">
                <thead>
                    <tr>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Nro<br>Nuevo</th>
                        <th class="text-center">Nro<br>Antiguo</th>
                        <th class="text-center">Cliente</th>
                        <th class="text-center">Importe<br>Neto</th>
                        <th class="text-center">I.V.A.</th>
                        <th class="text-center">Subtotal</th>
                        <th class="text-center">Peajes</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">% ó $<br>acordado</th>
                        <th class="text-center">A favor<br>del chofer</th>
                        <th class="text-center">% I.V.A.<br>Chofer</th>
                        <th class="text-center">A favor de<br>la empresa</th>

                    </tr>
                </thead>
                <tbody>
                    @php
                        use App\Models\TravelItem;

                        // Inicializar totales
                        $totalImporte = 0;
                        $totalIVA = 0;
                        $totalSubtotal = 0;
                        $totalPeajes = 0;
                        $totalTotal = 0;
                        $totalAFavorDelChofer = 0;
                        $totalIvaChofer = 0;
                        $totalAFavorDeLaEmpresa = 0;
                    @endphp

                    @foreach ($driverSettlement->travelCertificates as $travelCertificate)
                        @php
                            // Sumar valores de cada columna
                            $totalImporte += $travelCertificate->total - $travelCertificate->totalTolls;
                            $totalPeajes += $travelCertificate->totalTolls;
                            $totalSubtotal +=
                                $travelCertificate->total - $travelCertificate->totalTolls + $travelCertificate->iva;
                            $totalIVA += $travelCertificate->iva;
                            $totalTotal += $travelCertificate->total + $travelCertificate->iva;

                            if ($travelCertificate->commission_type == 'porcentaje') {
                                $totalAFavorDelChofer +=
                                    $travelCertificate->total -
                                    $travelCertificate->totalTolls -
                                    (($travelCertificate->total - $travelCertificate->totalTolls) / 100) *
                                        $travelCertificate->percent;
                                $totalIvaChofer +=
                                    (($travelCertificate->total -
                                        $travelCertificate->totalTolls -
                                        (($travelCertificate->total - $travelCertificate->totalTolls) / 100) *
                                            $travelCertificate->percent) /
                                        100) *
                                    21;
                                $totalAFavorDeLaEmpresa +=
                                    (($travelCertificate->total - $travelCertificate->totalTolls) / 100) *
                                    $travelCertificate->percent;
                            } else {
                                $totalAFavorDelChofer +=
                                    $travelCertificate->total -
                                    $travelCertificate->totalTolls -
                                    $travelCertificate->fixed_amount;
                                $totalIvaChofer +=
                                    (($travelCertificate->total -
                                        $travelCertificate->totalTolls -
                                        $travelCertificate->fixed_amount) /
                                        100) *
                                    21;
                                $totalAFavorDeLaEmpresa += $travelCertificate->fixed_amount;
                            }
                        @endphp

                        <tr>
                            {{-- FECHA --}}
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($travelCertificate->date)->format('d/m/Y') }}</td>
                            {{-- NUMERO NUEVO --}}
                            <td class="text-center">{{ number_format($travelCertificate->id, 0, ',', '.') }}
                            </td>
                            {{-- NUMERO ANTIGUO --}}
                            <td class="text-center">
                                {{ $travelCertificate->number ? number_format($travelCertificate->number, 0, ',', '.') : ' - ' }}
                            </td>
                            {{-- CLIENTE --}}
                            <td class="text-left">{{ $travelCertificate->client->name }}</td>
                            {{-- IMPORTE NETO --}}
                            <td class="text-right">
                                $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->totalTolls, 2, ',', '.') }}
                            </td>
                            {{-- IVA --}}
                            <td class="text-right">$&nbsp;{{ number_format($travelCertificate->iva, 2, ',', '.') }}
                            </td>
                            {{-- SUBTOTAL (IMPORTE NETO + IVA) --}}
                            <td class="text-right">
                                $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->totalTolls + $travelCertificate->iva, 2, ',', '.') }}
                            </td>
                            {{-- PEAJES --}}
                            <td class="text-right">
                                $&nbsp;{{ number_format($travelCertificate->totalTolls, 2, ',', '.') }}</td>
                            {{-- NETO + IVA + PEAJE = TOTAL --}}
                            <td class="text-right">
                                $&nbsp;{{ number_format($travelCertificate->total + $travelCertificate->iva, 2, ',', '.') }}
                            </td>
                            {{-- % o monto acordado --}}
                            <td class="text-right">
                                {{ $travelCertificate->commission_type == 'porcentaje' ? $travelCertificate->percent . ' %' : '$ ' . number_format($travelCertificate->fixed_amount, 2, ',', '.') }}
                            </td>
                            {{-- A FAVOR DEL CHOFER (IMPORTE NETO MENOS EL % QUE SE QUEDA LA EMPRESA DE COMISION) --}}
                            <td class="text-right">
                                $&nbsp;
                                @if ($travelCertificate->commission_type == 'porcentaje')
                                    {{ number_format($travelCertificate->total - $travelCertificate->totalTolls - (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent, 2, ',', '.') }}
                                @else
                                    {{ number_format($travelCertificate->total - $travelCertificate->totalTolls - $travelCertificate->fixed_amount, 2, ',', '.') }}
                                @endif
                            </td>
                            {{-- % IVA DE chofer --}}
                            <td class="text-right">
                                $&nbsp;
                                @if ($travelCertificate->commission_type == 'porcentaje')
                                    {{ number_format(
                                        (($travelCertificate->total -
                                            $travelCertificate->totalTolls -
                                            (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent) /
                                            100) *
                                            21,
                                        2,
                                        ',',
                                        '.',
                                    ) }}
                                @else
                                    {{ number_format(
                                        (($travelCertificate->total - $travelCertificate->totalTolls - $travelCertificate->fixed_amount) / 100) * 21,
                                        2,
                                        ',',
                                        '.',
                                    ) }}
                                @endif
                            </td>
                            {{-- A favor de la empresa --}}
                            <td class="text-right">
                                $&nbsp;
                                @if ($travelCertificate->commission_type == 'porcentaje')
                                    {{ number_format((($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent, 2, ',', '.') }}
                                @else
                                    {{ number_format($travelCertificate->fixed_amount, 2, ',', '.') }}
                                @endif

                            </td>
                        </tr>
                    @endforeach

                    {{-- Fila de totales --}}
                    <tr>

                        <td colspan="3" class="text-right"><strong>Totales:</strong></td>
                        {{-- totales importe neto --}}
                        <td class="text-right"><strong>$&nbsp;{{ number_format($totalImporte, 2, ',', '.') }}</strong>
                        </td>
                        {{-- totales importe iva --}}
                        <td class="text-right"><strong>$&nbsp;{{ number_format($totalIVA, 2, ',', '.') }}</strong>
                        </td>
                        {{-- totales subtotal --}}
                        <td class="text-right">
                            <strong>$&nbsp;{{ number_format($totalSubtotal, 2, ',', '.') }}</strong>
                        </td>
                        {{-- totales peajes --}}
                        <td class="text-right"><strong>$&nbsp;{{ number_format($totalPeajes, 2, ',', '.') }}</strong>
                        </td>
                        {{-- totales total importe neto + iva + peaje --}}
                        <td class="text-right"><strong>$&nbsp;{{ number_format($totalTotal, 2, ',', '.') }}</strong>
                        </td>
                        {{-- % o monto acordado --}}
                        <td class="text-right">
                        </td>
                        {{-- totales a favor del chofer --}}
                        <td class="text-right">
                            <strong>$&nbsp;{{ number_format($totalAFavorDelChofer, 2, ',', '.') }}</strong>
                        </td>
                        {{-- totales iva del chofer --}}
                        <td class="text-right">
                            <strong>$&nbsp;{{ number_format($totalIvaChofer, 2, ',', '.') }}</strong>
                        </td>
                        {{-- totales a favor de la empresa --}}
                        <td class="text-right">
                            <strong>$&nbsp;{{ number_format($totalAFavorDeLaEmpresa, 2, ',', '.') }}</strong>
                        </td>
                    </tr>
                </tbody>

            </table>

            <table class="table-resumen" style="margin-top:20px;">
                <tr style="background-color:#dc3546;color:#FFFFFF" class="text-center">
                    <th colspan="2" class="text-center">
                        Resumen
                    </th>
                </tr>

                <tr>
                    <th>
                        A favor del chofer
                    </th>
                    <td class="text-right">
                        $ {{ number_format($totalAFavorDelChofer, 2, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <th>
                        I.V.A. a favor del chofer
                    </th>
                    <td class="text-right">
                        $ {{ number_format($totalIvaChofer, 2, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <th>
                        Peajes
                    </th>
                    <td class="text-right">
                        $ {{ number_format($totalPeajes, 2, ',', '.') }}
                    </td>
                </tr>
                <tr style="background-color:#dc3546;color:#FFFFFF">
                    <th>
                        Total a facturar
                    </th>
                    <th class="text-right">
                        $ {{ number_format($totalPeajes + $totalAFavorDelChofer + $totalIvaChofer, 2, ',', '.') }}
                    </th>
                </tr>
                <tr>
                    <th>
                        A favor de la empresa
                    </th>
                    <th class="text-right">
                        $ {{ number_format($totalAFavorDeLaEmpresa, 2, ',', '.') }}
                    </th>
                </tr>
            </table>
        </div>
    </div>

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
            $pdf->page_text($x_liquidacion, $y_liquidacion, "Liquidación N° {{ $driverSettlement->id }} - Chofer: {{ $driverSettlement->driver->name }}", null, $size, $color);
        }
    </script>
</body>

</html>
