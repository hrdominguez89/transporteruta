@extends('adminlte::page')
@section('title', 'Corte operaciones')
@section('content_header')
    <div class="row">
        <h1 class="col-5">Cargas</h1>
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
                <th>cliente_tercero</th>
                <th>fecha_recepcion</th>
                <th>carga_remito(link)</th>
                <th>Destino</th>
                <th>carga_nombre</th>
                <th>bulto</th>
                <th>pallet_normal</th>
                <th>pallet_grande</th>
                <th>precio</th>
                <th>Generar constancia</th>             
            </tr>
        </thead>
        <tbody>
            @foreach ($cargas as $driver_id => $grupo)
                <tr class="bg-secondary text-white">
                    <td colspan="10" class="text-left font-weight-bold">
                        Chofer: {{ $grupo->first()->driver?->name ?? 'Sin asignar' }}
                    </td>
                </tr>
                @foreach ($grupo as $carga)
                    <tr>
                        <td>{{ $carga->cliente_tercero?->nombre ?? 'no asignado' }}</td>
                        <td>{{ $carga->fecha_de_recepcion?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $carga->remito?->numero ?? 'no asignado' }}</td>
                        <td>{{ $carga->destino }}</td>
                        <td>{{ $carga->nombre }}</td>
                        <td>{{ $carga->cantidad_bulto }}</td>
                        <td>{{ $carga->cantidad_pallet_normal }}</td>
                        <td>{{ $carga->cantidad_pallet_grande }}</td>
                        <td>{{ $carga->precio }}</td>
                        <td>
                            <a href="{{ Route('generartc', $carga->id) }}" class="btn btn-sm btn-info">Generar</a>
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
@stop