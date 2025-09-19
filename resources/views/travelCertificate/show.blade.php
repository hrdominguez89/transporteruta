@extends('adminlte::page')

@section('title', 'Constancias de Viaje')

@section('content_header')
    <div class="row">
        <div class="col-12">
            <a href="{{ Route('travelCertificates') }}" class="btn btn-sm btn-secondary mr-2">Volver</a>
        </div>
        <div class="col-12 mt-3">


            <h1>Constancia de Viaje N° <strong><span data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Numeración sistema nuevo">{{ number_format($travelCertificate->id, 0, ',', '.') }}</span> /
                    <span data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Numeración sistema antiguo">{{ $travelCertificate->number ? number_format($travelCertificate->number, 0, ',', '.') : ' - ' }}</span></strong>
            </h1>
        </div>

        @if ($travelCertificate->invoiced == 'NO')
            <div class="col-12 text-right mb-2">
                <button class="btn btn-sm btn-danger col-2 mr-2" data-toggle="modal" data-target="#storeModal">Agregar Nuevo
                    Item</button>
                <button class="btn btn-sm btn-success col-2" data-toggle="modal"
                    data-target="#updateModal{{ $travelCertificate->id }}">Actualizar Constancia</button>
            </div>
            <div class="col-12 text-right mb-2">
                <a href="{{ Route('travelCertificatePdf', $travelCertificate->id) }}" class="btn btn-sm btn-info col-4">Generar
                    PDF</a>
            </div>
           
        @else
            <div class="col-12 text-right mb-2">
                <a href="{{ Route('travelCertificatePdf', $travelCertificate->id) }}" class="btn btn-sm btn-info col-4">Generar
                    PDF</a>
            </div>
            <div class="col-12 text-left mb-2">
                <strong class="text-danger">La constancia ha sido agregada a la factura <a
                        href="{{ Route('showInvoice', $travelCertificate->invoice->id) }}">{{ number_format($travelCertificate->invoice->id, 0, ',', '.') }}</a>,
                    no se pueden realizar modificaciones.</strong>
            </div>
        @endif
        @include('travelItem.modals.store')
        @include('travelCertificate.modals.update')
    @stop

    @section('content')
        <h4>Detalles</h4>
        <table class="table table-sm table-bordered text-center">
            <thead class="bg-danger">
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Chofer</th>
                    <th>Pago al Chofer</th>
                    <th>Precio (Sin IVA)</th>
                    <th>IVA</th>
                    <th>Destino</th>
                    <th>Precio Total (Con IVA)</th>
                    <th>Facturado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ \Carbon\Carbon::parse($travelCertificate->date)->format('d/m/Y') }}</td>
                    <td>{{ $travelCertificate->client->name }}</td>
                    <td>{{ $travelCertificate->driver->name }}</td>
                    <td>$&nbsp;{{ number_format($travelCertificate->driverPayment, 2, ',', '.') }}</td>
                    <td>$&nbsp;{{ number_format($travelCertificate->total, 2, ',', '.') }}</td>
                    <td>$&nbsp;{{ number_format($travelCertificate->iva, 2, ',', '.') }}</td>
                    <td>{{ $travelCertificate->destiny }}</td>
                    <td>$&nbsp;{{ number_format($travelCertificate->total + $travelCertificate->iva, 2, ',', '.') }}</td>
                    <td>{{ $travelCertificate->invoiced }}</td>
                </tr>
            </tbody>
        </table>
        <h4>Items de Viaje</h4>
        <table class="table table-sm table-bordered text-center data-table">
            <thead class="bg-danger">
                <tr>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Precio Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($travelCertificate->travelItems as $travelItem)
                    <tr>
                        <td>{{ $travelItem->type }}</td>
                        <td class="text-center">{{ $travelItem->description }}</td>
                        <td data-order="{{ $travelItem->price }}">
                            $&nbsp;{{ number_format($travelItem->price, 2, ',', '.') }}</td>
                        <td>
                            @if ($travelCertificate->invoiced == 'NO')
                                @if ($travelItem->type == 'FIJO' && $tiene_tarifa_adicional)
                                    <strong class="text-danger">Este ítem tiene un adicional asociado. Eliminá primero el adicional para poder borrarlo.</strong>
                                @else
                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                        data-target="#deleteItemModal{{ $travelItem->id }}">Eliminar</button>
                                @endif
                            @else
                                <strong class="text-danger">¡No se pueden realizar cambios!</strong>
                            @endif
                        </td>
                    </tr>
                    @include('travelItem.modals.delete')
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
        </script>
    @stop
