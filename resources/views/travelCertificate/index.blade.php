@extends('adminlte::page')

@section('title', 'Constancias de Viaje')

@section('content_header')
    <div class="row">
        <h1 class="col-9">Constancias de Viaje</h1>
        <button class="btn btn-sm btn-danger col-2" data-toggle="modal" data-target="#storeModal">Agregar Constancia</button>
    </div>
    @include('travelCertificate.modals.store')
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Nro. Nuevo</th>
                <th>Nro. Antiguo</th>
                <th>Cliente</th>
                <th>Chofer</th>
                <th>Facturada</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($travelCertificates as $travelCertificate)
                <tr>
                    <td data-search="{{ $travelCertificate->id }}" data-order="{{ $travelCertificate->id }}">{{ number_format($travelCertificate->id, 0, ',', '.') }}
                    </td>
                    <td data-search="{{ $travelCertificate->number }}" data-order="{{ $travelCertificate->number }}">
                        {{ $travelCertificate->number ? number_format($travelCertificate->number, 0, ',', '.') : ' - ' }}
                    </td>
                    <td>{{ $travelCertificate->client->name }}</td>
                    <td>{{ $travelCertificate->driver->name }}</td>
                    <td>{{ $travelCertificate->invoiced }}</td>
                    <td>
                            <a href="{{ Route('showTravelCertificate', $travelCertificate->id) }}" class="btn btn-sm btn-info">Ver</a>
                            @if($travelCertificate->invoiced == 'NO')
                                <button class="btn btn-sm btn-danger btn-delete-travelcertificate" data-id="{{ $travelCertificate->id }}" data-toggle="modal" data-target="#confirmDeleteTravelCertificateModal" data-bs-toggle="modal" data-bs-target="#confirmDeleteTravelCertificateModal">Eliminar</button>
                            @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

        <!-- Modal de confirmación de eliminación de constancia de viaje -->
        <div class="modal fade" id="confirmDeleteTravelCertificateModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteTravelCertificateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteTravelCertificateModalLabel">Confirmar eliminación</h5>
                        <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ¿Está seguro que desea eliminar esta constancia de viaje? Esta acción borrará sus items y relaciones y no se puede deshacer.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteTravelCertificateBtn">Eliminar</button>
                    </div>
                </div>
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            var travelCertificateIdToDelete = null;

            // Abrir modal al click en eliminar
            $(document).on('click', '.btn-delete-travelcertificate', function (e) {
                e.preventDefault();
                travelCertificateIdToDelete = $(this).data('id');
                $('#confirmDeleteTravelCertificateModal').modal('show');
            });

            // Confirmar eliminación
            $('#confirmDeleteTravelCertificateBtn').on('click', function () {
                if (!travelCertificateIdToDelete) return;

                var url = '/eliminar/constancia-de-viaje/' + travelCertificateIdToDelete;

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (resp) {
                        $('#confirmDeleteTravelCertificateModal').modal('hide');
                        if (resp.success) {
                            // remove row from table
                            $('.btn-delete-travelcertificate[data-id="' + travelCertificateIdToDelete + '"]').closest('tr').fadeOut(300, function () { $(this).remove(); });
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminada',
                                text: resp.message || 'Constancia de viaje eliminada correctamente.',
                                timer: 2200,
                                showConfirmButton: false
                            }).then(function () {
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open');
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'No eliminada',
                                text: resp.message || 'No se pudo eliminar la constancia de viaje.'
                            }).then(function () {
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open');
                            });
                        }
                    },
                    error: function (xhr) {
                        $('#confirmDeleteTravelCertificateModal').modal('hide');
                        var msg = 'Error al eliminar la constancia de viaje.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        }).then(function () {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                        });
                    }
                });
            });

            // Reset id when modal closes
            $('#confirmDeleteTravelCertificateModal').on('hidden.bs.modal', function () {
                travelCertificateIdToDelete = null;
            });
        });
    </script>
@stop
