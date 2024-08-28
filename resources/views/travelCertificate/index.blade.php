@extends('adminlte::page')

@section('title', 'Constancias de Viaje')

@section('content_header')
    <div class="row">
        <h1 class="col-9">Constancias de Viaje</h1>
        <button class="btn btn-danger col-2" data-toggle="modal" data-target="#storeModal">Agregar Constancia</button>
    </div>
    @include('travelCertificate.modals.store')
@stop

@section('content')
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Chofer</th>
                <th>Facturada</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($travelCertificates as $travelCertificate)
                <tr>
                    <td>{{ $travelCertificate->number }}</td>
                    <td>{{ $travelCertificate->client->name }}</td>
                    <td>{{ $travelCertificate->driver->name }}</td>
                    <td>{{ $travelCertificate->invoiced }}</td>
                    <td>
                        <a href="{{ Route('showTravelCertificate', $travelCertificate->id) }}" class="btn btn-info">Ver</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop