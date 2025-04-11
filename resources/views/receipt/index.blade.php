@extends('adminlte::page')

@section('title', 'Recibos')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Recibos</h1>
        <button class="btn btn-danger" data-toggle="modal" data-target="#generateModal">Generar Recibo</button>
    </div>
    @include('receipt.modals.generate')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Pagado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipts as $receipt)
                <tr>
                    <td>{{ $receipt->number }}</td>
                    <td>{{ $receipt->client->name }}</td>
                    <td>{{ $receipt->total }}</td>
                    <td>{{ $receipt->paid }}</td>
                    <td>
                        <a href="{{ Route('showReceipt', $receipt->id) }}" class="btn btn-info">Ver</a>
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