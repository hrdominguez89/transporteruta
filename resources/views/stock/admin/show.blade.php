@extends('adminlte::page')

@section('title', 'Carga')

@section('content_header')
    <div class="row">
        <a href="{{ Route('stock') }}" class="btn btn-sm btn-secondary mr-2">Volver</a>
        <h1 class="col-9">Cliente: <strong>{{ $carga->client->name }}</strong></h1>
        <div class="d-flex ml-auto align-items-center">
          
            <button class="btn btn-sm btn-success mr-2" data-toggle="modal" data-target="#remitoModal">
                Cargar remito
            </button>
        </div>
        @include('stock.admin.modals.edit')
        @include('stock.admin.modals.uploadRemito')
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
                <th>Cantidad</th>
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
                <td>{{ $carga->cantidad }}</td>
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
                    <img src="{{ asset('storage/' . $carga->remito->path) }}"
                        alt="Remito" class="img-thumbnail" style="max-height: 250px">
                @else
                    <span class="text-muted">Sin remito</span>
                @endif
            </td>
            </tr>
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
    </script>
@stop
