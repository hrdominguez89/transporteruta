@extends('adminlte::page')

@section('title', 'Facturas')

@section('content_header')
    <div class="row">
        <a href="{{ Route('invoices') }}" class="btn btn-secondary">Volver</a>
        <h1 class="col-7">Factura N°<strong>{{ $invoice->number }}</strong></h1>
        @if($invoice->invoiced == 'SI' and $invoice->paid == 'NO')
            <button class="btn btn-danger col-2 mr-2" data-toggle="modal" data-target="#cancelModal{{ $invoice->id }}">Anular Factura</button>
            <a href="{{ Route('invoicePdf', $invoice->id) }}" class="btn btn-info col-2">Generar PDF</a>
        @elseif($invoice->invoiced == 'NO')
            <button class="btn btn-primary col-4" data-toggle="modal" data-target="#invoicedModal{{ $invoice->id }}">Facturar</button>
        @endif
        @if($invoice->paid == 'SI') 
        <a href="{{ Route('invoicePdf', $invoice->id) }}" class="btn btn-info col-4">Generar PDF</a>
        <br>
        <h5 class="text-danger">La factura se marco como pagada y se desconto el saldo de la cuenta corriente</h5>
        @endif
    </div>
    @include('invoice.modals.invoiced')
    @include('invoice.modals.cancel')
@stop

@section('content')
<table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total (Sin IVA)</th>
                <th>IVA</th>
                <th>Balance</th>
                <th>Facturado</th>
                <th>Total (Con IVA)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->date }}</td>
                <td>
                    <a href="{{ Route('showClient', $invoice->client->id) }}">{{ $invoice->client->name }}</a>
                </td>
                <td>{{ $invoice->total }}</td>
                <td>{{ $invoice->iva }}</td>
                <td>{{ $invoice->balance }}</td>
                <td>{{ $invoice->invoiced }}</td>
                <td>{{ ($invoice->total + $invoice->iva) }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    <h4>Notas de Credito</h4>
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Fecha</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->credits as $credit)
                <tr>
                    <td>
                        <a href="{{ Route('showCredit', $credit->id) }}">{{ $credit->number }}</a>
                    </td>
                    <td>{{ $credit->date }}</td>
                    <td>{{ $credit->total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <h4>Constancias de Viaje Agregadas</h4>
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Chofer</th>
                <th>Precio (Sin IVA)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->travelCertificates as $travelCertificate)
                <tr>
                    <td>
                        <a href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">{{ $travelCertificate->number }}</a>
                    </td>
                    <td>{{ $travelCertificate->driver->name }}</td>
                    <td>{{ $travelCertificate->total }}</td>
                    <td>
                        @if($invoice->invoiced == 'NO')
                            <form action="{{ Route('removeFromInvoice', $travelCertificate->id) }}" method="POST"    >
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="invoiceId" value="{{ $invoice->id }}">
                                <button type="submit" class="btn btn-warning">Quitar de la Factura</button>
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
    @if($invoice->invoiced == 'NO')
    <h4>Constancias de Viaje del Cliente sin Liquidar</h4>
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Chofer</th>
                <th>Precio (Sin IVA)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->client->travelCertificates as $travelCertificate)
                @if($travelCertificate->invoiceId != $invoice->id and $travelCertificate->invoiced == 'NO')
                    <tr>
                        <td>
                            <a href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">{{ $travelCertificate->number }}</a>
                        </td>
                        <td>{{ $travelCertificate->driver->name }}</td>
                        <td>{{ $travelCertificate->total }}</td>
                        <td>
                            <form action="{{ Route('addToInvoice', $travelCertificate->id) }}" method="POST"    >
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="invoiceId" value="{{ $invoice->id }}">
                                <button type="submit" class="btn btn-success">Agregar a la Factura</button>
                            </form>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    @endif
@stop