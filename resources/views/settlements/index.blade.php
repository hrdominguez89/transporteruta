@extends('adminlte::page')

@section('title', 'Liquidaciones')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Liquidaciones</h1>
    </div>
@stop

@section('content')
   <form method="GET" action="{{ route('Settlements') }}">
        <div class="container-fluid mb-3" >
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label for="driver_id">Chofer</label>
                    <select name="driver_id" id="driver_id" class="form-control">
                        <option value="">-- Todos --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="d-block">Tipo de periodo</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" value="mes" name="periodo" id="periodo_mes" checked>
                        <label class="form-check-label" for="periodo_mes">Mes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" value="toFrom" name="periodo" id="periodo_toFrom">
                        <label class="form-check-label" for="periodo_toFrom">Desde / Hasta</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="desde">Desde</label>
                    <input type="date" name="desde" id="desde" class="form-control" value="{{ request('desde') }}">
                </div>

                <div class="col-md-2">
                    <label for="hasta">Hasta</label>
                    <input type="date" name="hasta" id="hasta" class="form-control" value="{{ request('hasta') }}">
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-block">Generar vista previa</button>
                </div>
            </div>
        </div>
    </form>
    <form method="GET" action="{{ route('SettlementsExcel') }}">
        <div class="col-md-1">
            <button type="button" id="btn-excel" class="btn btn-success btn-block">
                <i class="fas fa-file-excel"></i> Excel
            </button>
        </div>
    </form>


    <div id="miCarrusel" class="carousel slide" data-bs-ride="false" data-bs-interval="false"  >
        <div class="carousel-indicators">
            <button type="button" class="btn btn-primary active"data-bs-target="#miCarrusel" data-bs-slide-to="0">1</button>
            <button type="button" class="btn btn-primary "data-bs-target="#miCarrusel" data-bs-slide-to="1">2</button>
            <button type="button" class="btn btn-primary "data-bs-target="#miCarrusel" data-bs-slide-to="2">3</button>
            <button type="button" class="btn btn-primary "data-bs-target="#miCarrusel" data-bs-slide-to="3">4</button>
            <button type="button" class="btn btn-primary "data-bs-target="#miCarrusel" data-bs-slide-to="4">5</button>
        </div>
        <div class="carousel-inner">
            @for ($s = 1; $s <= 5; $s++)
            <div class="carousel-item {{ $s === 1 ? 'active' : '' }}" style="transition: 0.3s">
                <br>
                <h4>Semana {{ $s }}</h4>
                <table class="table table-sm table-bordered text-center data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>N° constancia</th>
                            <th>Cliente</th>
                            <th>Chofer porcentaje</th>
                            <th>Importe neto</th>
                            <th>Peajes</th>
                            <th>Chofer(total)</th>
                            <th>Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($semanas[$s] ?? [] as $tc)
                            <tr>
                                <td>{{ $tc->date }}</td>
                                <td>{{ $tc->number ?? $tc->id }}</td>
                                <td>{{ $tc->client->name }}</td>
                                <td>{{ number_format($tc->driver->percent, 2, ',', '.') }}%</td>
                                <td>{{ $tc->subtotal_sin_peajes }}</td>
                                <td>{{ $tc->totalpeajes }}</td>
                                <td>{{ ($tc->driver->percent/100) * ($tc->subtotal_sin_peajes)}}</td>
                                <td>{{ ($tc->subtotal_sin_peajes * 0.25) - (($tc->driver->percent/100) * $tc->subtotal_sin_peajes) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endfor
        </div>
    </div>
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

        $('input[name="periodo"]').on('change', function () {
            const esMes = $(this).val() === 'mes';
            $('#desde, #hasta').prop('disabled', esMes);
            if (esMes) $('#desde, #hasta').val('');
        });

        
        $('#desde, #hasta').prop('disabled', true);

        $('#btn-excel').on('click', function () {
            const currentParams = window.location.search;
            window.location.href = '{{ route('SettlementsExcel') }}' + currentParams;
        });
    </script>
@stop
