<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style>
        /* Control del espaciado entre líneas */
        body,
        p {
            line-height: 1.2;
        }

        /* Reducción de márgenes entre elementos */
        h5,
        h6 {
            margin-top: 5px;
            margin-bottom: 5px;
        }

        p {
            margin-top: 3px;
            margin-bottom: 3px;
        }

        table {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        /* Ajuste del padding en celdas de tabla */
        td,
        th {
            padding: 5px !important;
        }

        .page-break {
            page-break-after: always;
        }

        tr:nth-child(even) {
            background-color: #e9ecef;
            /* Gris un poco más oscuro */
        }
    </style>
    <title>Reporte General de Deudores</title>
</head>

<body>
    <div class="text-center">
        <img src="vendor/adminlte/dist/img/logo.png" width="100px">
        <h5>Reporte General de Deudores</h5>
        <p>Saldo Total: <strong>$&nbsp;{{ number_format($total, 2, ',', '.') }}</strong></p>
        <hr>
        @foreach ($clients as $client)
            <div class="table-responsive-sm">
                <table class="table table-bordered"
                    style="border-radius:5px; border: 2px solid #dc3546; margin-bottom: 50px;">
                    <tr style="background-color: #dc3546; color: white;">
                        <th colspan="2" class="text-center">Cliente</th>
                        <th class="text-center">DNI/CUIT</th>
                        <th class="text-center">Saldo</th>
                    </tr>

                    <tr>
                        <th colspan="2" class="text-center">{{ $client->name }}</th>
                        <th class="text-center">{{ $client->dni }}</th>
                        <th class="text-right">$&nbsp;{{ number_format( $saldos[$client->id], 2, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" style="width: 100%;" class="text-center">
                            Facturas
                        </th>
                    </tr>

                    <tr style="background-color: #dc3546; color: white;">
                        <th class="text-center" style="width:20%">Número</th>
                        <th class="text-center" style="width:20%">Fecha</th>
                        <th class="text-center" style="width:20%">Vencimiento</th>
                        <th class="text-center"style="width:40%">Total</th>
                    </tr>

                    @foreach ($client->invoices as $invoice)
                        @if ($invoice->paid == 'NO')
                            <tr>
                                <td class="text-center">
                                    {{ number_format($invoice->number, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($invoice->date)->addDays(15)->format('d/m/Y') }}
                                </td>
                                <td class="text-right">
                                    $&nbsp;{{ number_format($invoice->totalWithIva, 2, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>
        @endforeach
    </div>
    <script type="text/php">
        if (isset($pdf)) {
            $pagina_x = 500;
            $pagina_y = 810;

            $texto_x = 50;
            $texto_y = 810;
            $pdf->page_text($texto_x, $texto_y, "Listado de cuentas corrientes al {{ \Carbon\Carbon::parse($date)->format('d/m/Y H:i:s') }}", null, 10, [0, 0, 0]);
            $pdf->page_text($pagina_x, $pagina_y, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 10, [0, 0, 0]);
        }
    </script>
</body>

</html>
