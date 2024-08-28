@extends('adminlte::page')

@section('title', 'Choferes')

@section('content_header')
    <div class="row">
        <h1 class="col-9">Choferes</h1>
        <button class="btn btn-danger col-2" data-toggle="modal" data-target="#storeModal">Agregar Chofer</button>
    </div>
    @include('driver.modals.store')
@stop

@section('content')
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Nombre</th>
                <th>DNI/CUIT</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($drivers as $driver)
                <tr>
                    <td>{{ $driver->name }}</td>
                    <td>{{ $driver->dni }}</td>
                    <td>
                        <a href="{{ Route('showDriver', $driver->id) }}" class="btn btn-info">Ver</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop