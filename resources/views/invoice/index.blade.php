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
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Número</th>
                <th>Cliente</th>
                <th>Total (Con IVA)</th>
                <th>Balance</th>
                <th>Facturado</th>
                <th>Pagada</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoices as $invoice)
                <tr>
                    <td data-order="{{ $invoice->number }}">{{ number_format($invoice->number, 0, ',', '.') }}</td>
                    <td>{{ $invoice->client->name }}</td>
                    <td class="text-right" data-order="{{ $invoice->totalWithIva }}">
                        $&nbsp;{{ number_format($invoice->totalWithIva, 2, ',', '.') }}</td>
                    <td class="text-right" data-order="{{ $invoice->balance }}">$&nbsp;{{ number_format($invoice->balance, 2, ',', '.') }}</td>
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
