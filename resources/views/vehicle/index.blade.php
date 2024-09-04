@extends('adminlte::page')

@section('title', 'Vehiculos')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Vehiculos</h1>
        <button class="btn btn-danger" data-toggle="modal" data-target="#storeModal">Agregar Vehiculo</button>
    </div>
    @include('vehicle.modals.store')
@stop

@section('content')
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $vehicle)
                <tr>
                    <td>{{ $vehicle->name }}</td>
                    <td>
                        <button class="btn btn-success" data-toggle="modal" data-target="#updateModal{{ $vehicle->id }}">Editar</button>
                    </td>
                </tr>
                @include('vehicle.modals.update')
            @endforeach
        </tbody>
    </table>
@stop