@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <div class="row align-items-center">
        <h1 class="col mb-0">Clientes</h1>
        <div class="col-auto d-flex">
           @if (auth()->user()->isSuperAdmin())
            <button type="button" class="btn btn-primary mr-2" data-bs-toggle="modal" data-bs-target="#modalConfigNotif">
                Panel de control
            </button>
            <button type="button" class="btn btn-warning mr-2" id="btnNotificarAhora">Notificar ahora</button>

            <form id="formNotificarAhora" action="{{ route('notificar.ahora') }}" method="POST">
                @csrf
            </form>

            <div class="modal fade" id="modalConfigNotif" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="formConfigNotif" action="{{ route('config.notificaciones.update') }}" method="POST">
                            @csrf
                            @method('POST')

                            <div class="modal-header">
                                <h5 class="modal-title">Configuración de notificaciones</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3 form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="automatico" name="automatico" value="1"
                                        @checked($config->automatico)>
                                    <label class="form-check-label" for="automatico">Automático</label>
                                </div>

                                <div class="mb-3">
                                    <label for="dia" class="form-label">Día de la semana</label>
                                    <select class="form-select" id="dia" name="dia">
                                        <option value="0" @selected($config->dia == 0)>Domingo</option>
                                        <option value="1" @selected($config->dia == 1)>Lunes</option>
                                        <option value="2" @selected($config->dia == 2)>Martes</option>
                                        <option value="3" @selected($config->dia == 3)>Miércoles</option>
                                        <option value="4" @selected($config->dia == 4)>Jueves</option>
                                        <option value="5" @selected($config->dia == 5)>Viernes</option>
                                        <option value="6" @selected($config->dia == 6)>Sábado</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="hora" class="form-label">Hora</label>
                                    <input type="time" class="form-control" id="hora" name="hora"
                                        value="{{ $config->hora }}">
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="btnGuardarConfig">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

          
        @endif
            <a href="{{ Route('generateDebtorsPdf') }}" class="btn btn-sm btn-info mr-2">Reporte Deudores</a>
            <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#storeModal">Agregar Cliente</button>
        </div>
    </div>
    @include('client.modals.store')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th class="text-center">Nombre</th>
                <th class="text-center">DNI/CUIT</th>
                <th class="text-center">Saldo</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($clients as $client)
                <tr>
                    <td class="text-center">{{ $client->name }}</td>
                    <td class="text-center">{{ $client->dni }}</td>
                    @if ($client->balance > 0)
                        <td class="bg-danger text-center" data-order="{{ $client->balance }}">
                            $&nbsp;{{ number_format($client->balance, 2, ',', '.') }}</td>
                    @else
                        <td class="text-right" data-order="{{ $client->balance }}">
                            $&nbsp;{{ number_format($client->balance, 2, ',', '.') }}</td>
                    @endif
                    <td class="text-center">
                        <a href="{{ Route('showClient', $client->id) }}" class="btn btn-sm btn-info">Ver</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                document.getElementById('btnGuardarConfig').addEventListener('click', function () {
                    Swal.fire({
                        title: '¿Confirmar cambios?',
                        text: 'Se actualizará la configuración de notificaciones.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('formConfigNotif').submit();
                        }
                    });
                });
            </script>
            <script>
    document.getElementById('btnNotificarAhora').addEventListener('click', function () {
        Swal.fire({
            title: '¿Notificar ahora?',
            text: 'Se enviarán los mails de facturas a los destinatarios.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, notificar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formNotificarAhora').submit();
            }
        });
    });
</script>
@stop
