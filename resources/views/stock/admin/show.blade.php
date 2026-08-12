@extends('adminlte::page')

@section('title', 'Carga')

@section('content_header')
    <div class="row align-items-center">
        <a href="{{ Route('stock') }}" class="btn btn-sm btn-secondary mr-2">Volver</a>
        <h1 class="col">Cliente: <strong>{{ $carga->client->name }}</strong></h1>
        <div class="ml-auto d-flex">
            {{-- <button class="btn btn-sm btn-danger mr-2" data-toggle="modal" data-target="#editPrice-{{ $carga->id }}">Editar precio</button>
            <button class="btn btn-sm btn-danger mr-2" data-toggle="modal" data-target="#editType-{{ $carga->id }}">Cantidades</button>
            @if ($carga?->remito)
                <button class="btn btn-sm btn-warning mr-2" data-toggle="modal" data-target="#editRemitoModal">Editar remito</button>
            @else
                <button class="btn btn-sm btn-light mr-2" data-toggle="modal" data-target="#remitoModal">Cargar remito</button>
            @endif
            <button class="btn btn-sm btn-outline-danger mr-2" data-toggle="modal" data-target="#storeContacto">Crear contacto</button> --}}
        </div>
        @include('stock.admin.modals.edit')
        @include('stock.admin.modals.editPrice')
        @include('stock.admin.modals.editType')
        @include('stock.admin.modals.estadoEnvio')
        @include('stock.admin.modals.remito.editRemito')
        @include('stock.admin.modals.remito.uploadRemito')
        @include('stock.admin.modals.contacto.storeContacto')
        @include('stock.admin.modals.contacto.updateContacto')
        @include('stock.admin.modals.contacto.deleteContacto')
    
       @if($errors->any())
    <div id="errorAlert" class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Corregí los siguientes errores:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <script>
        setTimeout(function () {
            $('#errorAlert').alert('close');
        }, 5000);
    </script>
@endif
    </div>
