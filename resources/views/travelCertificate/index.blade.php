@extends('adminlte::page')

@section('title', 'Constancias de Viaje')

@section('content_header')
    <div class="row">
        <h1 class="col-9">Constancias de Viaje</h1>
        <button class="btn btn-sm btn-danger col-2" data-toggle="modal" data-target="#storeModal">Agregar Constancia</button>
    </div>
    @include('travelCertificate.modals.store')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Nro. Nuevo</th>
                <th>Nro. Antiguo</th>
                <th>Cliente</th>
                <th>Chofer</th>
                <th>Facturada</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($travelCertificates as $travelCertificate)
                <tr>
                    <td data-order="{{ $travelCertificate->id }}">{{ number_format($travelCertificate->id, 0, ',', '.') }}
                    </td>
                    <td data-order="{{ $travelCertificate->number }}">
                        {{ $travelCertificate->number ? number_format($travelCertificate->number, 0, ',', '.') : ' - ' }}
                    </td>
                    <td>{{ $travelCertificate->client->name }}</td>
                    <td>{{ $travelCertificate->driver->name }}</td>
                    <td>{{ $travelCertificate->invoiced }}</td>
                    <td>
                        <a href="{{ Route('showTravelCertificate', $travelCertificate->id) }}" class="btn btn-sm btn-info">Ver</a>
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
