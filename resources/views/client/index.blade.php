@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <div class="row">
        <h1 class="col-7">Clientes</h1>
        <a href="{{ Route('generateDebtorsPdf') }}" class="btn btn-sm btn-info col-2 mr-2">Reporte Deudores</a>
        <button class="btn btn-sm btn-danger col-2" data-toggle="modal" data-target="#storeModal">Agregar Cliente</button>
    </div>
    @include('client.modals.store')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th class="text-center">Nombre</th>
                <th class="text-center">DNI/CUIT</th>
                <th class="text-center">Saldo</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($clients as $client)
                <tr>
                    <td class="text-center">{{ $client->name }}</td>
                    <td class="text-center">{{ $client->dni }}</td>
                    @if ($client->balance > 0)
                        <td class="bg-danger text-center" data-order="{{ $client->balance }}">
                            $&nbsp;{{ number_format($client->balance, 2, ',', '.') }}</td>
                    @else
                        <td class="text-right" data-order="{{ $client->balance }}">
                            $&nbsp;{{ number_format($client->balance, 2, ',', '.') }}</td>
                    @endif
                    <td class="text-center">
                        <a href="{{ Route('showClient', $client->id) }}" class="btn btn-sm btn-info">Ver</a>
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
