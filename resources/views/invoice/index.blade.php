@extends('adminlte::page')

@section('title', 'Facturas')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Facturas</h1>
        <button class="btn btn-danger" data-toggle="modal" data-target="#generateModal">Generar Factura</button>
    </div>
    @include('invoice.modals.generate')
@stop

@section('content')
    <table class="table table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Total (Con IVA)</th>
                <th>Balance</th>
                <th>Facturado</th>
                <th>Pagada</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->number }}</td>
                    <td>{{ $invoice->client->name }}</td>
                    <td>{{ $invoice->totalWithIva }}</td>
                    <td>{{ $invoice->balance }}</td>
                    <td>{{ $invoice->invoiced }}</td>
                    <td>{{ $invoice->paid }}</td>
                    <td>
                        <a href="{{ Route('showInvoice', $invoice->id) }}" class="btn btn-info">Ver</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop