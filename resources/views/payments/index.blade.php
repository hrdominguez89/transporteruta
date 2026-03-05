@extends('adminlte::page')

@section('title', 'Pagos')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Pagos</h1>
        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#generateModal">Ingresar Pagos</button>
    </div>
    @include('receipt.modals.generate')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Metodo</th>
                <th>Total</th>
                <th>Balance</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ number_format($payment->id, 0, ',', '.') }}</td>
                    <td>{{ $payment->client->name }}</td>
                    <td>$&nbsp;{{ number_format($payment->total, 2, ',', '.') }}</td>
                    <td>$&nbsp;{{ number_format($payment->taxTotal, 2, ',', '.') }}</td>
                    <td>$&nbsp;{{ number_format($payment->balance , 2, ',', '.') }}</td>
                    <td>
                        <a href="{{ Route('showReceipt', $payment->id) }}" class="btn btn-sm btn-info">Ver</a>
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
