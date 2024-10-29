@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Usuarios</h1>
        <button class="btn btn-danger col-2" data-toggle="modal" data-target="#storeModal">Agregar Usuario</button>
    </div>
    @include('user.modals.store')
@stop

@section('content')
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <button class="btn btn-success" data-toggle="modal" data-target="#updateModal{{ $user->id }}">Editar</button>
                        <button class="btn btn-warning" data-toggle="modal" data-target="#deleteModal{{ $user->id }}">Eliminar</button>
                    </td>
                </tr>
                @include('user.modals.update')
                @include('user.modals.delete')
            @endforeach
        </tbody>
    </table>
@stop