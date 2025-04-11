@extends('adminlte::page')

@section('title', 'Medios de Pago')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Medios de Pago</h1>
        <button class="btn btn-danger" data-toggle="modal" data-target="#storeModal">Agregar Medio de Pago</button>
    </div>
    @include('paymentMethod.modals.store')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paymentMethods as $paymentMethod)
                <tr>
                    <td>{{ $paymentMethod->name }}</td>
                    <td>
                        <button class="btn btn-success" data-toggle="modal" data-target="#updateModal{{ $paymentMethod->id }}">Editar</button>
                    </td>
                </tr>
                @include('paymentMethod.modals.update')
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