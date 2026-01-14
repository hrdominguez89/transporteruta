@extends('adminlte::page') 

@section('title', 'Facturas')

@section('content_header')
    @if(session('flag'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
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
                {{-- UX: corregimos acentos --}}
                <h5 class="text-danger">La factura se marcó como pagada y se descontó el saldo de la cuenta corriente</h5>
            </div>
        @endif
    </div>
    @include('invoice.modals.invoiced')
    @include('invoice.modals.cancel')
@stop

@section('content')

    {{-- ===================== REFACT (encabezado): Totales con modelo =====================
         La factura toma importes CALCULADOS de cada constancia para reflejar:
         - Descuentos (%) y montos (sin afectar Peajes)
         - Adicionales (% sobre FIJO)
         - Peajes separados
         Base de cálculo por constancia:
         NETO = (subtotal_sin_peajes - descuento_aplicable) + monto_adicional
         IVA  = iva_calculado (0 si cliente EXENTO)
         PEJ  = total_peajes
         TOTAL = NETO + IVA + PEJ
    ================================================================================ --}}
    @php
        $items = $invoice->travelCertificates ?? collect();

        // Condición IVA del cliente (factura = un cliente)
        $condIva  = strtoupper($invoice->client->ivaCondition ?? $invoice->client->iva_condition ?? $invoice->client->ivaType ?? '');
        $esExento = strpos($condIva, 'EXENTO') !== false;

        // Neto sin IVA NI PEAJES por constancia
        $totalNeto = (float) $items->sum(function ($tc) {
            return (($tc->subtotal_sin_peajes - $tc->descuento_aplicable) + $tc->monto_adicional);
        });

        // IVA total (0 si EXENTO)
        $totalIva = (float) $items->sum(function ($tc) use ($esExento) {
            return $esExento ? 0 : ($tc->iva_calculado ?? 0);
        });

        // Peajes totales
        $totalPeajes = (float) $items->sum(function ($tc) {
            return $tc->total_peajes ?? 0;
        });

        // Total con IVA
        $totalConIva = $totalNeto + $totalIva + $totalPeajes;
    @endphp
    {{-- ===================== /REFACT (encabezado) ====================================== --}}

    {{-- ======================= LEGACY (solo referencia, NO usar en cálculo) ============
        Se deja documentado pero comentado para evitar confusiones de Blade.
        La fuente de verdad son los campos calculados por constancia.
    ================================================================================ --}}

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
                    <a target="_blank" href="{{ Route('showClient', $invoice->client->id) }}">{{ $invoice->client->name }}</a>
                </td>

                {{-- REF: "Total (Sin IVA)" debe ser SOLO el Neto, sin sumar Peajes --}}
                <!-- <td>$&nbsp;{{ number_format($totalNeto, 2, ',', '.') }}</td> -->
                <td>$&nbsp;{{ $invoice->total }}</td>

                {{-- IVA (0 si EXENTO; si no, calculado por fila arriba) --}}
                <td>$&nbsp;{{ number_format($totalIva, 2, ',', '.') }}</td>

                {{-- Peajes (sumatoria por constancias) --}}
                <td>$&nbsp;{{ number_format($totalPeajes, 2, ',', '.') }}</td>

                <td>$&nbsp;{{ number_format($invoice->balance, 2, ',', '.') }}</td>
                <td>{{ $invoice->invoiced }}</td>

                {{-- Total (Con IVA) = Neto + IVA + Peajes --}}
                <td>$&nbsp;{{ number_format($invoice->getTotalWithIvaAttribute(), 2, ',', '.') }}</td>
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
                <th>Fecha</th>
                <th>Precio Neto</th>
                <th>I.V.A.</th>
                <th>Peajes</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            {{-- REFACT (por fila): cálculo por constancia usando campos del modelo --}}
            @foreach ($invoice->travelCertificates as $travelCertificate)
                @php
                    // Neto sin IVA ni peajes: (subtotal - descuento) + adicional
                    $netoFila   = ($travelCertificate->subtotal_sin_peajes - $travelCertificate->descuento_aplicable)
                                + $travelCertificate->monto_adicional;

                    // IVA: 0 si el cliente es EXENTO
                    $ivaFila    = $esExento ? 0 : ($travelCertificate->iva_calculado ?? 0);

                    // Peajes informados en la constancia
                    $peajesFila = $travelCertificate->total_peajes ?? 0;

                    // Total de la fila
                    $totalFila  = $netoFila + $ivaFila + $peajesFila;
                @endphp

                <tr>
                    @if ($invoice->invoiced == 'NO')
                        <td>
                            <input type="checkbox" class="bulk-select bulk-select-added"
                                   data-id="{{ $travelCertificate->id }}">
                        </td>
                    @endif

                    <td data-order="{{ $travelCertificate->id }}">
                        <a target="_blank" href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">
                            {{ number_format($travelCertificate->id, 0, ',', '.') }}
                        </a>
                    </td>
                    <td data-order="{{ $travelCertificate->number }}">
                        <a target="_blank" href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">
                            {{ number_format($travelCertificate->number, 0, ',', '.') }}
                        </a>
                    </td>
                    <td>{{ $travelCertificate->driver->name }}</td>
                    <td>{{ $travelCertificate->date ? $travelCertificate->date->format('Y/m/d') : 'Sin fecha' }}</td>

                    {{-- Importes calculados (refactor) --}}
                    <td data-order="{{ $netoFila }}">$&nbsp;{{ number_format($netoFila, 2, ',', '.') }}</td>
                    <td data-order="{{ $ivaFila }}">$&nbsp;{{ number_format($ivaFila, 2, ',', '.') }}</td>
                    <td data-order="{{ $peajesFila }}">$&nbsp;{{ number_format($peajesFila, 2, ',', '.') }}</td>
                    <td data-order="{{ $totalFila }}">$&nbsp;{{ number_format($totalFila, 2, ',', '.') }}</td>

                    <td>
                        @if ($invoice->invoiced == 'NO')
                            <form action="{{ Route('removeFromInvoice', $travelCertificate->id) }}" method="POST"
                                  class="prevent-double-submit">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="invoiceId" value="{{ $invoice->id }}">
                                <button type="submit" class="btn btn-sm btn-warning btn-submit-once">
                                    Quitar de la Factura
                                </button>
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
            <button id="bulk-remove-btn" class="btn btn-sm btn-warning btn-submit-once" disabled>
                Quitar seleccionados de la Factura
            </button>
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
                    @if ($travelCertificate->invoiceId != $invoice->id && $travelCertificate->invoiced == 'NO')
                        @php
                            // REFACT (por fila disponible): mismo cálculo que arriba
                            $netoFila   = (($travelCertificate->subtotal_sin_peajes - $travelCertificate->descuento_aplicable)
                                          + $travelCertificate->monto_adicional);
                            $ivaFila    = $esExento ? 0 : ($travelCertificate->iva_calculado ?? 0);
                            $peajesFila = $travelCertificate->total_peajes ?? 0;
                            $totalFila  = $netoFila + $ivaFila + $peajesFila;
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" class="bulk-select bulk-select-available"
                                       data-id="{{ $travelCertificate->id }}">
                            </td>
                            <td data-order="{{ $travelCertificate->id }}">
                                <a target="_blank" href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">
                                    {{ number_format($travelCertificate->id, 0, ',', '.') }}
                                </a>
                            </td>
                            <td data-order="{{ $travelCertificate->number }}">
                                <a target="_blank" href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">
                                    {{ number_format($travelCertificate->number, 0, ',', '.') }}
                                </a>
                            </td>
                            <td>{{ $travelCertificate->driver->name }}</td>

                            {{-- Importes calculados (refactor) --}}
                            <td data-order="{{ $netoFila }}">$&nbsp;{{ number_format($netoFila, 2, ',', '.') }}</td>
                            <td data-order="{{ $ivaFila }}">$&nbsp;{{ number_format($ivaFila, 2, ',', '.') }}</td>
                            <td data-order="{{ $peajesFila }}">$&nbsp;{{ number_format($peajesFila, 2, ',', '.') }}</td>
                            <td data-order="{{ $totalFila }}">$&nbsp;{{ number_format($totalFila, 2, ',', '.') }}</td>

                            <td>
                                <form action="{{ Route('addToInvoice', $travelCertificate->id) }}" method="POST"
                                      class="prevent-double-submit">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="invoiceId" value="{{ $invoice->id }}">
                                    <button type="submit" class="btn btn-sm btn-success btn-submit-once">
                                        Agregar a la Factura
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div class="mb-2">
            <button id="bulk-add-btn" class="btn btn-sm btn-success btn-submit-once" disabled>
                Agregar seleccionados a la Factura
            </button>
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
            $(document).on('click', '.btn-submit-once', function(e) {
                var $btn = $(this);
                if ($btn.prop('disabled')) {
                    e.preventDefault();
                    return;
                }
                var $form = $btn.closest('form');
                $btn.data('original-text', $btn.html());
                $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');

                if ($form.length) {
                    e.preventDefault();
                    $form.find('button[type="submit"]').each(function() {
                        $(this).prop('disabled', true);
                    });
                    try { $form[0].submit(); } catch (err) { $form.trigger('submit'); }
                } else {
                    $btn.prop('disabled', true);
                }
            });

            $(document).on('submit', 'form.prevent-double-submit', function() {
                var $form = $(this);
                $form.find('button[type="submit"]').each(function() {
                    var $b = $(this);
                    $b.prop('disabled', true);
                    if (!$b.data('original-text')) {
                        $b.data('original-text', $b.html());
                        $b.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
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

            $(document).on('change', '.bulk-select', function() {
                var anyAvailable = $('.bulk-select-available:checked').length > 0;
                var anyAdded = $('.bulk-select-added:checked').length > 0;
                $('#bulk-add-btn').prop('disabled', !anyAvailable);
                $('#bulk-remove-btn').prop('disabled', !anyAdded);
            });

            $(document).on('click', '#bulk-add-btn', function(e) {
                e.preventDefault();
                var ids = collectIds('.bulk-select-available');
                if (!ids.length) return;
                var form = $('<form>', { method: 'POST', action: '{{ Route("addMultipleToInvoice") }}' });
                var token = getCsrfToken();
                form.append($('<input>', { type: 'hidden', name: '_token', value: token }));
                form.append($('<input>', { type: 'hidden', name: '_method', value: 'PUT' }));
                form.append($('<input>', { type: 'hidden', name: 'invoiceId', value: '{{ $invoice->id }}' }));
                ids.forEach(function(id) {
                    form.append($('<input>', { type: 'hidden', name: 'ids[]', value: id }));
                });
                $('body').append(form);
                form[0].submit();
            });

            $(document).on('click', '#bulk-remove-btn', function(e) {
                e.preventDefault();
                var ids = collectIds('.bulk-select-added');
                if (!ids.length) return;
                var form = $('<form>', { method: 'POST', action: '{{ Route('removeMultipleFromInvoice') }}' });
                var token = getCsrfToken();
                form.append($('<input>', { type: 'hidden', name: '_token', value: token }));
                form.append($('<input>', { type: 'hidden', name: '_method', value: 'PUT' }));
                form.append($('<input>', { type: 'hidden', name: 'invoiceId', value: '{{ $invoice->id }}' }));
                ids.forEach(function(id) {
                    form.append($('<input>', { type: 'hidden', name: 'ids[]', value: id }));
                });
                $('body').append(form);
                form[0].submit();
            });
        })();
    </script>
@stop
