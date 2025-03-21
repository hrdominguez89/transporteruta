<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <title>Liquidaciones</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 20mm 10mm 20mm 10mm;

            @bottom-center {
                content: "Página " counter(page) " de " counter(pages);
            }
        }

        body {
            font-family: Arial, sans-serif;
        }
    </style>

</head>

<body>
    <div class="text-center">
        <div class="col-12">
            <h5>LIQUIDACION N°{{ $driverSettlement->id }}</h5>
        </div>
        <div class="row ">
            <img class="col-7"
                src="https://media.licdn.com/dms/image/C4D1BAQF9AP8K9M-0WQ/company-background_10000/0/1625358131993/transportes_ruta_s_r_l_cover?e=2147483647&v=beta&t=DMcRvoePh7phfXc3qOGVvqPwkBOIDx37opmL1OcJizM">
        </div>
        <div class="col-12 text-left">
            <p><strong>Periodo:</strong> {{ \Carbon\Carbon::parse($driverSettlement->dateFrom)->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse($driverSettlement->dateTo)->format('d/m/Y') }}</p>
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($driverSettlement->date)->format('d/m/Y') }}</p>
            <p><strong>Chofer:</strong> {{ $driverSettlement->driver->name }}</p>
        </div>
        <div class="col-12 text-left">
            <p><strong>CONSTANCIAS:</strong></p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Numero</th>
                        <th>Cliente</th>
                        <th class="text-right">Importe</th>
                        <th class="text-right">Peajes</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">IVA</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        use App\Models\TravelItem;

                        // Inicializar totales
                        $totalImporte = 0;
                        $totalSubtotal = 0;
                        $totalIVA = 0;
                        $totalPeajes = 0;
                        $totalTotal = 0;
                    @endphp

                    @foreach ($driverSettlement->travelCertificates as $travelCertificate)
                        @php
                            // Sumar valores de cada columna
                            $totalImporte += $travelCertificate->total - $travelCertificate->totalTolls;
                            $totalPeajes += $travelCertificate->totalTolls;
                            $totalSubtotal += $travelCertificate->total; // Parece que subtotal y total son iguales
                            $totalIVA += $travelCertificate->iva;
                            $totalTotal += $travelCertificate->total + $travelCertificate->iva;
                        @endphp

                        <tr>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($travelCertificate->date)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ number_format($travelCertificate->number, 0, ',', '.') }}</td>
                            <td>{{ $travelCertificate->client->name }}</td>
                            <td class="text-right">
                                $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->totalTolls, 2, ',', '.') }}
                            </td>
                            <td class="text-right">
                                $&nbsp;{{ number_format($travelCertificate->totalTolls, 2, ',', '.') }}</td>
                            <td class="text-right">$&nbsp;{{ number_format($travelCertificate->total, 2, ',', '.') }}
                            </td>
                            <td class="text-right">$&nbsp;{{ number_format($travelCertificate->iva, 2, ',', '.') }}
                            </td>
                            <td class="text-right">
                                $&nbsp;{{ number_format($travelCertificate->total + $travelCertificate->iva, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- Fila de totales --}}
                    <tr>
                        <td colspan="3" class="text-right"><strong>Totales:</strong></td>
                        <td class="text-right"><strong>$&nbsp;{{ number_format($totalImporte, 2, ',', '.') }}</strong>
                        </td>
                        <td class="text-right"><strong>$&nbsp;{{ number_format($totalPeajes, 2, ',', '.') }}</strong>
                        </td>
                        <td class="text-right"><strong>$&nbsp;{{ number_format($totalSubtotal, 2, ',', '.') }}</strong>
                        </td>
                        <td class="text-right"><strong>$&nbsp;{{ number_format($totalIVA, 2, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>$&nbsp;{{ number_format($totalTotal, 2, ',', '.') }}</strong>
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>
        <div class="col-12 text-left">
            <p><strong>A FAVOR DEL CHOFER:</strong>
                $&nbsp;{{ number_format($totalImporte - ($totalImporte / 100) * ($driverSettlement->driver->percent ?? 27.36), 2, ',', '.') }}
            </p>
            <p><strong>A FAVOR DE LA AGENCIA: </strong>
                $&nbsp;{{ number_format(($totalImporte / 100) * ($driverSettlement->driver->percent ?? 27.36), 2, ',', '.') }}</p>
            <p><strong>PEAJES: </strong> $&nbsp;{{ number_format($totalPeajes, 2, ',', '.') }}</p>
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
