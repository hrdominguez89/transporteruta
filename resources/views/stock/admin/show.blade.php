@extends('adminlte::page')

@section('title', 'Carga')

@section('content_header')
    <div class="row align-items-center">
        <a href="{{ Route('stock') }}" class="btn btn-sm btn-secondary mr-2">Volver</a>
        <h1 class="col">Cliente: <strong>{{ $carga->client->name }}</strong></h1>

        <div class="ml-auto d-flex">
            <button class="btn btn-sm btn-success mr-2" data-toggle="modal" data-target="#remitoModal">
                Cargar remito
            </button>
            <button class="btn btn-sm btn-primary mr-2" data-toggle="modal" data-target="#storeContacto">
                Crear contacto
            </button>
            @if ($carga->cliente_tercero?->contacto)
                <button class="btn btn-sm btn-primary mr-2" data-toggle="modal" data-target="#updateContacto-{{ $carga->cliente_tercero->contacto->id }}-{{ $carga->cliente_tercero->id }}">
                Editar contacto
            </button>
                <button class="btn btn-sm btn-danger mr-2" data-toggle="modal" data-target="#deleteContacto-{{ $carga->cliente_tercero->contacto->id }}-{{ $carga->cliente_tercero->id }}">
                Eliminar contacto
            </button>
            @endif
        </div>
        @include('stock.admin.modals.edit')
        @include('stock.admin.modals.uploadRemito')
        @include('stock.admin.modals.storeContacto')
        @include('stock.admin.modals.updateContacto')
        @include('stock.admin.modals.deleteContacto')
    
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
                <th>Cliente destino</th>
                <th>Cantidad</th>
                <th>Liquidado</th>
                <th>Constancia</th>
                <th>Motivo</th>
                <th>Fecha de recepcion T.R.</th>
                <th>Fecha de entrega</th>
                <th>Precio</th>
                <th>Tipo</th>
                <th>Espacio</th>
                <th>Destino</th>
                <th>Estado de envio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
               <td>{{ $carga->nombre }}</td>
                <td>{{ $carga->cliente_tercero?->nombre }}</td>
                <td>{{ $carga->cantidad }}</td>
                <td>{{ $carga->liquidado ? 'Sí' : 'No' }}</td>
                <td>{{ $carga->travel_certificate?->number ?? '-' }}</td>
                <td>{{ $carga->motivo ?? '-' }}</td>
                <td>{{ $carga->fecha_de_recepcion }}</td>
                <td>{{ $carga->fecha_de_entrega }}</td>
                <td>{{ $carga->precio }}</td>
                <td>{{ $carga->tipo }}</td>
                <td>{{ $carga->espacio }}</td>
                <td>{{ $carga->destino }}</td>
                <td>{{ $carga->estado_de_envio }}</td>
                <td>
                   <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#updateModal{{ $carga->id }}">
                    Editar
                </button>
                    <a href="{{ Route('deletestock', $carga->id) }}" class="btn btn-sm btn-info">Eliminar</a>
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <h4>Remito</h4>
    <table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Numero</th>
               <th>Remito</th>
            </tr>
            
        </thead>
         <tbody>
            <tr>
                <td style="font-size:100px">
                    {{ $carga->remito?->numero }}
                </td>
                <td>
    @if ($carga->remito?->path)
        @php $url = asset('storage/' . $carga->remito->path); @endphp

        <a href="{{ $url }}" target="_blank" rel="noopener">
            <img src="{{ $url }}" alt="Remito {{ $carga->remito->numero }}"
                 class="img-thumbnail" style="max-height: 80px; cursor: zoom-in">
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
    <h4>Contacto de tercero</h4>
    <table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Nombre</th>
                <th>Telefono</th>
                <th>Mail</th>
                <th>Categoria</th>
                <th>Comentario</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $carga->cliente_tercero?->contacto?->nombre }} {{ $carga->cliente_tercero?->contacto?->apellido }}</td>
                <td>{{ $carga->cliente_tercero?->contacto?->telefono }}</td>
                <td>{{ $carga->cliente_tercero?->contacto?->mail }}</td>
                <td>{{ $carga->cliente_tercero?->contacto?->categoria }}</td>
                <td>{{ $carga->cliente_tercero?->contacto?->comentario }}</td>
            </tr>
        </tbody>
    </table>

   
    
   
@stop
@section('js')
    <script>
        $(document).ready(function() {
            $('.data-table').DataTable();
        });
    
        $('.select2').select2();
    </script>
@stop
