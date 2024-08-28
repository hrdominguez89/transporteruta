@extends('adminlte::page')

@section('title', 'Choferes')

@section('content_header')
    <div class="row">
        <a href="{{ Route('drivers') }}" class="btn btn-secondary mr-2">Volver</a>
        <h1 class="col-9">Chofer: <strong>{{ $driver->name }}</strong></h1>
        <button class="btn btn-success col-2" data-toggle="modal" data-target="#updateModal{{ $driver->id }}">Actualizar Chofer</button>
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
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $driver->dni }}</td>
                <td>{{ $driver->address }}</td>
                <td>{{ $driver->city }}</td>
                <td>{{ $driver->phone }}</td>
                <td>{{ $driver->type }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    <h4>Liquidaciones Pendientes</h4>
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Fecha</th>
                <th>Tarifa del Chofer</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($driver->driverSettlements as $driverSettlement)
                @if($driverSettlement->liquidated =='NO')
                <tr>
                    <td>{{ $driverSettlement->number }}</td>
                    <td>{{ $driverSettlement->date }}</td>
                    <td>{{ $driverSettlement->total }}</td>
                    <td>
                        <a href="{{ Route('showDriverSettlement', $driverSettlement->id) }}" class="btn btn-info">Ver</a>
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <br>
    <h4>Liquidaciones Realizadas</h4>
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Fecha</th>
                <th>Tarifa del Chofer</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($driver->driverSettlements as $driverSettlement)
                @if($driverSettlement->liquidated =='SI')
                <tr>
                    <td>{{ $driverSettlement->number }}</td>
                    <td>{{ $driverSettlement->date }}</td>
                    <td>{{ $driverSettlement->total }}</td>
                    <td>
                        <a href="{{ Route('showDriverSettlement', $driverSettlement->id) }}" class="btn btn-info">Ver</a>
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@stop