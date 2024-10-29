<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
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
    </style>
    <title>Reporte General de Deudores</title>
</head>

<body>
    <div class="text-center">
        <img src="vendor/adminlte/dist/img/logo.png" width="100px">
        <h5>Reporte General de Deudores</h5>
        <p>Saldo Total: <strong>{{ $total }}</strong></p>
        <hr>
        @foreach($clients as $client)
        <div class="table-responsive-sm">
            <table class="table table-bordered">
                <thead>
                    <tr class="table-info">
                        <th scope="col">Cliente</th>
                        <th scope="col">DNI/CUIT</th>
                        <th scope="col">SALDO</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->dni }}</td>
                        <td>{{ $client->balance }}</td>
                    </tr>
                </tbody>
            </table>
            <h6>Facturas</h6>
            <table class="table table-bordered">
                <thead>
                    <tr class="table-secondary">
                        <th scope="col">Numero</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Vencimiento</th>
                        <th scope="col">Total</th>
                    </tr>
                </thead>
                <tbody>
                <tbody>
                    @foreach($client->invoices as $invoice)
                    @if($invoice->paid == 'NO')
                    <tr>
                        <td>{{ $invoice->number }}</td>
                        <td>{{ $invoice->date }}</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->date)->addDays(30)->format('Y-m-d') }}</td>
                        <td>{{ $invoice->totalWithIva }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                </tbody>
            </table>
            @endforeach
        </div>
        <p>Listado de cuentas corrientes al {{ $date }}</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>
</html>