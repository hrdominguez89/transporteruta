@extends('adminlte::page')

@section('title', 'Facturas ARCA')

@section('content_header')
    @if(session()->has('message'))
        <div class="alert alert-{{ session('flag') ? 'success' : 'danger' }}">
            {{ session('message') }}
        </div>
    @endif

    <div class="row">
        <h1 class="col-7">Facturas ARCA</h1>
        <button class="btn btn-sm btn-danger col-2" data-toggle="modal" data-target="#generateModal">
            Generar factura
        </button>
    </div>
    @include('arca.modals.generate')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th class="text-center">N° Comprobante</th>
                <th class="text-center">Pto. Vta.</th>
                <th class="text-center">Cliente</th>
                <th class="text-center">Total</th>
                <th class="text-center">Fecha</th>
                <th class="text-center">CAE</th>
                <th class="text-center">Resultado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facturasArca as $factura)
                <tr>
                    <td>{{ sprintf('%08d', $factura->cbt_desde) }}</td>
                    <td>{{ sprintf('%05d', $factura->punto_vta) }}</td>
                    <td>{{ $factura->invoice->client->name ?? '-' }}</td>
                    <td data-order="{{ $factura->imp_total }}">
                        $&nbsp;{{ number_format($factura->imp_total, 2, ',', '.') }}
                    </td>
                    <td data-order="{{ $factura->fecha_cbte }}">
                        {{ \Carbon\Carbon::createFromFormat('Ymd', $factura->fecha_cbte)->format('d/m/Y') }}
                    </td>
                    <td>{{ $factura->cae }}</td>
                    <td>
                        <span class="badge badge-{{ $factura->resultado === 'A' ? 'success' : 'danger' }}">
                            {{ $factura->resultado === 'A' ? 'Aprobado' : 'Rechazado' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('showFacturaArca', $factura->id) }}" class="btn btn-sm btn-info">Ver</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.data-table').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                }
            });
            $('.select2').select2();
        });
    </script>
@stop