@extends('adminlte::page')

@section('title', 'Liquidaciones')

@section('content_header')
    <div class="row">
        <h1 class="col-10">Liquidacion de sueldo</h1>
    </div>
@stop

@section('content')
    <form method="GET" action="{{ route('Settlements') }}" id="formId">
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
                <table class="table table-sm table-bordered text-center data-table" >
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>N° constancia</th>
                            <th>Cliente</th>
                            <th>Chofer porcentaje</th>
                            <th>Importe neto</th>
                            <th>Base recaudacion</th>
                            <th>Peajes</th>
                            <th>Estacionamiento</th>
                            <th>Carg/Des (B)</th>
                            <th>Noche (B)</th>
                            <th>Noche (N)</th>
                            <th>Carga (N)</th>
                            {{-- <th>Chofer recaudacion N </th> --}}
                            <th>Chofer carg/Des(N)</th>
                            {{-- <th>Base de recaudacion N(B)</th> --}}
                            <th>Chofer noche(N)</th>
                            <th>Chofer(total)</th>
                            <th>Diferencia</th>
                            <th>Comentarios</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($semanas[$s] ?? [] as $tc)
                            <tr>
                                {{-- Fecha --}}
                                <td>{{ $tc['date'] }}</td>
                                {{-- N° constancia --}}
                                <td>
                                    <a href="{{ Route('showTravelCertificate', $tc['id'] ) }}">
                                    {{  $tc['id']}}
                                    </a>
                                </td>
                                {{-- Cliente --}}
                                <td>{{ $tc['client']['name'] }}</td>
                                {{-- Chofer porcentaje --}}
                                <td>
                                    <input
                                        id="driverpercent-{{ $tc['id'] }}"
                                        step="0.01"
                                        class="form-control form-control-sm input-editable"
                                        data-field="driverpercent"
                                        data-semana="{{ $s }}"
                                        data-id="{{ $tc["id"] }}"
                                        value="{{ number_format($tc["driver"]["percent"],2) }}"
                                    >
                                </td>
                                {{-- Importe neto --}}
                                <td>{{ $tc['subtotal_sin_peajes'] }}</td>
                                
                                {{-- Base recaudacion --}}
                                <td>
                                    <input
                                        id="baseRecaudacion-{{ $tc['id'] }}"
                                        step="0.01"
                                        class="form-control form-control-sm input-editable"
                                        data-field="baseRecaudacion"
                                        data-semana="{{ $s }}"
                                        data-id="{{ $tc["id"] }}"
                                        value="{{  $tc['subtotal_sin_peajes'] - $tc['totalcargadescargaB'] -$tc['totalNocheB'] }}" 
                                    > {{-- aca restar las cargas en N y noches en N  --}}
                                </td>
                                {{-- Peajes --}}
                                <td>{{ $tc['total_peajes'] }}</td>
                                {{-- -estacionamiento --}}
                                <td>{{ $tc['estacionamiento'] }}</td>
                                {{-- Carg/Des (B) --}}
                                <td>{{ $tc['totalcargadescargaB'] }}</td>
                                {{-- Noche (B) --}}
                                <td>{{ $tc['totalNocheB'] }}</td>
                                {{-- Noche (N) --}}
                                <td>{{ $tc['totalNocheN'] }}</td>
                                {{-- Carga (N) --}}
                                <td>{{ $tc['totalcargadescargaN'] }}</td>
                                {{-- Chofer carg/Des(N) --}}
                                <td>
                                    <input
                                        
                                        type="number"
                                        class="form-control form-control-sm input-editable"
                                        data-field="totalcargadescargaN"
                                        data-semana="{{ $s }}"
                                        data-id="{{ $tc['id'] }}"
                                        value="{{ $tc['totalcargadescargaN']  * 0.75}}"
                                    >
                                </td>
                                {{-- Chofer noche(N) --}}
                                <td>
                                    <input
                                        type="number"
                                        step="0.01"
                                        class="form-control form-control-sm input-editable"
                                        data-field="totalNocheN"
                                        data-semana="{{ $s }}"
                                        data-id="{{ $tc['id'] }}"
                                        value="{{ $tc['totalNocheN'] * 0.75}}"
                                    >
                                </td>
                                {{-- Chofer(total) --}}
                                <td data-cell="choferTotal">{{ number_format(($tc['driver']['percent'] / 100) * ($tc['subtotal_sin_peajes'] - $tc['totalcargadescargaB'] - $tc['totalNocheB']), 2) }}</td>
                                {{-- Diferencia se restan todas las noches y las descargas --}}
                                <td data-cell="diferencia">{{ number_format((($tc['subtotal_sin_peajes'] - $tc['totalcargadescargaB'] - $tc['totalNocheB']) * 0.25) - (($tc['driver']['percent'] / 100) * ($tc['subtotal_sin_peajes'] - $tc['totalcargadescargaB'] - $tc['totalNocheB'])), 2) }}</td>
                                {{-- Comentarios --}}
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script>
        $('#miCarrusel').on('slid.bs.carousel', function () {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
        document.addEventListener('input', function (e) {
            if (!e.target.matches('.input-editable[data-field="driverpercent"], .input-editable[data-field="baseRecaudacion"]')) return;

            const row     = e.target.closest('tr');
            const percent = parseFloat(row.querySelector('[data-field="driverpercent"]').value) || 0;
            const base    = parseFloat(row.querySelector('[data-field="baseRecaudacion"]').value) || 0;

            const choferTotal = base * (percent / 100);
            const diferencia  = (base * 0.25) - choferTotal;

            const fmt = n => n.toFixed(2);

            row.querySelector('[data-cell="choferTotal"]').textContent = fmt(choferTotal);
            row.querySelector('[data-cell="diferencia"]').textContent  = fmt(diferencia);
        });
        function validarMismoMesYAnio(fecha1, fecha2) {
            const [anio1, mes1] = fecha1.split('-');
            const [anio2, mes2] = fecha2.split('-');
            return anio1 === anio2 && mes1 === mes2;
        }
        function manejarValidacionFechas() {
            const desde = document.getElementById('desde').value;
            const hasta = document.getElementById('hasta').value;
            if (!desde || !hasta) {
                return false;
            }
            if (!validarMismoMesYAnio(desde, hasta)) {
                 Swal.fire({
                    icon: 'warning',
                    title: 'Fechas inválidas',
                    text: 'Las fechas deben pertenecer al mismo mes y año.',
                    confirmButtonText: 'Aceptar'
                });
                return false;
            }
            return true;
        }
        document.addEventListener('DOMContentLoaded', function () {
            const inputDesde = document.getElementById('desde');
            const inputHasta = document.getElementById('hasta');
            const form = document.getElementById('formId'); // <-- reemplazar por el ID real del form
            inputDesde.addEventListener('change', manejarValidacionFechas);
            inputHasta.addEventListener('change', manejarValidacionFechas);
            form.addEventListener('submit', function (e) {
                if (!manejarValidacionFechas()) {
                    e.preventDefault();
                }
            });
        });
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
                'scrollX': true,
                dom: 'Bfrtip',
                buttons: ['colvis'],
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

        $.fn.dataTable.tables().forEach(function (tableNode, idx) {
            const dt = $(tableNode).DataTable();
            const semana = idx + 1;
        payload[semana] = [];

        dt.rows().every(function () {
            const tr = this.node();

            const getInput = field => {
                const el = tr.querySelector(`[data-field="${field}"]`);
                if (!el) return null;
                return el.type === 'text' ? el.value : (parseFloat(el.value) || 0);
            };
            const getCell = cell => {
                const el = tr.querySelector(`[data-cell="${cell}"]`);
                if (!el) return 0;
                const limpio = el.textContent.trim().replace(/\./g, '').replace(',', '.');
                return parseFloat(limpio) || 0;
            };
            const getTd = i => {
                const td = tr.children[i];
                return td ? td.textContent.trim() : '';
            };
            const parseNum = txt => {
                const limpio = String(txt).trim().replace(/\./g, '').replace(',', '.');
                return parseFloat(limpio) || 0;
            };

            const id = tr.querySelector('[data-id]')?.dataset.id;
            if (!id) return;

            payload[semana].push({
                id:                  parseInt(id),
                date:                getTd(0),
                number:              getTd(1),
                cliente:             getTd(2),
                driverpercent:       getInput('driverpercent'),
                importe_neto:        parseNum(getTd(4)),
                baseRecaudacion:     getInput('baseRecaudacion'),
                total_peajes:        parseNum(getTd(6)),
                estacionamiento:     parseNum(getTd(7)),
                totalcargadescargaB: parseNum(getTd(8)),
                totalNocheB:         parseNum(getTd(9)),
                totalNocheN:         parseNum(getTd(10)),
                totalcargadescargaN: parseNum(getTd(11)),
                choferCargDescN:     getInput('totalcargadescargaN'),
                choferNocheN:        getInput('totalNocheN'),
                choferTotal:         getCell('choferTotal'),
                diferencia:          getCell('diferencia'),
                comentarios:         getInput('comentarios'),
            });
        });
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
        console.error(err.message);
        alert('Error: ' + err.message);
    });
});
    </script>
@stop