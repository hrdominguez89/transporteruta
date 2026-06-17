@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')

    <div class="row">
        <a href="{{ Route('clients') }}" class="btn btn-sm btn-secondary mr-2">Volver</a>
        <h1 class="col-9">Cliente: <strong>{{ $client->name }}</strong></h1>
        <button class="btn btn-sm btn-success col-2" data-toggle="modal" data-target="#updateModal{{ $client->id }}">Actualizar
            Cliente</button>
        @include('client.modals.update')
    </div>
@stop

@section('content')
    <h4>Datos del Cliente</h4>
    <table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>DNI/CUIT</th>
                <th>Direccion</th>
                <th>Ciudad</th>
                <th>Telefono</th>
                <th>IVA Tipo</th>
                <th>Saldo Deudor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $client->dni }}</td>
                <td>{{ $client->address }}</td>
                <td>{{ $client->city }}</td>
                <td>{{ $client->phone }}</td>
                <td>{{ $client->ivaType }}</td>
                <td>$&nbsp;{{ number_format($client->balance, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    @include('client.modals.storeContacto')
    <h4>Contactos:</h4>
    <div class="row">
        <div class="col-4">
            <h5>Crear contacto</h3>
            <button class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#storeContacto">
                +
            </button>
            <div id="contactosCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @include('client.modals.updateContacto')
                    @foreach($client->contactos as $index => $contacto)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" style="transition: 0.5s">
                            <div class="d-flex justify-content-center">
                                <article class="card" style="width: 220px;">
                                    <div class="card-body">
                                        <ul class="card__data list-unstyled mb-0">
                                            <li><span>Nombre :</span><strong> {{ $contacto->nombre }}</strong></li>
                                            <li><span>Apellido :</span><strong> {{ $contacto->apellido }}</strong></li>
                                            <li><span>Departamento :</span><strong> {{ $contacto->categoria }}</strong></li>
                                            <li><span>Mail :</span><strong> {{ $contacto->mail }}</strong></li>
                                            <li><span>Telefono :</span><strong> {{ $contacto->telefono }}</strong></li>
                                            <li><span>Comentario :</span><strong> {{ $contacto->comentario }}</strong></li>
                                           <button class="btn btn-primary"
                                                    data-toggle="modal"
                                                    data-target="#updateContacto-{{ $contacto->id }}-{{ $client->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="{{ route('deleteContacto', [$contacto->id, $client->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link p-0">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </ul>
                                    </div>
                                </article>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#contactosCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#contactosCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            </div>  
        </div>
        <div class="col-4">
            <h5>Observaciones:</h5>
            <div>{{ $client->observations }}</div>
        </div>
        <div class="col-4">
            <h5>Plazos de vencimiento</h5>
            <p>Dias de vencimiento asignado:  {{ $client->paymentTermDays }} dias.</p>
        </div>
    </div>
    
    <br>
    <h4>Facturas Pendientes de Pagar</h4>
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th class="text-center">Número</th>
                <th class="text-center">Total con IVA</th>
                <th class="text-center">Balance</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($client->invoices as $invoice)
                @if ($invoice->paid == 'NO' and $invoice->invoiced == 'SI')
                    <tr>
                        <td class="text-center">{{ number_format($invoice->number, 0, ',', '.') }}</td>
                        <td class="text-right">$&nbsp;{{ number_format($invoice->totalWithIva, 2, ',', '.') }}</td>
                        <td class="text-right">$&nbsp;{{ number_format($invoice->balance, 2, ',', '.') }}</td>
                        <td class="text-center">
                            <a href="{{ Route('showInvoice', $invoice->id) }}" class="btn btn-sm btn-info">Ver</a>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <br>
    <h4>Facturas Abiertas</h4>
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th class="text-center">Número</th>
                <th class="text-center">Total con IVA</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($client->invoices as $invoice)
                @if ($invoice->invoiced == 'NO')
                    <tr>
                        <td class="text-center">{{ number_format($invoice->number, 0, ',', '.') }}</td>
                        <td class="text-right">$&nbsp;{{ number_format($invoice->totalWithIva, 2, ',', '.') }}</td>
                        <td class="text-center">
                            <a href="{{ Route('showInvoice', $invoice->id) }}" class="btn btn-sm btn-info">Ver</a>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <br>
    <h4>Facturas Pagadas</h4>
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th class="text-center">Número</th>
                <th class="text-center">Total con IVA</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($client->invoices as $invoice)
                @if ($invoice->paid == 'SI')
                    <tr>
                        <td class="text-center">{{ number_format($invoice->number, 0, ',', '.') }}</td>
                        <td class="text-right">$&nbsp;{{ number_format($invoice->totalWithIva, 2, ',', '.') }}</td>
                        <td class="text-center">
                            <a href="{{ Route('showInvoice', $invoice->id) }}" class="btn btn-sm btn-info">Ver</a>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@stop
@section('js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