@stop
@section('content')
    <h4>Datos de la carga</h4>
    <table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Nombre</th>
                <th>Bultos</th>
                <th>Pallets normales</th>
                <th>Pallets grandes</th>   
                <th>Precio</th>
                <th>Estado de envio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $carga->nombre }}</td>
                <td>{{ $carga->cantidad_bulto }}</td>
                <td>{{ $carga->cantidad_pallet_normal }}</td>
                <td>{{ $carga->cantidad_pallet_grande }}</td>
                <td>${{ $carga->precio }}</td>
                <td>
                    <span class="badge badge-success p-2" style="font-size: 1rem;">{{ $carga->estadoActual?->estado }}</span>  {{ $carga->estadoActual?->horario }} 
                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#updateEstadoEnvioModal{{ $carga->id }}">Actualizar</button>
                </td>
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            Acciones
                        </button>
                        <div class="dropdown-menu">
                            <button class="dropdown-item" data-toggle="modal" data-target="#updateModal{{ $carga->id }}">Editar carga</button>
                            <button class="dropdown-item" data-toggle="modal" data-target="#editPrice-{{ $carga->id }}">Editar precio</button>
                            <button class="dropdown-item" data-toggle="modal" data-target="#editType-{{ $carga->id }}">Cantidades</button>
                            @if ($carga?->remito)
                                <button class="dropdown-item" data-toggle="modal" data-target="#editRemitoModal">Editar remito</button>
                            @else
                                <button class="dropdown-item" data-toggle="modal" data-target="#remitoModal">Cargar remito</button>
                            @endif
                            <button class="dropdown-item" data-toggle="modal" data-target="#storeContacto">Crear contacto</button>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <table class="table table-bordered text-center">
        <thead class="bg-dark">
            <tr>
                <th>Chofer</th>
                <th>Cliente destino</th>
                <th>Destino</th>
                <th>Fecha de recepcion T.R.</th>
                <th>Fecha de entrega</th>
                <th>Constancia</th>
                <th>Liquidado</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $carga->driver?->name ?? 'sin asignar'}}</td>
                <td>{{ $carga->cliente_tercero?->nombre }}</td>
                <td>{{ $carga->destino }}</td>
                <td>{{ $carga->fecha_de_recepcion }}</td>
                <td>{{ $carga->fecha_de_entrega ?? '---'}}</td>
                <td>{{ $carga->travel_certificate?->id ?? '---' }}</td>
                <td>{{ $carga->liquidado ? 'Sí' : 'No' }}</td>
                <td>{{ $carga->motivo ?? '---'}}</td>
            </tr>
        </tbody>
    </table>
    <h4>Remito</h4>
    <table class="table table-bordered text-center">
        <thead class="thead-light">
            <tr>
                <th>Numero</th>
                <th>Valor declarado</th>
               <th>Remito</th>
            </tr>
            
        </thead>
         <tbody>
            <tr>
                <td style="font-size:30px">{{ $carga->remito?->numero }}</td>
                <td style="font-size:30px">${{ $carga->remito?->valor_declarado }}</td>
                <td>
                    @if ($carga->remito?->path)
                        @php 
                            $url = asset('storage/' . $carga->remito->path); 
                        @endphp
                        <a href="{{ $url }}" target="_blank" rel="noopener">
                            <img src="{{ $url }}" alt="Remito {{ $carga->remito->numero }}"
                                class="img-thumbnail" style="max-height: 100px; cursor: zoom-in">
                        </a>
                        <div class="mt-1">
                            <a href="{{ route('remitoPdf', $carga->remito->id) }}"
                            class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Descargar PDF
                            </a>
                        </div>
                    @else
                        <span class="text-muted">Sin remito</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <h4>Datos del cliente 3ro</h4>
    <table class="table table-bordered text-center">
        <thead style="background-color: #f8d7da;">
            <tr>
                <th>Nombre</th>
                <th>N° de cliente</th>
                <th>CUIT</th>
                <th>Condición de venta</th>
                <th>Código postal</th>
                <th>Domicilio de entrega</th>
                <th>Horario de entrega</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $carga->cliente_tercero?->nombre }}</td>
                <td>{{ $carga->cliente_tercero?->numero_cliente }}</td>
                <td>{{ $carga->cliente_tercero?->cuit }}</td>
                <td>{{ $carga->cliente_tercero?->condicion_venta }}</td>
                <td>{{ $carga->cliente_tercero?->codigo_postal }}</td>
                <td>{{ $carga->cliente_tercero?->direccion }}</td>
                <td>{{ $carga->cliente_tercero?->horario_entrega }}</td>
            </tr>
        </tbody>
    </table>
    <br>
   <h4>Contactos</h4>
    <table class="table table-bordered text-center">
        <thead class="thead-rojo">
            <tr>
                <th>Nombre</th>
                <th>Departamento</th>
                <th>mail</th>
                <th>telefono</th>
                <th>observacion</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($carga->cliente_tercero?->contactos ?? [] as $contacto)
                <tr>
                    <td>{{ $contacto->nombre }}</td>
                    <td>{{ $contacto->categoria }}</td>
                    <td>{{ $contacto->mail }}</td>
                    <td>{{ $contacto->telefono }}</td>
                    <td>{{ $contacto->comentario }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary mr-2" data-toggle="modal" data-target="#updateContacto-{{ $contacto->id }}-{{ $carga->cliente_tercero->id }}">
                            Editar contacto
                        </button>
                        <button class="btn btn-sm btn-danger mr-2" data-toggle="modal" data-target="#deleteContacto-{{ $contacto->id }}-{{ $carga->cliente_tercero->id }}">
                            Eliminar contacto
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-muted">Sin contactos</td>
                </tr>
            @endforelse
        </tbody>
    </table>

   
    
@stop
@section('css')
<style>
.thead-rojo th {
    background-color: #ffffff;
    border: 1px solid #dc3545;
    border-bottom: 1px solid #dc3545 !important;
}
</style>
@endsection
@section('js')
    <script>
        $(document).ready(function() {
            $('.data-table').DataTable();
        });
    
        $('.select2').select2();
    </script>
@stop
