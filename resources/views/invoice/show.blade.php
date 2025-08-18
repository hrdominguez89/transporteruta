@extends('adminlte::page')

@section('title', 'Facturas')

@section('content_header')
    <div class="row">

        <div class="col-12">
            <a href="{{ Route('invoices') }}" class="btn btn-sm btn-secondary">Volver</a>
        </div>
        <div class="col-12 mt-3">
            <h1>Factura N°
                <strong>{{ number_format($invoice->number, 0, ',', '.') }}-{{ sprintf('%05d', $invoice->pointOfSale) }}</strong>
            </h1>
        </div>

        @if ($invoice->invoiced == 'SI' and $invoice->paid == 'NO')
            <div class="col-12 text-right mb-2">
                <button class="btn btn-sm btn-danger col-2 mr-2" data-toggle="modal"
                    data-target="#cancelModal{{ $invoice->id }}">Anular Factura</button>
                <a target="_blank" href="{{ Route('invoicePdf', $invoice->id) }}" class="btn btn-sm btn-info col-2">Generar
                    PDF</a>
            </div>
        @elseif($invoice->invoiced == 'NO')
            <div class="col-12 text-right mb-2">
                <button class="btn btn-sm btn-primary col-4" data-toggle="modal"
                    data-target="#invoicedModal{{ $invoice->id }}">Facturar</button>
            </div>
        @endif
        @if ($invoice->paid == 'SI')
            <div class="col-12 text-right mb-2">

                <a target="_blank" href="{{ Route('invoicePdf', $invoice->id) }}" class="btn btn-sm btn-info col-4">Generar
                    PDF</a>
            </div>
            <div class="col-12 text-left mb-2">
                <h5 class="text-danger">La factura se marco como pagada y se desconto el saldo de la cuenta corriente</h5>
            </div>
        @endif
    </div>
    @include('invoice.modals.invoiced')
    @include('invoice.modals.cancel')
@stop

