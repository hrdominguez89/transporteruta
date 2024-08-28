<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <title>Liquidaciones</title>
</head>
<body>
    <div class="text-center">
        <div class="col-12">
            <h5>LIQUIDACION N°{{ $driverSettlement->id }}</h5>
        </div>
            <div class="row ">
                <img class="col-7" src="https://media.licdn.com/dms/image/C4D1BAQF9AP8K9M-0WQ/company-background_10000/0/1625358131993/transportes_ruta_s_r_l_cover?e=2147483647&v=beta&t=DMcRvoePh7phfXc3qOGVvqPwkBOIDx37opmL1OcJizM">
            </div>
        <div class="col-12 text-left">
            <p><strong>Fecha:</strong> {{ $driverSettlement->date }}</p>
            <p><strong>Chofer:</strong> {{ $driverSettlement->driver->name }}</p>
        </div>
        <div class="col-12 text-left">
            <p><strong>CONSTANCIAS:</strong></p>
            <table class="table table-bordered">
               <thead>
                    <tr>
                        <td>Fecha</td>
                        <td>Numero</td>
                        <td>Cliente</td>
                        <td>IVA</td>
                        <td>Peajes</td>
                        <td>Total</td>
                    </tr>
               </thead> 
               <tbody>
                @php use App\Models\TravelItem; @endphp
                    @foreach($driverSettlement->travelCertificates as $travelCertificate)
                        <tr>
                            <td>{{ $travelCertificate->date }}</td>
                            <td>{{ $travelCertificate->number }}</td>
                            <td>{{ $travelCertificate->client->name }}</td>
                            <td>{{ $travelCertificate->iva }}</td>
                            <td>@php $tolls = TravelItem::where('type', 'PEAJE')->where('travelCertificateId', $travelCertificate->id); $totalTolls = $tolls->sum('price'); echo $totalTolls;@endphp</td>
                            <td>{{ $travelCertificate->total }}</td>
                        </tr>
                    @endforeach      
               </tbody>
            </table>      
        </div>
        <div class="col-12 text-left">
            <p><strong>A FAVOR DEL CHOFER:</strong> ${{ $driverSettlement->total }}</p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>
</html>