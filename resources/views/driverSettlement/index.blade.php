@extends('adminlte::page')

@section('title', 'Liquidaciones de Choferes')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Liquidaciones de Choferes</h1>
        <button class="btn btn-danger" data-toggle="modal" data-target="#generateModal">Generar Liquidacion</button>
    </div>
    @include('driverSettlement.modals.generate')
@stop

@section('content')
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Chofer</th>
                <th>Total</th>
                <th>Medio de Pago</th>
                <th>Liquidado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($driverSettlements as $driverSettlement)
                <tr>
                    <td>{{ $driverSettlement->number }}</td>
                    <td>{{ $driverSettlement->driver->name }}</td>
                    <td>{{ $driverSettlement->total }}</td>
                    <td>@if($driverSettlement->paymentMethodId != 0){{ $driverSettlement->paymentMethod->name }}@endif</td>
                    <td>{{ $driverSettlement->liquidated }}</td>
                    <td>
                        <a href="{{ Route('showDriverSettlement', $driverSettlement->id) }}" class="btn btn-info">Ver</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop