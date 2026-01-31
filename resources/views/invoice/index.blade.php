@extends('adminlte::page')

@section('title', 'Facturas')

@section('content_header')
    <div class="row">
    <h1 class="col-10">Facturas</h1>
    <!-- Keep both BS4 and BS5 attributes for compatibility -->
    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#generateModal" data-bs-toggle="modal" data-bs-target="#generateModal">Generar Factura</button>
    </div>
    @include('invoice.modals.generate')
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Número-Punto de venta</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total (Con IVA)</th>
                <th>Balance</th>
                <th>Facturado</th>
                <th>Pagada</th>
                <th>Referencia</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoices as $invoice)
                <tr>
                    <td data-search="{{ $invoice->number }}-{{ sprintf('%05d', $invoice->pointOfSale) }}" data-order="{{ $invoice->number }}">{{ number_format($invoice->number, 0, ',', '.') }}-{{ sprintf('%05d', $invoice->pointOfSale) }}</td>
                    <td> {{ $invoice->date }} </td>
                    <td>{{ $invoice->client->name }}</td>
                    <td class="text-right" data-order="{{ $invoice->totalWithIva }}">
                        $&nbsp;{{ number_format($invoice->totalWithIva, 2, ',', '.') }}</td>
                    <td class="text-right" data-order="{{ $invoice->balance }}">$&nbsp;{{ number_format($invoice->balance, 2, ',', '.') }}</td>
                    <td>{{ $invoice->invoiced }}</td>
                    <td>{{ $invoice->paid }}</td>
                    <td>{{ $invoice->reference ? $invoice->reference : '-' }}</td>
                    <td>
                        <a href="{{ Route('showInvoice', $invoice->id) }}" class="btn btn-sm btn-info">Ver</a>
                        @if($invoice->invoiced === 'NO' && $invoice->paid === 'NO')
                            <button type="button" class="btn btn-sm btn-danger btn-delete-invoice" data-id="{{ $invoice->id }}" data-toggle="modal" data-target="#confirmDeleteModal" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                Eliminar
                            </button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿Está seguro que desea eliminar esta factura? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script>
        $(document).ready(function() {
            $('.data-table').DataTable();

            // Mostrar modal si hay errores de validación del servidor
            var hasServerErrors = @json($errors->any());
            if (hasServerErrors) {
                $('#generateModal').modal('show');
            }

            // Reiniciar el modal cuando se abre.
            // Lógica: si el modal se abre por el botón (e.relatedTarget present), o
            // si NO hay errores del servidor, entonces reseteamos el formulario.
            $('#generateModal').on('show.bs.modal', function (e) {
                if (!hasServerErrors || e.relatedTarget) {
                    // Limpiar el formulario
                    var form = $('#generateModal form')[0];
                    if (form) form.reset();

                    // Remover clases de error
                    $('#generateModal .form-control').removeClass('is-invalid');

                    // Ocultar mensajes de error
                    $('#generateModal .invalid-feedback').hide();

                    // Restaurar valores por defecto
                    $('#generateModal input[name="pointOfSale"]').val(3);
                    // Vaciar número y fecha
                    $('#generateModal input[name="number"]').val('');
                    $('#generateModal input[name="date"]').val('');
                    $('#generateModal select[name="clientId"]').val('');
                }
            });

            // Forzar cierre del modal si los atributos data-dismiss/data-bs-dismiss no funcionan
            $('#generateModal').on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"], .close', function (ev) {
                // Evitar que cualquier otro handler prevenga el cierre
                ev.preventDefault();
                $('#generateModal').modal('hide');
            });
        });

        var table = new DataTable('.data-table', {
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            }
        });
        $('.select2').select2();
    </script>
    <!-- Cargar SweetAlert2 desde CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            var invoiceIdToDelete = null;

            // Helper seguro para usar SweetAlert2 si está cargado, si no usar alert() como fallback.
            function safeSwal(options) {
                if (window.Swal && typeof Swal.fire === 'function') {
                    return Swal.fire(options);
                }
                return new Promise(function (resolve) {
                    try {
                        var message = (options.title ? options.title + "\n" : "") + (options.text || "");
                        alert(message);
                    } catch (e) {
                        console.warn('safeSwal fallback alert failed', e);
                    }
                    resolve();
                });
            }

            // Cuando se hace click en el botón eliminar, guardamos el id y mostramos modal
            $(document).on('click', '.btn-delete-invoice', function (e) {
                e.preventDefault();
                invoiceIdToDelete = $(this).data('id');
                $('#confirmDeleteModal').modal('show');
            });

            // Confirmar eliminación
            $('#confirmDeleteBtn').on('click', function (e) {
                if (!invoiceIdToDelete) return;

                // Construir URL a partir de la route
                var url = '/eliminar/factura/' + invoiceIdToDelete;

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (resp) {
                        if (resp.success) {
                            // Cerrar modal
                            $('#confirmDeleteModal').modal('hide');
                            // Opcional: quitar la fila de la tabla
                            $('.btn-delete-invoice[data-id="' + invoiceIdToDelete + '"]').closest('tr').fadeOut(300, function () { $(this).remove(); });
                            // Cerrar modal (por si queda backdrop)
                            $('#confirmDeleteModal').modal('hide');
                            // Mostrar SweetAlert2 y limpiar backdrop cuando se cierre
                            safeSwal({
                                icon: 'success',
                                title: 'Eliminada',
                                text: resp.message || 'Factura eliminada correctamente.',
                                timer: 2500,
                                showConfirmButton: false
                            }).then(function () {
                                // Asegurar que no quede backdrop ni clase modal-open
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open');
                            });
                        } else {
                            // Cerrar modal y eliminar backdrop en caso de warning
                            $('#confirmDeleteModal').modal('hide');
                            safeSwal({
                                icon: 'warning',
                                title: 'No eliminada',
                                text: resp.message || 'No se pudo eliminar la factura.'
                            }).then(function () {
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open');
                            });
                        }
                    },
                    error: function (xhr) {
                        var msg = 'Error al eliminar la factura.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        // Cerrar modal y eliminar backdrop en caso de error
                        $('#confirmDeleteModal').modal('hide');
                        safeSwal({
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
            $('#confirmDeleteModal').on('hidden.bs.modal', function () {
                invoiceIdToDelete = null;
            });
        });
    </script>
@stop
