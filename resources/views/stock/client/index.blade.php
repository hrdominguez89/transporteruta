@extends('adminlte::page')

@section('title', 'Stock')

@section('content_header')
    <div class="row">
        <h1 class="col-9">Cargas de {{ $client?->name }}</h1>
    </div>
    @if($errors->any())
    <div id="errorAlert" class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <script>
        setTimeout(function () {
            $('#errorAlert').alert('close');
        }, 5000);
    </script>
    @endif
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Fecha de recepcion T.R.</th>
                <th>Fecha de entrega</th>
                <th>Precio</th>
                <th>Tipo</th>
                <th>Destino</th>
                <th>Remito</th>
                <th>Estado de envio</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cargas as $carga)
                <tr>
                    <td>{{ $carga->nombre }}</td>
                    <td>{{ $carga->cantidad }}</td>
                    <td>{{ $carga->fecha_de_recepcion }}</td>
                    <td>{{ $carga->fecha_de_entrega }}</td>
                    <td>{{ $carga->precio }}</td>
                    <td>{{ $carga->tipo }}</td>
                    <td>{{ $carga->destino }}</td>
                    <td>{{ $carga->remito?->numero }}</td>
                    <td>
                        <a href="{{ Route('showcarga', $carga->id) }}" class="btn btn-sm btn-info">Ver</a>
                    </td>
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