@extends('adminlte::page')

@section('title', 'Liquidaciones')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Liquidaciones</h1>
    </div>
@stop

@section('content')
    <form method="GET" action="{{ route('Settlements') }}">
        <div class="container-fluid mb-3">
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

    <div class="container-fluid mb-3">
        <div class="row">
            <div class="col-md-1">
                <button type="button" id="btn-excel" class="btn btn-success btn-block">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
            </div>
        </div>
    </div>

    <div id="miCarrusel" class="carousel slide" data-bs-ride="false" data-bs-interval="false">
        <div class="carousel-indicators">
            @for ($i = 1; $i <= 5; $i++)
                <button type="button" class="btn btn-primary {{ $i === 1 ? 'active' : '' }}"
                    data-bs-target="#miCarrusel" data-bs-slide-to="{{ $i - 1 }}">{{ $i }}</button>
            @endfor
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
                            <th>Recaudacion</th>
                            <th>Peajes</th>
                            <th>Carga (B)</th>
                            <th>Noche (B)</th>
                            <th>Noche (N)</th>
                            <th>Carga (N)</th>
                            <th>Chofer carg/Des(B)</th>
                            <th>Chofer carg/Des(N)</th>
                            <th>Chofer noche(B)</th>
                            <th>Chofer noche(N)</th>
                            <th>Chofer(total)</th>
                            <th>Diferencia</th>
                            <th>Comentarios</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($semanas[$s] ?? [] as $tc)
                            <tr>
                                <td>{{ $tc['date'] }}</td>
                                <td>
                                    <a href="{{ Route('showTravelCertificate', $tc['id'] ) }}">
                                    {{  $tc['id']}}
                                    </a>
                                </td>
                                <td>{{ $tc['client']['name'] }}</td>
                                <td>
                                    @if($driver->type == 'PROPIO')
                                    25 %
                                    @else
                                    20 %
                                    @endif
                                </td>
                                <td>{{ $tc['subtotal_sin_peajes'] }}</td>
                                <td>{{ $tc['subtotal_sin_peajes'] - $tc['totalcargadescargaB'] -$tc['totalNocheB'] }}</td>
                                <td>{{ $tc['total_peajes'] }}</td>
                                <td>{{ $tc['totalcargadescargaB'] }}</td>
                                <td>{{ $tc['totalNocheB'] }}</td>
                                <td>{{ $tc['totalNocheB'] }}</td>
                                <td>{{ $tc['totalcargadescargaB'] }}</td>
                                <td>
                                    <input
                                        type="number"
                                        step="0.01"
                                        class="form-control form-control-sm input-editable"
                                        data-field="totalcargadescargaB"
                                        data-semana="{{ $s }}"
                                        data-id="{{ $tc['id'] }}"
                                        value="{{ $tc['totalcargadescargaB'] }}"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        step="0.01"
                                        class="form-control form-control-sm input-editable"
                                        data-field="totalcargadescargaN"
                                        data-semana="{{ $s }}"
                                        data-id="{{ $tc['id'] }}"
                                        value="{{ $tc['totalcargadescargaN'] }}"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        step="0.01"
                                        class="form-control form-control-sm input-editable"
                                        data-field="totalNocheB"
                                        data-semana="{{ $s }}"
                                        data-id="{{ $tc['id'] }}"
                                        value="{{ $tc['totalNocheB'] }}"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        step="0.01"
                                        class="form-control form-control-sm input-editable"
                                        data-field="totalNocheN"
                                        data-semana="{{ $s }}"
                                        data-id="{{ $tc['id'] }}"
                                        value="{{ $tc['totalNocheN'] }}"
                                    >
                                </td>
                                <td>{{ ($tc['driver']['percent'] / 100) * $tc['subtotal_sin_peajes'] }}</td>
                                <td>{{ ( ($tc['subtotal_sin_peajes'] - $tc['cargaDescargaNocheB'])* 0.25) - (0.25) *  ($tc['subtotal_sin_peajes'] - $tc['cargaDescargaNocheB']) }}</td>
                              <td>
                                <input
                                    type="text"
                                    class="form-control form-control-sm input-editable"
                                    data-field="comentarios"
                                    data-semana="{{ $s }}"
                                    data-id="{{ $tc['id'] }}"
                                    value="{{ $tc['comentarios'] ?? '' }}"
                                >
                            </td>
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
        const estadoOriginal = @json($semanas);

        const estado = {};
        Object.entries(estadoOriginal).forEach(([semana, viajes]) => {
            viajes.forEach(viaje => {
                estado[viaje.id] = { ...viaje, semana: parseInt(semana) };
            });
        });

        $(document).on('change', '.input-editable', function () {
            const id    = $(this).data('id');
            const field = $(this).data('field');
            const raw   = $(this).val();
            const valor = $(this).attr('type') === 'text' ? raw : (parseFloat(raw) || 0);

            if (estado[id] !== undefined) {
                estado[id][field] = valor;
            }
        });

        $(document).ready(function () {
            $('.data-table').DataTable({
                'scrollX': true ,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' }
            });
        });

        $('.select2').select2();

        $('input[name="periodo"]').on('change', function () {
            const esMes = $(this).val() === 'mes';
            $('#desde, #hasta').prop('disabled', esMes);
            if (esMes) $('#desde, #hasta').val('');
        });
        $('#desde, #hasta').prop('disabled', true);

        $('#btn-excel').on('click', function () {
            const payload = {};
            Object.values(estado).forEach(viaje => {
                const s = viaje.semana;
                if (!payload[s]) payload[s] = [];
                payload[s].push(viaje);
            });

            fetch('{{ route('SettlementsExcel') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ semanas: payload })
            })
            .then(res => {
                if (!res.ok) throw new Error('Error al generar el Excel.');
                return res.blob();
            })
            .then(blob => {
                const url = URL.createObjectURL(blob);
                const a   = document.createElement('a');
                a.href    = url;
                a.download = 'liquidacion.xlsx';
                a.click();
                URL.revokeObjectURL(url);
            })
            .catch(err => {
                console.error(err.message); // ver en consola el detalle real
                alert('Error: ' + err.message);
            });
        });
    </script>
@stop