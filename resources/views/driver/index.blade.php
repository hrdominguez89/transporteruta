@extends('adminlte::page')

@section('title', 'Choferes')

@section('content_header')
    <div class="row">
        <h1 class="col-9">Choferes</h1>
        <button class="btn btn-sm btn-danger col-2" data-toggle="modal" data-target="#storeModal">Agregar Chofer</button>
    </div>
    @include('driver.modals.store')
    @if($errors->any())
    <div id="errorAlert" class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <script>
        setTimeout(function () {
            $('#errorAlert').alert('close');
        }, 5000);
    </script>
    @endif
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Nombre</th>
                <th>DNI/CUIT</th>
                <th>Tipo</th>
                <th>Sub tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($drivers as $driver)
                <tr>
                    <td>{{ $driver->name }}</td>
                    <td>{{ $driver->dni }}</td>
                    <td>{{ $driver->type }}</td>
                    <td>{{ $driver->subtipo }}</td>
                    <td>
                        <a href="{{ Route('showDriver', $driver->id) }}" class="btn btn-sm btn-info">Ver</a>
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