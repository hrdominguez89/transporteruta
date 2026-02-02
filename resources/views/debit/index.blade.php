@extends('adminlte::page')

@section('title', 'Notas de Debito')

@section('content_header')
<div class="row">
    @if(session('mensaje'))
    <div class="alert alert-success">
        {{ session('mensaje') }}
    </div>
    @endif
        <h1 class="col-10">Notas de Debito</h1>
        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#generateModal">Generar Nota de Debito</button>
    </div>
    @include('debit.modals.generate')
@stop
@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($debits as $debit)
                <tr>
                    <td>{{ $debit->referenceNumber }}</td>
                    <td>{{ $debit->client->name }}</td>
                    <td>{{ $debit->balance }}</td>
                    <td>
                        <a href="{{ Route('debitshow', $debit->id) }}" class="btn btn-sm btn-info">Ver</a>
                        <a href="{{ Route('deleteDebit', $debit->id) }}" class="btn btn-sm btn-danger">Eliminar</a>
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