@extends('adminlte::page')

@section('title', 'Pago')

@section('content_header')
    <div class="row">
        <a href="{{ Route('pagos') }}" class="btn btn-sm btn-secondary mr-2">Volver</a>
        <h1 class="col-9">Cliente: <strong>{{ $pago->client?->name }}</strong></h1>
        <button class="btn btn-sm btn-success col-2" data-toggle="modal" data-target="#updateModal{{ $pago->id }}">Editar pago</button>
        @include('payments.modals.edit')
        @if($errors->any())
            <div id="errorAlert" class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <script>
                setTimeout(function () {
                    $('#errorAlert').alert('close');
                }, 5000);
            </script>
        @endif
    </div>
@stop

@section('content')
    <h4>Datos del pago</h4>
    <table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Metodo</th>
                <th>Total</th>
                <th>Restante</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $pago->method }}</td>
                <td>{{ $pago->total }}</td>
                <td>{{ $pago->balance }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    <h3>Recibos en los que se encuentra el pago:</h3>
    <table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Id</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Pagado</th>
            </tr>
        </thead>
        <tbody>
            
            @foreach ($pago->obtenerRecibos as $recibo)
                <tr>
                    <td>{{ $recibo->id }}</td>
                    <td>{{ $recibo->date }}</td>
                    <td>{{ $recibo->total }}</td>
                    <td>{{ $recibo->paid }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.data-table').DataTable();
        });
        var table = new DataTable('.data-table', {
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            }
        });
        $('.select2').select2();
    </script>
@stop