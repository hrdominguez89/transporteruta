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
    @php
        $valor_declarado = 0;
        $valor_viajes = 0;
        $porcentaje = 0;
        foreach ($cargas as $carga)
        {
            $valor_declarado += $carga->remito?->valor_declarado;
            $valor_viajes += $carga->precio;
        }
        $porcentaje = (100 * $valor_viajes) / $valor_declarado;

    @endphp
    <table class="table table-sm table-bordered text-center data-table" style="width: 20%">
        <thead class="bg-danger">
            <tr>
                <th>Total de valor decladarado</th>
                <th>Total de viajes</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($valor_declarado,2,',','.') }}</td>
                <td>{{ number_format($valor_viajes,2,',','.') }}</td>
                <td>%{{ number_format($porcentaje,2,',','.') }}</td>  
            </tr>
        </tbody>
    </table>
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
             <tr>
                <th>Cliente</th>
                <th>Cliente 3ro</th>
                <th>Destino</th>
                <th>Remito</th>
                <th>Nombre</th>
                <th>Fecha de recepcion</th>
                <th>Bultos</th>
                <th>Pallets normales</th>
                <th>Pallets grandes</th>
                <th>Estado de envio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cargas as $carga)
                @include('stock.admin.modals.delete')

                @if ($carga->estadoActual?->estado =='ENTREGADO')
                    <tr class="bg-success">
                @elseif($carga->estadoActual?->estado =='RECHAZADO')
                    <tr class="bg-danger">
                @else
                    <tr>
                @endif   
                    <td>{{ $carga->client->name }}</td>
                    <td>{{ $carga->cliente_tercero?->nombre ?? 'no asignado' }}</td>
                    <td>{{ $carga->destino }}</td>
                    <td>{{ $carga->remito?->numero ?? 'no asignado' }}</td>
                    <td>{{ $carga->nombre }}</td>
                    <td>{{ $carga->fecha_de_recepcion?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $carga->cantidad_bulto }}</td>
                    <td>{{ $carga->cantidad_pallet_normal }}</td>
                    <td>{{ $carga->cantidad_pallet_grande }}</td>
                    <td>{{ $carga->estadoActual?->estado }}</span>  {{ $carga->estadoActual?->horario }} </td>
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