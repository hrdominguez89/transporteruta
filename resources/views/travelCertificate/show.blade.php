@extends('adminlte::page')

@section('title', 'Constancias de Viaje')

@section('content_header')
    <div class="row">
        <a href="{{ Route('travelCertificates') }}" class="btn btn-secondary mr-2">Volver</a>
        <h1 class="col-7">Constancia de Viaje N°<strong>{{ $travelCertificate->number }}</strong></h1>
        @if($travelCertificate->invoiced == 'NO')
        <button class="btn btn-danger col-2 mr-2" data-toggle="modal" data-target="#storeModal">Agregar Nuevo Item</button>
        <button class="btn btn-success col-2" data-toggle="modal" data-target="#updateModal{{ $travelCertificate->id }}">Actualizar Constancia</button>
        @else
        <a href="{{ Route('travelCertificatePdf', $travelCertificate->id) }}" class="btn btn-info col-4">Generar PDF</a>
        <strong class="text-danger">La constancia ha sido agregada a la factura <a href="{{ Route('showInvoice', $travelCertificate->invoice->id) }}">{{ $travelCertificate->invoice->id }}</a>, no se pueden realizar modificaciones.</strong>
        @endif
    </div>
    @include('travelItem.modals.store')
    @include('travelCertificate.modals.update')
@stop

@section('content')
    <h4>Detalles</h4>
    <table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Chofer</th>
                <th>Pago al Chofer</th>
                <th>Precio (Sin IVA)</th>
                <th>IVA</th>
                <th>Destino</th>
                <th>Precio Total (Con IVA)</th>
                <th>Facturado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $travelCertificate->date }}</td>
                <td>{{ $travelCertificate->client->name }}</td>
                <td>{{ $travelCertificate->driver->name }}</td>
                <td>{{ $travelCertificate->driverPayment }}</td>
                <td>{{ $travelCertificate->total }}</td>
                <td>{{ $travelCertificate->iva }}</td>
                <td>{{ $travelCertificate->destiny }}</td>
                <td>{{ $travelCertificate->total + $travelCertificate->iva }}</td>
                <td>{{ $travelCertificate->invoiced }}</td>
            </tr>
        </tbody>
    </table>
    <h4>Items de Viaje</h4>
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Tipo</th>
                <th>Precio Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($travelCertificate->travelItems as $travelItem)
                <tr>
                    <td>{{ $travelItem->type }}</td>
                    <td>{{ $travelItem->price }}</td>
                    @if($travelCertificate->invoiced == 'NO')
                        <td>
                            <button class="btn btn-danger" data-toggle="modal" data-target="#deleteItemModal{{ $travelItem->id }}">Eliminar</button>
                        </td>
                    @else
                        <td>
                            <strong class="text-danger">¡No se pueden realizar cambios!</strong>
                        </td>
                    @endif
                </tr>
                @include('travelItem.modals.delete')
            @endforeach
        </tbody>
    </table>
@stop