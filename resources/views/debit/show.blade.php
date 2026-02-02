@extends('adminlte::page')

@section('title', 'Nota de debito')

@section('content_header')
    <div class="row">
    <a href="{{ Route('debitos') }}" class="btn btn-sm btn-secondary">Volver</a>
    <h1 class="col-9">Nota de Debito N°<strong>{{ $debit->number }}</strong></h1>
    @if($debit->invoiceId == null)
        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addInvoiceModal{{ $debit->id }}">Agregar Factura</button>
    @else
        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#removeInvoiceModal{{ $debit->id }}">Quitar Factura</button>
    @endif
    </div>
    @include('debit.modals.addInvoice')
    @include('debit.modals.removeInvoice')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Factura</th>
            </tr>
        </thead>
        <tbody>
                <tr>
                    <td>{{ $debit->referenceNumber }}</td>
                    <td>{{ $debit->emission_date }}</td>
                    <td>
                        <a href="{{ Route('showClient', $debit->client->id) }}">{{ $debit->client->name }}</a>
                    </td>
                    <td>{{ $debit->balance }}</td>
                    <td>
                        @if($debit->invoiceId != null)
                            <a href="{{ Route('showInvoice', $debit->invoiceId) }}">{{ $debit->invoice->number }}</a>
                        @else
                            <span class="text-danger">No Agregada</span>
                        </td>
                        @endif
                </tr>
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