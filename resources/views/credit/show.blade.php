@extends('adminlte::page')

@section('title', 'Notas de Credito')

@section('content_header')
    <div class="row">
    <a href="{{ Route('credits') }}" class="btn btn-sm btn-secondary">Volver</a>
    <h1 class="col-9">Nota de Credito N°<strong>{{ $credit->number }}</strong></h1>
    @if($credit->invoiceId == 0)
        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addInvoiceModal{{ $credit->id }}">Agregar Factura</button>
    @else
        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#removeInvoiceModal{{ $credit->id }}">Quitar Factura</button>
    @endif
    </div>
    @include('credit.modals.addInvoice')
    @include('credit.modals.removeInvoice')
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
                    <td>{{ $credit->number }}</td>
                    <td>{{ $credit->date }}</td>
                    <td>
                        <a href="{{ Route('showClient', $credit->client->id) }}">{{ $credit->client->name }}</a>
                    </td>
                    <td>{{ $credit->total }}</td>
                    <td>
                        @if($credit->invoiceId != 0)
                            <a href="{{ Route('showInvoice', $credit->invoiceId) }}">{{ $credit->invoice->number }}</a>
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