<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <title>Reporte General de Deudores</title>
</head>
<body>
    <div class="text-center">
        <img src="vendor/adminlte/dist/img/logo.png" width="100px">
        <h5>Reporte General de Deudores</h5>
        <p>Saldo Total: <strong>{{ $total }}</strong></p>
        <hr>
        @foreach($clients as $client)
            <table class="table text-center">
                <thead>
                    <tr>
                        <td>
                            <p>Cliente</p>
                        </td>
                        <td>
                            <p>DNI/CUIT</p>
                        </td>
                        <td>
                            <p>Saldo</p>
                        </td>
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
                <table class="table table-bordered text-center">
                    <thead>
                        <th>Numero</th>
                        <th>Fecha</th>
                        <th>Total</th>
                    </thead>
                    <tbody>
                            @foreach($client->invoices as $invoice)
                                @if($invoice->paid == 'NO')
                                <tr>
                                    <td>{{ $invoice->id }}</td>
                                    <td>{{ $invoice->date }}</td>
                                    <td>{{ $invoice->totalWithIva }}</td>
                                </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
        @endforeach
        <p>Listado de cuentas corrientes al {{ $date }}</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>
</html>