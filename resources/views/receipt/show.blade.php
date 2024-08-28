@extends('adminlte::page')

@section('title', 'Recibos')

@section('content_header')
    <div class="row">
        <a href="{{ Route('receipts') }}" class="btn btn-secondary">Volver</a>
        <h1 class="col-7">Recibo N°<strong>{{ $receipt->id }}</strong></h1>
        @if($receipt->paid == 'NO')
            <button class="btn btn-warning col-4" data-toggle="modal" data-target="#paidModal{{ $receipt->id }}">Marcar como Pagado</button>
        @else
            <button class="btn btn-danger col-2 mr-2" data-toggle="modal" data-target="#cancelModal{{ $receipt->id }}">Anular Pago</button>
            <a href="{{ Route('receiptPdf', $receipt->id) }}" class="btn btn-info col-2">Generar PDF</a>
        @endif
    </div>
    @if($receipt->paid == 'SI') 
       <h5 class="text-danger">El recibo se marco como pagado y se desconto el saldo de la cuenta corriente</h5> 
    @endif
    @include('receipt.modals.paid')
    @include('receipt.modals.cancel')
@stop

@section('content')
<table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Pagado</th>
                <th>Retenciones</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $receipt->date }}</td>
                <td>
                    <a href="{{ Route('showClient', $receipt->client->id) }}">{{ $receipt->client->name }}</a>
                </td>
                <td>{{ $receipt->paid }}</td>
                <td>{{ $receipt->taxTotal }}</td>
                <td>{{ $receipt->total }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    <h4>Facturas Agregadas</h4>
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Precio (Con IVA)</th>
                <th>Balance</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->invoices as $invoice)
                <tr>
                    <td>
                        <a href="{{ Route('showInvoice', $invoice->id) }}">{{ $invoice->number }}</a>
                    </td>
                    <td>{{ $invoice->client->name }}</td>
                    <td>{{ $invoice->totalWithIva }}</td>
                    <td>{{ $invoice->balance }}</td>
                    <td>
                        @if($receipt->paid == 'NO')
                            <form action="{{ Route('removeFromReceipt', $invoice->id) }}" method="POST"    >
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="receiptId" value="{{ $receipt->id }}">
                                <button type="submit" class="btn btn-warning">Quitar del Recibo</button>
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
    @if($receipt->paid == 'NO')
    <h4>Facturas del Cliente sin Pagar</h4>
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Precio (Con IVA)</th>
                <th>Balance</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->client->invoices as $invoice)
                @if($invoice->paid == 'NO' and $invoice->invoiced == 'SI')
                    <tr>
                        <td>
                            <a href="{{ Route('showInvoice', $invoice->id) }}">{{ $invoice->number }}</a>
                        </td>
                        <td>{{ $invoice->client->name }}</td>
                        <td>{{ $invoice->totalWithIva }}</td>
                        <td>{{ $invoice->balance }}</td>
                        <td>
                            <button class="btn btn-success" data-toggle="modal" data-target="#addInvoiceModal{{ $invoice->id }}{{ $receipt->id }}">Agregar al Recibo</button>
                        </td>
                    </tr>
                @endif
                @include('receipt.modals.addInvoice')
            @endforeach
        </tbody>
    </table>
    @endif
@stop