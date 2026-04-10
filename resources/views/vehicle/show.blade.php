@extends('adminlte::page') 

@section('title', 'Vehiculo')

@section('content_header')
    @if(session('flag'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    <div class="row">
        <div class="col-12">
            <a href="{{ Route('vehicles') }}" class="btn btn-sm btn-secondary">Volver</a>
        </div>
        <div class="col-12 mt-3">
            <h1>Patente:
                <strong>{{  $vehiculo->name   }}</strong>
            </h1>
            {{-- <h5>Tipo:
                <strong>{{ $vehiculo->tipo }}</strong>
            </h5>
            <h5>Marca:
                <strong>{{ $vehiculo->marca}}</strong>
            </h5>
            <h5>Modelo:
                <strong>{{ $vehiculo->modelo }}</strong>
            </h5>
            <h5>Año:
                <strong>{{ $vehiculo->año}}</strong>
            </h5> --}}
        </div>
    </div>
    <form method="GET" action="{{ route('showVehicle',$vehiculo->id) }}">
        <div class="container-fluid mb-3 mt-3">
            <div class="row align-items-end">
                <div class="col-md-auto">
                    <div class="form-check">
                        <input name="historico" type="checkbox" id="historico" class="form-check-input" {{ request('historico') ? 'checked' : '' }}>
                        <label class="form-check-label" for="historico">Historico</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="desde">Desde</label>
                    <input type="date" name="desde" id="desde" class="form-control" value="{{ request('desde') }}">
                </div>
                <div class="col-md-4">
                    <label for="hasta">Hasta</label>
                    <input type="date" name="hasta" id="hasta" class="form-control" value="{{ request('hasta') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-block">Buscar</button>
                </div>
            </div>
        </div>
    </form>
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Recaudacion</th>
                <th>Porcentaje correspondiente al peaje</th>
                <th>Peajes</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $recaudacion ?? "-" }}</td>
                <td>
                    @if($peajes > 0)
                        {{ round((100 * $peajes) / $recaudacion, 2) }} %
                    @endif
                </td>
                <td>{{ $peajes ?? "-" }}</td>
            </tr>
        </tbody>
    </table>
    @stop
    