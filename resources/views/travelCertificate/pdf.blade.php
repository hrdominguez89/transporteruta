<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
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
            <p><strong>Fecha:</strong> {{ $travelCertificate->date }}</p>
            <h5>CONSTANCIA DE VIAJE N°{{ $travelCertificate->number }}</h5>
        </div>
            <div class="row ">
                <img class="col-7" src="https://media.licdn.com/dms/image/C4D1BAQF9AP8K9M-0WQ/company-background_10000/0/1625358131993/transportes_ruta_s_r_l_cover?e=2147483647&v=beta&t=DMcRvoePh7phfXc3qOGVvqPwkBOIDx37opmL1OcJizM">
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
            @foreach($travelCertificate->travelItems as $travelItem)
                <p><span class="text-danger">Tipo:</span> {{ $travelItem->type }}</p>
                <p><span class="text-danger">Total:</span> {{ $travelItem->price }}</p>
            @endforeach            
        </div>
        <div class="col-12 table-bordered text-left">
            <p><strong>TOTAL:</strong> $&nbsp;{{ $travelCertificate->total }}</p>
            <p><strong>PEAJES:</strong> $&nbsp;{{ $totalTolls }}</p>
            <p><strong>IVA:</strong> $&nbsp;{{ $travelCertificate->iva }}</p>
            <p><strong>TOTAL CON IVA:</strong> $&nbsp;{{ $travelCertificate->total + $travelCertificate->iva}}</p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>
</html>