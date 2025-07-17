@extends('adminlte::page')

@section('title', 'Liquidaciones de Choferes')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Liquidaciones de Choferes</h1>
        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#generateModal">Generar Liquidacion</button>
    </div>
    @include('driverSettlement.modals.generate')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Número<br>Nuevo</th>
                <th>Número<br>Antiguo</th>
                <th>Chofer</th>
                <th>Total</th>
                <th>Medio de Pago</th>
                <th>Liquidado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($driverSettlements as $driverSettlement)
                <tr>
                    <td>{{ number_format($driverSettlement->id, 0, ',', '.') }}
                    </td>
                    <td> {{ $driverSettlement->number ? number_format($driverSettlement->number, 0, ',', '.') : ' - ' }}
                    </td>
                    <td>{{ $driverSettlement->driver->name }}</td>
                    <td data-order="{{ $driverSettlement->total }}" class="text-right">
                        $&nbsp;{{ number_format($driverSettlement->total, 2, ',', '.') }}</td>
                    <td>
                        @if ($driverSettlement->paymentMethodId != 0)
                            {{ $driverSettlement->paymentMethod->name }}
                        @endif
                    </td>
                    <td>{{ $driverSettlement->liquidated }}</td>
                    <td class="text-left">
                        <a href="{{ Route('showDriverSettlement', $driverSettlement->id) }}" class="btn btn-sm btn-info">Ver</a>
                        @if ($driverSettlement->liquidated == 'NO')
                        <a href="{{ Route('deleteDriverSettlement', $driverSettlement->id) }}" class="btn btn-sm btn-danger"
                            title="Eliminar liquidación"><span class="fas fa-trash"></span></a>
                        @endif
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

@extends('adminlte::page')

@section('title', 'Liquidaciones de Choferes')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Liquidaciones de Choferes</h1>
        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#generateModal">Generar Liquidacion</button>
    </div>
    @include('driverSettlement.modals.generate')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Número<br>Nuevo</th>
                <th>Número<br>Antiguo</th>
                <th>Chofer</th>
                <th>Total</th>
                <th>Medio de Pago</th>
                <th>Liquidado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($driverSettlements as $driverSettlement)
                <tr>
                    <td>{{ number_format($driverSettlement->id, 0, ',', '.') }}
                    </td>
                    <td> {{ $driverSettlement->number ? number_format($driverSettlement->number, 0, ',', '.') : ' - ' }}
                    </td>
                    <td>{{ $driverSettlement->driver->name }}</td>
                    <td data-order="{{ $driverSettlement->total }}" class="text-right">
                        $&nbsp;{{ number_format($driverSettlement->total, 2, ',', '.') }}</td>
                    <td>
                        @if ($driverSettlement->paymentMethodId != 0)
                            {{ $driverSettlement->paymentMethod->name }}
                        @endif
                    </td>
                    <td>{{ $driverSettlement->liquidated }}</td>
                    <td class="text-left">
                        <a href="{{ Route('showDriverSettlement', $driverSettlement->id) }}" class="btn btn-sm btn-info">Ver</a>
                        @if ($driverSettlement->liquidated == 'NO')
                        <a href="{{ Route('deleteDriverSettlement', $driverSettlement->id) }}" class="btn btn-sm btn-danger"
                            title="Eliminar liquidación"><span class="fas fa-trash"></span></a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop

{{--imprimir liquidaciones de choferes de un rango de fecha --}}
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
