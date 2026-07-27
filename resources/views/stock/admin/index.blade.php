@extends('adminlte::page')

@section('title', 'Stock')

@section('content_header')
    <div class="row">
        <h1 class="col-7">Cargas</h1>
        <button class="btn btn-sm btn-danger col-1" data-toggle="modal" data-target="#storeModal">Agregar carga</button>
        <button class="btn btn-sm btn-danger col-1 ml-2" data-toggle="modal" data-target="#pricemodal">Precios</button>
        <button class="btn btn-sm btn-danger col-1 ml-2" data-toggle="modal" data-target="#buscarcortedeoperaciones">Corte de op.</button>
        <button class="btn btn-sm btn-danger col-1 ml-2" data-toggle="modal" data-target="#storeClienteTercero">Agregar 3ro</button>
        <form action="{{ route('stock') }}" method="GET" class="form-inline mb-3">
            <select name="client_id" class="form-control mr-2">
                <option value="">Todos los clientes</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}"
                        {{ request('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
        </form>
    </div>
    @include('stock.admin.modals.store')
    @include('stock.admin.modals.price')
    @include('stock.admin.modals.operations')
    @include('stock.admin.modals.storeClienteTercero')
    @if($errors->any())
    <div id="errorAlert" class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <script>
        setTimeout(function () {
            $('#errorAlert').alert('close');
        }, 5000);
    </script>
    @endif
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
             <tr>
                <th>Nombre</th>
                <th>Cliente</th>
                <th>Cantidad</th>
                {{-- <th>Fecha de recepcion T.R.</th> --}}
                {{-- <th>Fecha de entrega</th> --}}
                <th>Tipo</th>
                <th>Destino</th>
                <th>Estado de envio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cargas as $carga)
                 <tr>
                    <td>{{ $carga->nombre }}</td>
                    <td>{{ $carga->client->name }}</td>
                    <td>{{ $carga->cantidad }}</td>
                    {{-- <td>{{ $carga->fecha_de_recepcion }}</td> --}}
                    {{-- <td>{{ $carga->fecha_de_entrega }}</td> --}}
                    <td>{{ $carga->tipo }}</td>
                    <td>{{ $carga->destino }}</td>
                    <td>{{ $carga->estado_de_envio }}</td>
                    <td>
                        <a href="{{ Route('showcarga', $carga->id) }}" class="btn btn-sm btn-info">Ver</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
            @isset($corte)
        <div class="modal fade" id="cortedeoperaciones" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Resultado del corte</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
</button>
            </div>

            <div class="modal-body">
                <p class="mb-1"><strong>Cliente:</strong> {{ $corte['cliente'] }}</p>
                <p class="mb-3"><strong>Corte al:</strong> {{ $corte['fecha'] }}</p>

                <table class="table table-bordered text-center">
                <thead class="bg-danger">
                    <tr>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                    <td>Bultos</td>
                    <td>{{ $corte['bultos_cantidad'] }}</td>
                    <td>${{ number_format($corte['bultos_total'], 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                    <td>Pallets</td>
                    <td>{{ $corte['pallets_cantidad'] }}</td>
                    <td>${{ number_format($corte['pallets_total'], 2, ',', '.') }}</td>
                    </tr>
                    <tr class="font-weight-bold">
                    <td>Total</td>
                    <td>{{ $corte['bultos_cantidad'] + $corte['pallets_cantidad'] }}</td>
                    <td>${{ number_format($corte['total'], 2, ',', '.') }}</td>
                    </tr>
                </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
            </div>
        </div>
        </div>

       
        @endisset   
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
        $(document).ready(function () {
            $('#cortedeoperaciones').modal('show');
        });
    </script>
@stop