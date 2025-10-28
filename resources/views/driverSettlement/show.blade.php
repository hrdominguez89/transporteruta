@extends('adminlte::page')

@section('title', 'Liquidaciones de Choferes')

@section('content_header')
    <div class="row">
        <div class="col-12">
            <a href="{{ Route('driverSettlements') }}" class="btn btn-sm btn-secondary mr-2">Volver</a>
        </div>
        <div class="col-12 mt-3">
            <h1>Liquidación N° <strong><span data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Numeración sistema nuevo">{{ number_format($driverSettlement->id, 0, ',', '.') }}</span> /
                    <span data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Numeración sistema antiguo">{{ $driverSettlement->number ? number_format($driverSettlement->number, 0, ',', '.') : ' - ' }}</span></strong>
            </h1>
        </div>
        <div class="col-12 text-right mb-2">
            @if ($driverSettlement->liquidated == 'NO')
                <button class="btn btn-sm btn-primary col-3" data-toggle="modal"
                    data-target="#liquidatedModal{{ $driverSettlement->id }}">Liquidar</button>
            @else
                <button class="btn btn-sm btn-danger mr-2" data-toggle="modal"
                    data-target="#cancelModal{{ $driverSettlement->id }}">Anular
                    Liquidación</button>
                <a href="{{ Route('driverSettlementPdf', $driverSettlement->id) }}" target="_blank"
                    class="btn btn-sm btn-info">Emitir
                    Comprobante</a>
            @endif
        </div>
        @include('driverSettlement.modals.liquidated')
        @include('driverSettlement.modals.cancel')
    @stop

    @section('content')
        <table class="table table-bordered text-center">
            <thead class="bg-danger">
                <tr>
                    <th>Fecha</th>
                    <th>Período</th>
                    <th>Chofer</th>
                    <th>Total</th>
                    <th>Liquidado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ \Carbon\Carbon::parse($driverSettlement->date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($driverSettlement->dateFrom)->format('d/m/Y') }} -
                        {{ \Carbon\Carbon::parse($driverSettlement->dateTo)->format('d/m/Y') }}</td>
                    <td>
                        <a target="_blank"
                            href="{{ Route('showDriver', $driverSettlement->driver->id) }}">{{ $driverSettlement->driver->name }}</a>
                    </td>
                    <td>$&nbsp;{{ number_format($driverSettlement->total, 2, ',', '.') }}</td>
                    <td>{{ $driverSettlement->liquidated }}</td>
                </tr>
            </tbody>
        </table>
        <br>
        <h4>Constancias de Viaje Agregadas</h4>
        <table class="table table-sm table-bordered text-center data-table">
            <thead class="bg-danger">
                <tr>
                    <th class="text-center" style="font-size:14px">Fecha</th>
                    <th class="text-center" style="font-size:14px">Nro<br>Nuevo</th>
                    <th class="text-center" style="font-size:14px">Nro<br>Antiguo</th>
                    <th class="text-center" style="font-size:14px">Cliente</th>
                    <th class="text-center" style="font-size:14px">Importe<br>Neto</th>
                    <th class="text-center" style="font-size:14px">I.V.A.</th>
                    <th class="text-center" style="font-size:14px">Subtotal</th>
                    <th class="text-center" style="font-size:14px">Peajes</th>
                    <th class="text-center" style="font-size:14px">Total</th>
                    <th class="text-center" style="font-size:14px">% ó $<br>acordado</th>
                    <th class="text-center" style="font-size:14px">A favor<br>del chofer</th>
                    <th class="text-center" style="font-size:14px">% I.V.A.<br>Chofer</th>
                    <th class="text-center" style="font-size:14px">A favor de<br>la empresa</th>
                    <th class="text-center" style="font-size:14px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($driverSettlement->travelCertificates as $travelCertificate)
                    <tr>
                        <td style="font-size:14px;" class="text-center"
                            data-order="{{ \Carbon\Carbon::parse($travelCertificate->date)->timestamp }}">
                            {{ \Carbon\Carbon::parse($travelCertificate->date)->format('d/m/Y') }}</td>
                        <td data-order="{{ $travelCertificate->id }}" style="font-size:14px;" class="text-center">
                            <a target="_blank" title="Numeración Nueva"
                                href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">{{ number_format($travelCertificate->id, 0, ',', '.') }}
                            </a>
                        </td>
                        <td data-order="{{ $travelCertificate->number }}" style="font-size:14px;" class="text-center">
                            <a target="_blank" title="Numeración Antigua"
                                href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">
                                {{ $travelCertificate->number ? number_format($travelCertificate->number, 0, ',', '.') : ' - ' }}</a>
                        </td>
                        <td style="font-size:14px;" class="text-left">{{ $travelCertificate->client->name }}</td>
                        {{-- IMPORTE NETO --}}
                        <td data-order="{{ $travelCertificate->total - $travelCertificate->totalTolls }}"
                            style="font-size:14px;" class="text-right">
                            $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->totalTolls, 2, ',', '.') }}
                        </td>
                        {{-- IVA --}}
                        <td data-order="{{ $travelCertificate->iva }}" style="font-size:14px;" class="text-right">
                            $&nbsp;{{ number_format($travelCertificate->iva, 2, ',', '.') }}
                        </td>
                        {{-- SUBTOTAL (IMPORTE NETO + IVA) --}}
                        <td data-order="{{ $travelCertificate->total - $travelCertificate->totalTolls + $travelCertificate->iva }}"
                            style="font-size:14px;" class="text-right">
                            $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->totalTolls + $travelCertificate->iva, 2, ',', '.') }}
                        </td>
                        {{-- PEAJES --}}
                        <td data-order="{{ $travelCertificate->totalTolls }}" style="font-size:14px;" class="text-right">
                            $&nbsp;{{ number_format($travelCertificate->totalTolls, 2, ',', '.') }}</td>
                        {{-- NETO + IVA + PEAJE = TOTAL --}}
                        <td data-order="{{ $travelCertificate->total + $travelCertificate->iva }}" style="font-size:14px;"
                            class="text-right">
                            $&nbsp;{{ number_format($travelCertificate->total + $travelCertificate->iva, 2, ',', '.') }}
                        </td>
                        {{-- % ó $ acordado --}}
                        {{-- % ó $ acordado --}}
                        @if (in_array($travelCertificate->commission_type, ['porcentaje', 'porcentaje pactado']))
                            <td data-order="{{ $travelCertificate->percent }}" style="font-size:14px;" class="text-right">
                                {{ $travelCertificate->percent }}&nbsp;%
                            </td>
                            {{-- A FAVOR DEL CHOFER (IMPORTE NETO MENOS EL % QUE SE QUEDA LA EMPRESA DE COMISION) --}}
                            <td data-order="{{ $travelCertificate->total - $travelCertificate->totalTolls - (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent }}"
                                style="font-size:14px;" class="text-right">
                                $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->totalTolls - (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent, 2, ',', '.') }}
                            </td>
                            {{-- % IVA DE chofer --}}
                            <td data-order="{{ (($travelCertificate->total -
                                $travelCertificate->totalTolls -
                                (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent) /
                                100) *
                                21 }}"
                                style="font-size:14px;" class="text-right">
                                $&nbsp;{{ number_format(
                                    (($travelCertificate->total -
                                        $travelCertificate->totalTolls -
                                        (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent) /
                                        100) *
                                        21,
                                    2,
                                    ',',
                                    '.',
                                ) }}
                            </td>
                            {{-- A favor de la empresa --}}
                            <td data-order="{{ (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent }}"
                                style="font-size:14px;" class="text-right">
                                $&nbsp;{{ number_format((($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent, 2, ',', '.') }}
                            </td>
                        @else
                            <td data-order="{{ $travelCertificate->fixed_amount }}" style="font-size:14px;"
                                class="text-right">
                                $&nbsp;{{ number_format($travelCertificate->fixed_amount, 2, ',', '.') }}
                            </td>
                            {{-- A FAVOR DEL CHOFER (IMPORTE NETO MENOS EL % QUE SE QUEDA LA EMPRESA DE COMISION) --}}
                            <td data-order="{{ $travelCertificate->total - $travelCertificate->totalTolls - $travelCertificate->fixed_amount }}"
                                style="font-size:14px;" class="text-right">
                                $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->totalTolls - $travelCertificate->fixed_amount, 2, ',', '.') }}
                            </td>
                            {{-- % IVA DE chofer --}}
                            <td data-order="{{ (($travelCertificate->total - $travelCertificate->totalTolls - $travelCertificate->fixed_amount) / 100) * 21 }}"
                                style="font-size:14px;" class="text-right">
                                $&nbsp;{{ number_format(
                                    (($travelCertificate->total - $travelCertificate->totalTolls - $travelCertificate->fixed_amount) / 100) * 21,
                                    2,
                                    ',',
                                    '.',
                                ) }}
                            </td>
                            {{-- A favor de la empresa --}}
                            <td data-order="{{ $travelCertificate->fixed_amount }}" style="font-size:14px;"
                                class="text-right">
                                $&nbsp;{{ number_format($travelCertificate->fixed_amount, 2, ',', '.') }}
                            </td>
                        @endif
                        <td>
                            @if ($driverSettlement->liquidated == 'NO')
                                <form action="{{ Route('removeFromDriverSettlement', $travelCertificate->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="driverSettlementId" value="{{ $driverSettlement->id }}">
                                    <button type="submit" class="btn btn-sm btn-sm btn-warning">Quitar de la
                                        Liquidación</button>
                                </form>
                            @else
                                <strong class="text-danger">¡No se pueden realizar cambios!</strong>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
            <h4>Constancias de viaje del chofer</h4>
            <form id="formConstancias">
                <div class="d-flex justify-content-end ">
                    <button id="btn-ds-id"class="btn btn-sm btn-sm btn-success mb-3" value="{{ $driverSettlement->id }}">Agregar constancias</button>
                </div>
            </form>
            <table class="table table-sm table-bordered text-center data-table " >
                <thead class="bg-danger">
                    <tr>
                        <th class="text-center" style="font-size:14px">Fecha</th>
                        <th class="text-center" style="font-size:14px">Nro<br><br>Nuevo</th>
                        <th class="text-center" style="font-size:14px">Nro<br><br>Antiguo</th>
                        <th class="text-center" style="font-size:14px">Cliente</th>
                        <th class="text-center" style="font-size:14px">Importe<br>Neto</th>
                        <th class="text-center" style="font-size:14px">I.V.A.</th>
                        <th class="text-center" style="font-size:14px">Subtotal</th>
                        <th class="text-center" style="font-size:14px">Peajes</th>
                        <th class="text-center" style="font-size:14px">Total</th>
                        <th class="text-center" style="font-size:14px">% ó $<br>acordado</th>
                        <th class="text-center" style="font-size:14px">A favor<br>del chofer</th>
                        <th class="text-center" style="font-size:14px">% I.V.A.<br>Chofer</th>
                        <th class="text-center" style="font-size:14px">A favor de<br>la empresa</th>
                        <th class="text-center" style="font-size:14px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($driverSettlement->driver->travelCertificates as $travelCertificate)
                        @if ($travelCertificate->date >= $driverSettlement->dateFrom and $travelCertificate->date <= \Carbon\Carbon::parse($driverSettlement->dateTo))
                            @if ($travelCertificate->driverSettlementId != $driverSettlement->id)
                                <tr>
                                    <td style="font-size:14px;" class="text-center"
                                        data-order="{{ \Carbon\Carbon::parse($travelCertificate->date)->timestamp }}">
                                            {{ \Carbon\Carbon::parse($travelCertificate->date)->format('d/m/Y') }}</td>
                                    <td data-order="{{ $travelCertificate->id }}" style="font-size:14px;"
                                        class="text-center">
                                        <a target="_blank" title="Numeración Nueva"
                                            href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">{{ number_format($travelCertificate->id, 0, ',', '.') }}
                                        </a>
                                    </td>
                                    <td data-order="{{ $travelCertificate->number }}" style="font-size:14px;"
                                        class="text-center">
                                        <a target="_blank" title="Numeración Antigua"
                                            href="{{ Route('showTravelCertificate', $travelCertificate->id) }}">
                                            {{ $travelCertificate->number ? number_format($travelCertificate->number, 0, ',', '.') : ' - ' }}</a>
                                    </td>
                                    <td style="font-size:14px;" class="text-left">{{ $travelCertificate->client->name }}
                                    </td>
                                    {{-- IMPORTE NETO --}}
                                    <td data-order="{{ $travelCertificate->total - $travelCertificate->totalTolls }}"
                                        style="font-size:14px;" class="text-right">
                                        $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->totalTolls, 2, ',', '.') }}
                                    </td>
                                    {{-- IVA --}}
                                    <td data-order="{{ $travelCertificate->iva }}" style="font-size:14px;"
                                        class="text-right">
                                        $&nbsp;{{ number_format($travelCertificate->iva, 2, ',', '.') }}
                                    </td>
                                    {{-- SUBTOTAL (IMPORTE NETO + IVA) --}}
                                    <td data-order="{{ $travelCertificate->total - $travelCertificate->totalTolls + $travelCertificate->iva }}"
                                        style="font-size:14px;" class="text-right">
                                        $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->totalTolls + $travelCertificate->iva, 2, ',', '.') }}
                                    </td>
                                    {{-- PEAJES --}}
                                    <td data-order="{{ $travelCertificate->totalTolls }}" style="font-size:14px;"
                                        class="text-right">
                                        $&nbsp;{{ number_format($travelCertificate->totalTolls, 2, ',', '.') }}</td>
                                    {{-- NETO + IVA + PEAJE = TOTAL --}}
                                    <td data-order="{{ $travelCertificate->total + $travelCertificate->iva }}"
                                        style="font-size:14px;" class="text-right">
                                        $&nbsp;{{ number_format($travelCertificate->total + $travelCertificate->iva, 2, ',', '.') }}
                                    </td>
                                    {{-- % ó $ acordado --}}
                                    @if (in_array($travelCertificate->commission_type, ['porcentaje', 'porcentaje pactado']))
                                        <td data-order="{{ $travelCertificate->percent }}" style="font-size:14px;"
                                            class="text-right">
                                            {{ $travelCertificate->percent }}&nbsp;%
                                        </td>
                                        {{-- A FAVOR DEL CHOFER (IMPORTE NETO MENOS EL % QUE SE QUEDA LA EMPRESA DE COMISION) --}}
                                        <td data-order="{{ $travelCertificate->total - $travelCertificate->totalTolls - (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent }}"
                                            style="font-size:14px;" class="text-right">
                                            $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->totalTolls - (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent, 2, ',', '.') }}
                                        </td>
                                        {{-- % IVA DE chofer --}}
                                        <td data-order="{{ (($travelCertificate->total -
                                            $travelCertificate->totalTolls -
                                            (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent) /
                                            100) *
                                            21 }}"
                                            style="font-size:14px;" class="text-right">
                                            $&nbsp;{{ number_format(
                                                (($travelCertificate->total -
                                                    $travelCertificate->totalTolls -
                                                    (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent) /
                                                    100) *
                                                    21,
                                                2,
                                                ',',
                                                '.',
                                            ) }}
                                        </td>
                                        {{-- A favor de la empresa --}}
                                        <td data-order="{{ (($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent }}"
                                            style="font-size:14px;" class="text-right">
                                            $&nbsp;{{ number_format((($travelCertificate->total - $travelCertificate->totalTolls) / 100) * $travelCertificate->percent, 2, ',', '.') }}
                                        </td>
                                    @else
                                        <td data-order="{{ $travelCertificate->fixed_amount }}" style="font-size:14px;"
                                            class="text-right">
                                            $&nbsp;{{ number_format($travelCertificate->fixed_amount, 2, ',', '.') }}
                                        </td>
                                        {{-- A FAVOR DEL CHOFER (IMPORTE NETO MENOS EL % QUE SE QUEDA LA EMPRESA DE COMISION) --}}
                                        <td data-order="{{ $travelCertificate->total - $travelCertificate->totalTolls - $travelCertificate->fixed_amount }}"
                                            style="font-size:14px;" class="text-right">
                                            $&nbsp;{{ number_format($travelCertificate->total - $travelCertificate->totalTolls - $travelCertificate->fixed_amount, 2, ',', '.') }}
                                        </td>
                                        {{-- % IVA DE chofer --}}
                                        <td data-order="{{ (($travelCertificate->total - $travelCertificate->totalTolls - $travelCertificate->fixed_amount) / 100) * 21 }}"
                                            style="font-size:14px;" class="text-right">
                                            $&nbsp;{{ number_format(
                                                (($travelCertificate->total - $travelCertificate->totalTolls - $travelCertificate->fixed_amount) / 100) * 21,
                                                2,
                                                ',',
                                                '.',
                                            ) }}
                                        </td>
                                        {{-- A favor de la empresa --}}
                                        <td data-order="{{ $travelCertificate->fixed_amount }}" style="font-size:14px;"
                                            class="text-right">
                                            $&nbsp;{{ number_format($travelCertificate->fixed_amount, 2, ',', '.') }}
                                        </td>
                                    @endif

                                    <td>
                                        <form action="{{ Route('addToDriverSettlement', $travelCertificate->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="driverSettlementId"
                                                value="{{ $driverSettlement->id }}">
                                            <button type="submit" class="btn btn-sm btn-sm btn-success">Agregar a la
                                            Liquidación</button>
                                        </form>
                                        <input type="checkbox" value="{{ $travelCertificate->id }}">
                                    </td>
                                </tr>
                            @endif
                        @endif
                    @endforeach
                </tbody>
            </table>
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
            $(document).ready(function() {
                // Activar tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();
            });

            document.getElementById('formConstancias').addEventListener('submit', function(e) {
                e.preventDefault();
                cargaMultiple();
            });

            function cargaMultiple() {
                const checkedBoxes = document.querySelectorAll('input[type="checkbox"]:checked');
                const ids = Array.from(checkedBoxes).map(cb => cb.value);
                const driverSettlementId = document.getElementById("btn-ds-id").value;
                if (ids.length === 0) {
                    alert('Selecciona al menos una constancia');
                    return;
                }
                
                // OPCIÓN 1: Enviar con AJAX (RECOMENDADO)
                fetch('{{ route("addMultipleToDriverSettlement") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ids: ids,
                        driverSettlementId:driverSettlementId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    window.location.href = data.redirect; // o recargar página
                });
            }
        </script>
    @stop
