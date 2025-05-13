<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <title>Constancias</title>
</head>
<style>
    .container {
        border: 1px solid black;
        padding: 65px;
    }
</style>

<body>
    <div class="container text-center">
        <div class="col-12">
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($travelCertificate->date)->format('d/m/Y') }}</p>
            <h5>CONSTANCIA DE VIAJE N° {{ number_format($travelCertificate->number, 0, ',', '.') }}</h5>
        </div>
        <div class="row ">
            <img class="col-7"
                src="https://media.licdn.com/dms/image/C4D1BAQF9AP8K9M-0WQ/company-background_10000/0/1625358131993/transportes_ruta_s_r_l_cover?e=2147483647&v=beta&t=DMcRvoePh7phfXc3qOGVvqPwkBOIDx37opmL1OcJizM">
        </div>


        <div class="col-12 table-bordered text-left">
            <p><strong>Cliente:</strong> {{ $travelCertificate->client->name }}</p>
            <p><strong>Chofer:</strong> {{ $travelCertificate->driver->name }}</p>
            <p><strong>Vehiculo:</strong> {{ $travelCertificate->driver->vehicle->name }}</p>
            <p><strong>Hora de Salida:</strong></p>
            <p><strong>Hora de Llegada:</strong></p>
        </div>
        <div class="col-12 table-bordered text-left">
            <p><strong>CONCEPTOS:</strong></p>
            @foreach ($travelCertificate->travelItems as $travelItem)
                <p><span class="text-danger">Tipo:</span> {{ $travelItem->type }}</p>
                <p><span class="text-danger">Total:</span> {{ $travelItem->price }}</p>
            @endforeach
        </div>
        <div class="col-12 table-bordered text-left">
            <p><strong>TOTAL:</strong> $&nbsp;{{ number_format($travelCertificate->total, 2, ',', '.') }}</p>
            <p><strong>PEAJES:</strong> $&nbsp;{{ number_format($totalTolls, 2, ',', '.') }}</p>
            <p><strong>IVA:</strong> $&nbsp;{{ number_format($travelCertificate->iva, 2, ',', '.') }}</p>
            <p><strong>TOTAL CON IVA:</strong>
                $&nbsp;{{ number_format($travelCertificate->total + $travelCertificate->iva, 2, ',', '.') }}</p>
        </div>
    </div>
</body>

</html>
