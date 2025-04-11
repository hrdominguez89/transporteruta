@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <div class="row">
        <a href="{{ Route('clients') }}" class="btn btn-secondary mr-2">Volver</a>
        <h1 class="col-9">Cliente: <strong>{{ $client->name }}</strong></h1>
        <button class="btn btn-success col-2" data-toggle="modal" data-target="#updateModal{{ $client->id }}">Actualizar Cliente</button>
        @include('client.modals.update')
    </div>
@stop

@section('content')
    <h4>Datos del Cliente</h4>
    <table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>DNI/CUIT</th>
                <th>Direccion</th>
                <th>Ciudad</th>
                <th>Telefono</th>
                <th>IVA Tipo</th>
                <th>Saldo Deudor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $client->dni }}</td>
                <td>{{ $client->address }}</td>
                <td>{{ $client->city }}</td>
                <td>{{ $client->phone }}</td>
                <td>{{ $client->ivaType }}</td>
                <td>{{ $client->balance }}</td>
            </tr>
        </tbody>
    </table>
    <h4>Observaciones:</h4>
    <p>{{ $client->observations }}</p>
    <br>
    <h4>Facturas Pendientes de Pagar</h4>
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Total con IVA</th>
                <th>Balance</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($client->invoices as $invoice)
                @if($invoice->paid == 'NO' and $invoice->invoiced == 'SI')
                <tr>
                    <td>{{ $invoice->number }}</td>
                    <td>{{ $invoice->totalWithIva }}</td>
                    <td>{{ $invoice->balance }}</td>
                    <td>
                        <a href="{{ Route('showInvoice', $invoice->id) }}" class="btn btn-info">Ver</a>
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <br>
    <h4>Facturas Abiertas</h4>
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Total con IVA</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($client->invoices as $invoice)
                @if($invoice->invoiced == 'NO')
                <tr>
                    <td>{{ $invoice->number }}</td>
                    <td>{{ $invoice->totalWithIva }}</td>
                    <td>
                        <a href="{{ Route('showInvoice', $invoice->id) }}" class="btn btn-info">Ver</a>
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <br>
    <h4>Facturas Pagadas</h4>
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Total con IVA</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($client->invoices as $invoice)
                @if($invoice->paid == 'SI')
                <tr>
                    <td>{{ $invoice->number }}</td>
                    <td>{{ $invoice->totalWithIva }}</td>
                    <td>
                        <a href="{{ Route('showInvoice', $invoice->id) }}" class="btn btn-info">Ver</a>
                    </td>
                </tr>
                @endif
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