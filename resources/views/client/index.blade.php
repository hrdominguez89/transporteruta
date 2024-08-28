@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <div class="row">
        <h1 class="col-7">Clientes</h1>
        <a href="{{ Route('generateDebtorsPdf') }}" class="btn btn-info col-2 mr-2">Reporte Deudores</a>
        <button class="btn btn-danger col-2" data-toggle="modal" data-target="#storeModal">Agregar Cliente</button>
    </div>
    @include('client.modals.store')
@stop

@section('content')
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Nombre</th>
                <th>DNI/CUIT</th>
                <th>Saldo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
                <tr>
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->dni }}</td>
                    @if($client->balance > 0)
                    <td class="bg-danger">{{ $client->balance }}</td>
                    @else
                    <td>{{ $client->balance }}</td>
                    @endif
                    <td>
                        <a href="{{ Route('showClient', $client->id) }}" class="btn btn-info">Ver</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
