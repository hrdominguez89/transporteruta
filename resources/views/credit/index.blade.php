@extends('adminlte::page')

@section('title', 'Notas de Credito')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Notas de Credito</h1>
        <button class="btn btn-danger" data-toggle="modal" data-target="#generateModal">Generar Nota de Credito</button>
    </div>
    @include('credit.modals.generate')
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
            @foreach($credits as $credit)
                <tr>
                    <td>{{ $credit->number }}</td>
                    <td>{{ $credit->client->name }}</td>
                    <td>{{ $credit->total }}</td>
                    <td>
                        <a href="{{ Route('showCredit', $credit->id) }}" class="btn btn-info">Ver</a>
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