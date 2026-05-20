@extends('adminlte::page')

@section('title', 'Liquidación')

@section('content_header')
    <div class="row align-items-center">
        <div class="col-8">
            <h1>Liquidación · {{ $settlement->driver->name }} · {{ $settlement->periodo->format('m/Y') }}</h1>
        </div>
        <div class="col-4 text-right">
            <a href="{{ route('Settlements') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-auto">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-auto">{{ session('warning') }}</div>
    @endif

    <div class="container-fluid mb-3">
    <div class="row align-items-end">
        <div class="col-md-1">
            <label class="d-block">&nbsp;</label>
            <button type="button" id="btn-guardar" class="btn btn-sm btn-secondary btn-block">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
        <div class="col-md-1">
            <label class="d-block">&nbsp;</label>
            <button type="button" id="btn-excel" class="btn btn-sm btn-success btn-block">
                <i class="fas fa-file-excel"></i> Generar Excel
            </button>
        </div>
        @if($ultimaSemanaCargada < 5)
        <div class="col-md-4">
            <form action="{{ route('Settlements.siguienteSemana', $settlement) }}" method="POST" class="m-0">
                @csrf
                <label for="semana" class="d-block">Semana N°</label>
                <div class="d-flex align-items-center">
                    <select name="semana" id="semana" class="form-control mr-2" style="width: auto;">
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">
                        Generar semana
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>

    <div id="miCarrusel" class="carousel slide" data-bs-ride="false" data-bs-interval="false">
        <div class="carousel-indicators">
            @foreach ($semanas as $numSemana => $detalles)
                <button type="button" class="btn btn-primary {{ $loop->first ? 'active' : '' }}"
                    data-bs-target="#miCarrusel" data-bs-slide-to="{{ $loop->index }}">{{ $numSemana }}</button>
            @endforeach
        </div>
        <div class="carousel-inner">
            @foreach ($semanas as $numSemana => $detalles)
                <div class="carousel-item {{ $loop->first ? 'active' : '' }}" style="transition: 0.3s">
                    <br>
                    <h4>Semana {{ $numSemana }}</h4>
                    <table class="table table-sm table-bordered text-center data-table">
                        <thead>
                            <tr class ="bg-danger">
                                <th>Fecha</th>
                                <th>N° constancia</th>
                                <th>Cliente</th>
                                <th>Chofer %</th>
                                <th>Importe neto</th>
                                <th>Base recaudacion</th>
                                <th>Peajes</th>
                                <th>Estacionamiento</th>
                                <th>Carg/Des (B)</th>
                                <th>Noche (B)</th>
                                <th>Noche (N)</th>
                                <th>Carga (N)</th>
                                <th>Base recaudacion N</th>
                                <th>Chofer recaudacion N</th>
                                <th>Chofer carg/Des(N)</th>
                                <th>Chofer noche(N)</th>
                                <th>Chofer (total)</th>
                                <th>Diferencia</th>
                                <th>Comentarios</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detalles as $detalle)
                                <tr data-detail-id="{{ $detalle->id }}">
                                    <td>{{ optional($detalle->fecha)->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('showTravelCertificate', $detalle->travel_certificate_id) }}">
                                            {{ $detalle->travel_certificate_id }}
                                        </a>
                                    </td>
                                    <td>{{ $detalle->client?->name }}</td>
                                    <td>
                                        <input
                                            type="number" step="0.01"
                                            class="form-control form-control-sm input-editable"
                                            data-field="chofer_porcentaje"
                                            value="{{ $detalle->chofer_porcentaje }}">
                                    </td>
                                    <td data-cell="importe_neto">{{ $detalle->importe_neto }}</td>
                                    <td>
                                        <input
                                            type="number" step="0.01"
                                            class="form-control form-control-sm input-editable"
                                            data-field="base_recaudacion"
                                            value="{{ $detalle->base_recaudacion ?? ($detalle->importe_neto - $detalle->carga_descarga_b - $detalle->noche_b - $detalle->carga_descarga_n - $detalle->noche_n) }}">
                                    </td>
                                    <td>{{ $detalle->peajes }}</td>
                                    <td>{{ $detalle->estacionamiento }}</td>
                                    <td>{{ $detalle->carga_descarga_b }}</td>
                                    <td>{{ $detalle->noche_b }}</td>
                                    <td data-cell="noche_n">{{ $detalle->noche_n }}</td>
                                    <td data-cell="carga_descarga_n">{{ $detalle->carga_descarga_n }}</td>
                                    <td>
                                        <input
                                            type="number" step="0.01"
                                            class="form-control form-control-sm input-editable"
                                            data-field="base_recaudacion_n"
                                            value="{{ $detalle->base_recaudacion_n ?? 0 }}">
                                    </td>
                                    <td>
                                        <input
                                            type="number" step="0.01"
                                            class="form-control form-control-sm input-editable"
                                            data-field="chofer_n"
                                            value="{{ $detalle->chofer_n ?? 0 }}">
                                    </td>
                                    <td>
                                        <input
                                            type="number" step="0.01"
                                            class="form-control form-control-sm input-editable"
                                            data-field="chofer_cd_n"
                                            value="{{ $detalle->chofer_cd_n ?? ($detalle->carga_descarga_n * 0.75) }}">
                                    </td>
                                    <td>
                                        <input
                                            type="number" step="0.01"
                                            class="form-control form-control-sm input-editable"
                                            data-field="chofer_n_n"
                                            value="{{ $detalle->chofer_n_n ?? ($detalle->noche_n * 0.75) }}">
                                    </td>
                                    <td data-cell="chofer_total">{{ $detalle->chofer_total ?? number_format(($detalle->chofer_porcentaje / 100) * ($detalle->importe_neto - $detalle->carga_descarga_b - $detalle->noche_b), 2, '.', '') }}</td>
                                    <td data-cell="diferencia">{{ $detalle->diferencia ?? 0 }}</td>
                                    <td>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm input-editable"
                                            data-field="comentarios"
                                            value="{{ $detalle->comentarios ?? '' }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        setTimeout(() => {
            document.querySelectorAll('.alert-auto').forEach(el => $(el).fadeOut());
        }, 3000);
        const URL_GUARDAR = '{{ route('guardarEdicion', $settlement) }}';
        const URL_EXCEL   = '{{ route('SettlementsExcel', $settlement) }}';
        const CSRF        = '{{ csrf_token() }}';

        $('#miCarrusel').on('slid.bs.carousel', function () {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });

        // Recalcula chofer_total y diferencia en tiempo real
        document.addEventListener('input', function (e) {
            if (!e.target.matches('.input-editable[data-field="chofer_porcentaje"], .input-editable[data-field="base_recaudacion"], .input-editable[data-field="chofer_n"]')) return;

            const row = e.target.closest('tr');
            const get = sel => parseFloat(row.querySelector(sel)?.value ?? row.querySelector(sel)?.textContent) || 0;

            const percent     = parseFloat(row.querySelector('[data-field="chofer_porcentaje"]').value) || 0;
            const base        = parseFloat(row.querySelector('[data-field="base_recaudacion"]').value) || 0;
            const importeNeto = parseFloat(row.querySelector('[data-cell="importe_neto"]').textContent) || 0;
            const choferN     = parseFloat(row.querySelector('[data-field="chofer_n"]').value) || 0;
            const cargaN      = parseFloat(row.querySelector('[data-cell="carga_descarga_n"]').textContent) || 0;
            const nocheN      = parseFloat(row.querySelector('[data-cell="noche_n"]').textContent) || 0;

            const choferTotal = base * (percent / 100);
            const diferencia  = (importeNeto * 0.25) - choferTotal - choferN - cargaN - nocheN;

            row.querySelector('[data-cell="chofer_total"]').textContent = choferTotal.toFixed(2);
            row.querySelector('[data-cell="diferencia"]').textContent   = diferencia.toFixed(2);
        });

        // Junta todos los detalles de todas las semanas
        function recolectarDetalles() {
            const detalles = [];
            document.querySelectorAll('tr[data-detail-id]').forEach(tr => {
                const detalle = { id: parseInt(tr.dataset.detailId) };
                tr.querySelectorAll('.input-editable').forEach(input => {
                    const field = input.dataset.field;
                    detalle[field] = input.type === 'text' ? input.value : (parseFloat(input.value) || 0);
                });
                detalle.chofer_total = parseFloat(tr.querySelector('[data-cell="chofer_total"]').textContent) || 0;
                detalle.diferencia   = parseFloat(tr.querySelector('[data-cell="diferencia"]').textContent) || 0;
                detalles.push(detalle);
            });
            return detalles;
        }

        function guardar() {
            return fetch(URL_GUARDAR, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ detalles: recolectarDetalles() })
            }).then(res => {
                if (!res.ok) throw new Error('Error al guardar.');
                return res.json();
            });
        }

        $('#btn-guardar').on('click', function () {
            guardar()
                .then(() => Swal.fire({ icon: 'success', title: 'Guardado', timer: 1500, showConfirmButton: false }))
                .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
        });

        $('#btn-excel').on('click', function () {
            guardar()
                .then(() => { window.location = URL_EXCEL; })
                .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
        });

        $(document).ready(function () {
            $('.data-table').DataTable({
                scrollX: true,
                dom: 'Bfrtip',
                buttons: ['colvis'],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' }
            });
        });
    </script>
@stop