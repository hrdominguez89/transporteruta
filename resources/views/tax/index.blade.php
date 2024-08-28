@extends('adminlte::page')

@section('title', 'Impuestos')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Impuestos</h1>
        <button class="btn btn-danger" data-toggle="modal" data-target="#storeModal">Agregar Impuesto</button>
    </div>
    @include('tax.modals.store')
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
            @foreach($taxes as $tax)
                <tr>
                    <td>{{ $tax->name }}</td>
                    <td>
                        <button class="btn btn-success" data-toggle="modal" data-target="#updateModal{{ $tax->id }}">Editar</button>
                    </td>
                </tr>
                @include('tax.modals.update')
            @endforeach
        </tbody>
    </table>
@stop