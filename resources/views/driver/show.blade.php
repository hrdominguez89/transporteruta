@extends('adminlte::page')

@section('title', 'Choferes')

@section('content_header')
    <div class="row">
        <a href="{{ Route('drivers') }}" class="btn btn-sm btn-secondary mr-2">Volver</a>
        <h1 class="col-9">Chofer: <strong>{{ $driver->name }}</strong></h1>
        <button class="btn btn-sm btn-success col-2" data-toggle="modal" data-target="#updateModal{{ $driver->id }}">Actualizar
            Chofer</button>
        @include('driver.modals.update')
    </div>
@stop

@section('content')
    <h4>Datos del Chofer</h4>
    <table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>DNI/CUIT</th>
                <th>Direccion</th>
                <th>Ciudad</th>
                <th>Telefono</th>
                <th>Tipo</th>
                <th>Vehiculo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $driver->dni }}</td>
                <td>{{ $driver->address }}</td>
                <td>{{ $driver->city }}</td>
                <td>{{ $driver->phone }}</td>
                <td>{{ $driver->type }}</td>
                <td>{{ $driver->vehicle ? $driver->vehicle->name : "" }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    <h4>Liquidaciones Pendientes</h4>
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th style="width:3%;">Número nuevo</th>
                <th style="width:3%;">Número antiguo</th>
                <th>Fecha</th>
                <th>Pago a la Agencia</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($driver->driverSettlements as $driverSettlement)
                @if ($driverSettlement->liquidated == 'NO')
                    <tr>
                        <td>{{ number_format($driverSettlement->id, '0', ',', '.') }}</td>
                        <td>{{ $driverSettlement->number ? number_format($driverSettlement->number, '0', ',', '.') : '-' }}
                        <td style="font-size:14px;" class="text-center"
                            data-order="{{ \Carbon\Carbon::parse($driverSettlement->date)->timestamp }}">
                            {{ \Carbon\Carbon::parse($driverSettlement->date)->format('d/m/Y') }}</td>
                        <td>$&nbsp;{{ number_format($driverSettlement->total, 2, ',', '.') }}</td>
                        <td>
                            <a href="{{ Route('showDriverSettlement', $driverSettlement->id) }}"
                                class="btn btn-sm btn-info">Ver</a>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <br>
    <h4>Liquidaciones Realizadas</h4>
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th style="width:3%;">Número nuevo</th>
                <th style="width:3%;">Número antiguo</th>
                <th>Fecha</th>
                <th>Pago a la Agencia</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($driver->driverSettlements as $driverSettlement)
                @if ($driverSettlement->liquidated == 'SI')
                    <tr>
                        <td>{{ number_format($driverSettlement->id, '0', ',', '.') }}</td>
                        <td>{{ $driverSettlement->number ? number_format($driverSettlement->number, '0', ',', '.') : '-' }}
                        </td>
                        <td style="font-size:14px;" class="text-center"
                            data-order="{{ \Carbon\Carbon::parse($driverSettlement->date)->timestamp }}">
                            {{ \Carbon\Carbon::parse($driverSettlement->date)->format('d/m/Y') }}</td>
                        <td>$&nbsp;{{ number_format($driverSettlement->total, 2, ',', '.') }}</td>
                        <td>
                            <a href="{{ Route('showDriverSettlement', $driverSettlement->id) }}"
                                class="btn btn-sm btn-info">Ver</a>
                        </td>
                    </tr>
                @endif
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
