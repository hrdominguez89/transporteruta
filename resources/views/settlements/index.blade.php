@extends('adminlte::page')

@section('title', 'liquidacion de choferes')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Liquidaciones de sueldo</h1>
        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#storeModal">Crear liquidacion</button>
    </div>
    @include('settlements.modals.store')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>ID</th>
                <th>Chofer</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($settlements as $settlement)
                <tr>
                    <td>{{ $settlement->id }}</td>
                    <td>{{ $settlement->driver->name }}</td>
                    <td>{{ $settlement->date }}</td>
                    <td>
                        <a href="{{ route('ShowSettlement', $settlement->id) }}" class="btn btn-sm btn-success">ver</a>
                    </td>
                </tr>
                @include('settlements.show')
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