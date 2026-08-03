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
            <th>Cliente 3ro</th>
            <th>Destino</th>
            <th>Remito</th>
            <th>Nombre</th>
            <th>Fecha de recepcion</th>
            <th>Tipo</th>
            <th>Espacio</th>
            <th>Estado de envio</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cargas as $carga)
            <tr>
                <td>{{ $carga->cliente_tercero?->nombre ?? 'no asignado' }}</td>
                <td>{{ $carga->destino }}</td>
                <td>{{ $carga->remito?->numero ?? 'no asignado' }}</td>
                <td>{{ $carga->nombre }}</td>
                <td>{{ $carga->fecha_de_recepcion?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $carga->tipo }}</td>
                <td>{{ $carga->espacio }}</td>
                <td>{{ $carga->estado_de_envio }}</td>
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