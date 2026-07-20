@extends('adminlte::page')

@section('title', 'Factura ARCA')

@section('content_header')
    @if(session()->has('message'))
        <div class="alert alert-{{ session('flag') ? 'success' : 'danger' }}">
            {{ session('message') }}
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <a href="{{ route('facturasArca') }}" class="btn btn-sm btn-secondary">Volver</a>
        </div>
        <div class="col-12 mt-3">
            <h1>Comprobante N°: <strong>{{ sprintf('%08d', $facturaArca->cbt_desde) }}</strong></h1>
        </div>
        <div class="col-12 mt-2">
            <h3>Punto de venta: <strong>{{ sprintf('%05d', $facturaArca->punto_vta) }}</strong></h3>
        </div>
    </div>
@stop

@section('content')
    <table class="table table-bordered">
        <tbody>
            <tr>
                <th class="bg-light" style="width:30%">Cliente</th>
                <td>
                    @if ($facturaArca->invoice && $facturaArca->invoice->client)
                        <a target="_blank" href="{{ route('showClient', $facturaArca->invoice->client->id) }}">
                            {{ $facturaArca->invoice->client->name }}
                        </a>
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <th class="bg-light">Factura interna</th>
                <td>{{ $facturaArca->invoice->number ?? '-' }}</td>
            </tr>
            <tr>
                <th class="bg-light">Tipo de comprobante</th>
                <td>{{ $facturaArca->tipo_cbte }} (Factura A)</td>
            </tr>
            <tr>
                <th class="bg-light">Fecha del comprobante</th>
                <td>{{ \Carbon\Carbon::createFromFormat('Ymd', $facturaArca->fecha_cbte)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th class="bg-light">Importe total</th>
                <td>$&nbsp;{{ number_format($facturaArca->imp_total, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th class="bg-light">CAE</th>
                <td><strong>{{ $facturaArca->cae }}</strong></td>
            </tr>
            <tr>
                <th class="bg-light">Vencimiento del CAE</th>
                <td>{{ \Carbon\Carbon::createFromFormat('Ymd', $facturaArca->fecha_vto)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th class="bg-light">Resultado</th>
                <td>
                    <span class="badge badge-{{ $facturaArca->resultado === 'A' ? 'success' : 'danger' }}">
                        {{ $facturaArca->resultado === 'A' ? 'Aprobado' : 'Rechazado' }}
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
@stop