@section('content')
    <table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total (Sin IVA)</th>
                <th>IVA</th>
                <th>Peajes</th>
                <th>Balance</th>
                <th>Facturado</th>
                <th>Total (Con IVA)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}</td>
                <td>
                    <a target="_blank"
                        href="{{ Route('showClient', $invoice->client->id) }}">{{ $invoice->client->name }}</a>
                </td>
                <td>$&nbsp;{{ number_format($invoice->total, 2, ',', '.') }}</td>
                <td>$&nbsp;{{ number_format($invoice->iva, 2, ',', '.') }}</td>
                <td>$&nbsp;{{ number_format($totalTolls, 2, ',', '.') }}</td>
                <td>$&nbsp;{{ number_format($invoice->balance, 2, ',', '.') }}</td>
                <td>{{ $invoice->invoiced }}</td>
                <td>$&nbsp;{{ number_format($invoice->total + $invoice->iva, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    <h4>Notas de Credito</h4>
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
                <th>Fecha</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->credits as $credit)
                <tr>
                    <td>
                        <a target="_blank" href="{{ Route('showCredit', $credit->id) }}">{{ $credit->number }}</a>
                    </td>
                    <td data-order="{{ \Carbon\Carbon::parse($credit->date)->timestamp }}">
                        {{ \Carbon\Carbon::parse($credit->date)->format('d/m/Y') }}</td>
                    <td data-order="{{ $credit->total }}">$&nbsp;{{ number_format($credit->total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <h4>Constancias de Viaje Agregadas</h4>
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                @if ($invoice->invoiced == 'NO')
                    <th>Seleccionar</th>
                @endif
                <th>Nro. Nuevo</th>
                <th>Nro. Antiguo</th>
                <th>Chofer</th>
                <th>Precio Neto</th>
                <th>I.V.A.</th>
                <th>Peajes</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->travelCertificates as $travelCertificate)
                <tr>
                    @if ($invoice->invoiced == 'NO')
                        <td>
                            <input type="checkbox" class="bulk-select bulk-select-added"
                                data-id="{{ $travelCertificate->id }}">
                        </td>
                    @endif
                    <td data-order="{{ $travelCertificate->id }}">
                        <a target="_blank"
                            href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">{{ number_format($travelCertificate->id, 0, ',', '.') }}</a>
                    </td>
                    <td data-order="{{ $travelCertificate->number }}">
                        <a target="_blank"
                            href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">{{ number_format($travelCertificate->number, 0, ',', '.') }}</a>
                    </td>
                    <td>{{ $travelCertificate->driver->name }}</td>
                    {{-- precio neto --}}
                    <td data-order="{{ $travelCertificate->importeNeto }}">
                        $&nbsp;{{ number_format($travelCertificate->importeNeto, 2, ',', '.') }}</td>
                    {{-- i.v.a. --}}
                    <td data-order="{{ $travelCertificate->iva }}">
                        $&nbsp;{{ number_format($travelCertificate->iva, 2, ',', '.') }}</td>
                    {{-- peajes --}}
                    <td data-order="{{ $travelCertificate->peajes }}">
                        $&nbsp;{{ number_format($travelCertificate->peajes, 2, ',', '.') }}</td>
                    {{-- total --}}
                    <td
                        data-order="{{ $travelCertificate->importeNeto + $travelCertificate->iva + $travelCertificate->peajes }}">
                        $&nbsp;{{ number_format($travelCertificate->importeNeto + $travelCertificate->iva + $travelCertificate->peajes, 2, ',', '.') }}
                    </td>
                    <td>
                        @if ($invoice->invoiced == 'NO')
                            <form action="{{ Route('removeFromInvoice', $travelCertificate->id) }}" method="POST"
                                class="prevent-double-submit">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="invoiceId" value="{{ $invoice->id }}">
                                <button type="submit" class="btn btn-sm btn-warning btn-submit-once">Quitar de la
                                    Factura</button>
                            </form>
                        @else
                            <strong class="text-danger">¡No se pueden realizar cambios!</strong>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($invoice->invoiced == 'NO')
        <div class="mb-2">
            <button id="bulk-remove-btn" class="btn btn-sm btn-warning btn-submit-once" disabled>Quitar seleccionados de la
                Factura</button>
        </div>
    @endif
    <br>
    @if ($invoice->invoiced == 'NO')
        <h4>Constancias de Viaje del Cliente sin Liquidar</h4>
        <table class="table table-sm table-bordered text-center data-table">
            <thead class="bg-danger">
                <tr>
                    <th>Seleccionar</th>
                    <th>Nro. Nuevo</th>
                    <th>Nro. Antiguo</th>
                    <th>Chofer</th>
                    <th>Precio Neto</th>
                    <th>I.V.A.</th>
                    <th>Peajes</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clients->travelCertificates as $travelCertificate)
                    @if ($travelCertificate->invoiceId != $invoice->id and $travelCertificate->invoiced == 'NO')
                        <tr>
                            <td>
                                <input type="checkbox" class="bulk-select bulk-select-available"
                                    data-id="{{ $travelCertificate->id }}">
                            </td>
                            <td data-order="{{ $travelCertificate->id }}">
                                <a target="_blank"
                                    href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">{{ number_format($travelCertificate->id, 0, ',', '.') }}</a>
                            </td>
                            <td data-order="{{ $travelCertificate->number }}">
                                <a target="_blank"
                                    href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">{{ number_format($travelCertificate->number, 0, ',', '.') }}</a>
                            </td>
                            <td>{{ $travelCertificate->driver->name }}</td>
                            {{-- precio neto --}}
                            <td data-order="{{ $travelCertificate->importeNeto }}">
                                $&nbsp;{{ number_format($travelCertificate->importeNeto, 2, ',', '.') }}</td>
                            {{-- i.v.a. --}}
                            <td data-order="{{ $travelCertificate->iva }}">
                                $&nbsp;{{ number_format($travelCertificate->iva, 2, ',', '.') }}</td>
                            {{-- peajes --}}
                            <td data-order="{{ $travelCertificate->peajes }}">
                                $&nbsp;{{ number_format($travelCertificate->peajes, 2, ',', '.') }}</td>
                            {{-- total --}}
                            <td
                                data-order="{{ $travelCertificate->importeNeto + $travelCertificate->iva + $travelCertificate->peajes }}">
                                $&nbsp;{{ number_format($travelCertificate->importeNeto + $travelCertificate->iva + $travelCertificate->peajes, 2, ',', '.') }}
                            </td>
                            <td>
                                <form action="{{ Route('addToInvoice', $travelCertificate->id) }}" method="POST"
                                    class="prevent-double-submit">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="invoiceId" value="{{ $invoice->id }}">
                                    <button type="submit" class="btn btn-sm btn-success btn-submit-once">Agregar a la
                                        Factura</button>
                                </form>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div class="mb-2">
            <button id="bulk-add-btn" class="btn btn-sm btn-success btn-submit-once" disabled>Agregar seleccionados a la
                Factura</button>
        </div>
    @endif
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
        // Prevent double form submit: disable submit buttons on click and on submit
        (function() {
            // when a button with .btn-submit-once is clicked, disable it and submit its form
            $(document).on('click', '.btn-submit-once', function(e) {
                var $btn = $(this);
                // if button already disabled, prevent further action
                if ($btn.prop('disabled')) {
                    e.preventDefault();
                    return;
                }
                // find parent form (don't require the prevent-double-submit class to be present)
                var $form = $btn.closest('form');
                // prepare UI
                $btn.data('original-text', $btn.html());
                $btn.html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...'
                    );

                // disable all submit buttons in the form (or just this button if no form)
                if ($form.length) {
                    // prevent default to ensure disabling doesn't block submission, then submit programmatically
                    e.preventDefault();
                    $form.find('button[type="submit"]').each(function() {
                        $(this).prop('disabled', true);
                    });
                    // use native submit to avoid timing issues where disabling the button prevents the native submit
                    try {
                        $form[0].submit();
                    } catch (err) {
                        // fallback: trigger jQuery submit
                        $form.trigger('submit');
                    }
                } else {
                    // no form found, just disable the button and let default behavior proceed
                    $btn.prop('disabled', true);
                }
            });

            // as a backup, on submit disable buttons (covers programmatic submits)
            $(document).on('submit', 'form.prevent-double-submit', function(e) {
                var $form = $(this);
                // disable all submit buttons to avoid duplicates
                $form.find('button[type="submit"]').each(function() {
                    var $b = $(this);
                    $b.prop('disabled', true);
                    if (!$b.data('original-text')) {
                        $b.data('original-text', $b.html());
                        $b.html(
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
                    }
                });
                return true;
            });
        })();

        // Bulk select handling: enable bulk buttons and submit selected ids
        (function() {
            function getCsrfToken() {
                var token = $('meta[name="csrf-token"]').attr('content');
                if (token) return token;
                // fallback: try to read from a hidden input in page
                var input = $('input[name="_token"]').first();
                return input.length ? input.val() : '';
            }

            function collectIds(selector) {
                var ids = [];
                $(selector).each(function() {
                    if ($(this).is(':checked')) ids.push($(this).data('id'));
                });
                return ids;
            }

            // toggle bulk buttons depending on which table's checkboxes changed
            $(document).on('change', '.bulk-select', function() {
                var anyAvailable = $('.bulk-select-available:checked').length > 0;
                var anyAdded = $('.bulk-select-added:checked').length > 0;
                // enable add button only when any available is selected
                $('#bulk-add-btn').prop('disabled', !anyAvailable);
                // enable remove button only when any added is selected
                $('#bulk-remove-btn').prop('disabled', !anyAdded);
            });

            // submit selected ids to bulk add route
            $(document).on('click', '#bulk-add-btn', function(e) {
                e.preventDefault();
                var ids = collectIds('.bulk-select-available');
                if (!ids.length) return;
                // build form
                var form = $('<form>', {
                    method: 'POST',
                    action: '{{ Route('addMultipleToInvoice') }}'
                });
                var token = getCsrfToken();
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: token
                }));
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_method',
                    value: 'PUT'
                }));
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'invoiceId',
                    value: '{{ $invoice->id }}'
                }));
                ids.forEach(function(id) {
                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'ids[]',
                        value: id
                    }));
                });
                // attach and submit
                $('body').append(form);
                form[0].submit();
            });

            // submit selected ids to bulk remove route
            $(document).on('click', '#bulk-remove-btn', function(e) {
                e.preventDefault();
                var ids = collectIds('.bulk-select-added');
                if (!ids.length) return;
                var form = $('<form>', {
                    method: 'POST',
                    action: '{{ Route('removeMultipleFromInvoice') }}'
                });
                var token = getCsrfToken();
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: token
                }));
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_method',
                    value: 'PUT'
                }));
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'invoiceId',
                    value: '{{ $invoice->id }}'
                }));
                ids.forEach(function(id) {
                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'ids[]',
                        value: id
                    }));
                });
                $('body').append(form);
                form[0].submit();
            });
        })();
    </script>
@stop
