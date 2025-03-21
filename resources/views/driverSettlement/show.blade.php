@extends('adminlte::page')

@section('title', 'Liquidaciones de Choferes')

@section('content_header')
    <div class="row">
        <a href="{{ Route('driverSettlements') }}" class="btn btn-secondary mr-2">Volver</a>
        <h1 class="col-8">Liquidacion N°<strong>{{ $driverSettlement->number }}</strong></h1>
        @if($driverSettlement->liquidated == 'NO')
            <button class="btn btn-primary col-3" data-toggle="modal" data-target="#liquidatedModal{{ $driverSettlement->id }}">Liquidar</button>
        @else
        <button class="btn btn-danger mr-2" data-toggle="modal" data-target="#cancelModal{{ $driverSettlement->id }}">Anular Liquidacion</button>
        <a href="{{ Route('driverSettlementPdf', $driverSettlement->id) }}" target="_blank" class="btn btn-info">Emitir Comprobante</a>
        @endif
    </div>
    @include('driverSettlement.modals.liquidated')
    @include('driverSettlement.modals.cancel')
@stop

@section('content')
<table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Fecha</th>
                <th>Chofer</th>
                <th>Total</th>
                <th>Liquidado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $driverSettlement->date }}</td>
                <td>
                    <a href="{{ Route('showDriver', $driverSettlement->driver->id) }}">{{ $driverSettlement->driver->name }}</a>
                </td>
                <td>{{ $driverSettlement->total }}</td>
                <td>{{ $driverSettlement->liquidated }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    <h4>Constancias de Viaje Agregadas</h4>
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Tarifa Chofer</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($driverSettlement->travelCertificates as $travelCertificate)
                <tr>
                    <td>
                        <a href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">{{ $travelCertificate->id }}</a>
                    </td>
                    <td>{{ $travelCertificate->client->name }}</td>
                    <td>{{ $travelCertificate->total - $travelCertificate->driverPayment}}</td>
                    <td>
                        @if($driverSettlement->liquidated == 'NO')
                            <form action="{{ Route('removeFromDriverSettlement', $travelCertificate->id) }}" method="POST"    >
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="driverSettlementId" value="{{ $driverSettlement->id }}">
                                <button type="submit" class="btn btn-warning">Quitar de la Liquidacion</button>
                            </form>
                        @else
                            <strong class="text-danger">¡No se pueden realizar cambios!</strong>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <br>
    @if($driverSettlement->liquidated == 'NO')
    <h4>Constancias de Viaje del Chofer sin Liquidar</h4>
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Tarifa del Chofer</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($driverSettlement->driver->travelCertificates as $travelCertificate)
            @if($travelCertificate->date >= $driverSettlement->dateFrom and $travelCertificate->date <= $driverSettlement->dateTo)
                @if($travelCertificate->driverSettlementId != $driverSettlement->id and $travelCertificate->isPaidToDriver == 'NO')
                    <tr>
                        <td>
                            <a href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">{{ $travelCertificate->id }}</a>
                        </td>
                        <td>{{ $travelCertificate->driver->name }}</td>
                        <td>{{ $travelCertificate->total - $travelCertificate->driverPayment}}</td>
                        <td>
                            <form action="{{ Route('addToDriverSettlement', $travelCertificate->id) }}" method="POST"    >
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="driverSettlementId" value="{{ $driverSettlement->id }}">
                                <button type="submit" class="btn btn-success">Agregar a la Liquidacion</button>
                            </form>
                        </td>
                    </tr>
                @endif
                @endif
            @endforeach
        </tbody>
    </table>
    @endif
@stop