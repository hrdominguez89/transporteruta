@extends('adminlte::page')

@section('title', 'Facturas')

@section('content_header')
    <div class="row">

        <div class="col-12">
            <a href="{{ Route('invoices') }}" class="btn btn-sm btn-secondary">Volver</a>
        </div>
        <div class="col-12 mt-3">
            <h1>Factura N° <strong>{{ number_format($invoice->number, 0, ',', '.') }}-{{ sprintf('%05d', $invoice->pointOfSale) }}</strong></h1>
        </div>

        @if ($invoice->invoiced == 'SI' and $invoice->paid == 'NO')
            <div class="col-12 text-right mb-2">
                <button class="btn btn-sm btn-danger col-2 mr-2" data-toggle="modal"
                    data-target="#cancelModal{{ $invoice->id }}">Anular Factura</button>
                <a target="_blank" href="{{ Route('invoicePdf', $invoice->id) }}" class="btn btn-sm btn-info col-2">Generar PDF</a>
            </div>
        @elseif($invoice->invoiced == 'NO')
            <div class="col-12 text-right mb-2">
                <button class="btn btn-sm btn-primary col-4" data-toggle="modal"
                    data-target="#invoicedModal{{ $invoice->id }}">Facturar</button>
            </div>
        @endif
        @if ($invoice->paid == 'SI')
            <div class="col-12 text-right mb-2">

                <a target="_blank" href="{{ Route('invoicePdf', $invoice->id) }}" class="btn btn-sm btn-info col-4">Generar PDF</a>
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
                <td>$&nbsp;{{ number_format($invoice->total, 2, ',', '.') }}</td>
                <td>$&nbsp;{{ number_format($invoice->iva, 2, ',', '.') }}</td>
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
                            <form action="{{ Route('removeFromInvoice', $travelCertificate->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="invoiceId" value="{{ $invoice->id }}">
                                <button type="submit" class="btn btn-sm btn-warning">Quitar de la Factura</button>
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
    @if ($invoice->invoiced == 'NO')
        <h4>Constancias de Viaje del Cliente sin Liquidar</h4>
        <table class="table table-sm table-bordered text-center data-table">
            <thead class="bg-danger">
                <tr>
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
                                <form action="{{ Route('addToInvoice', $travelCertificate->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="invoiceId" value="{{ $invoice->id }}">
                                    <button type="submit" class="btn btn-sm btn-success">Agregar a la Factura</button>
                                </form>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
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
    </script>
@stop
