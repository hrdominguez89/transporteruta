@extends('adminlte::page')

@section('title', 'Stock')

@section('content_header')
   <div class="row mb-2">
    <h1 class="col-12">Cargas</h1>
</div>

<div class="row mb-3">
    <div class="col-12 d-flex flex-wrap" style="gap: .5rem;">
        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#storeModal">Agregar carga</button>
        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#storeClienteTercero">Agregar 3ro</button>
        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#pricemodal">Editar precios</button>
        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editClienteTercero">Editar 3ro</button>
        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#buscarcortedeoperaciones">Corte de operaciones</button>
        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteClienteTercero">Eliminar 3ro</button>

        <form action="{{ route('stock') }}" method="GET" class="d-inline">
            <input type="hidden" name="all_cargas" value="1">
            <button type="submit" class="btn btn-sm btn-info">Ver todas las cargas</button>
        </form>
        <form action="{{ route('stock') }}" method="GET" class="d-inline">
            <input type="hidden" name="all_cargas" value="0">
            <button type="submit" class="btn btn-sm btn-info">Ver actuales cargas</button>
        </form>
    </div>
</div>

<div class="row mb-3">
    <form action="{{ route('stock') }}" method="GET" class="form-inline col-12">
        <label for="client_id" class="mr-2">Clientes:</label>
        <select name="client_id" id="client_id" class="form-control mr-2" required>
            <option value="">Todos los clientes</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                    {{ $client->name }}
                </option>
            @endforeach
        </select>

        <div id="wrapper_tercero" class="form-inline mr-2" style="display: none;">
            <label for="filtro_tercero" class="mr-2">Cliente (3ro):</label>
            <select id="filtro_tercero" name="cliente_tercero_id" class="form-control">
                <option value="">---- Todos ----</option>
                @foreach ($clientes_terceros as $tercero)
                    <option value="{{ $tercero->id }}" data-client="{{ $tercero->client_id }}"
                        {{ request('cliente_tercero_id') == $tercero->id ? 'selected' : '' }}>
                        {{ $tercero->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
    </form>
</div>
    @include('stock.admin.modals.clienteTercero.editClienteTercero')   
    @include('stock.admin.modals.store')
    @include('stock.admin.modals.price.price')
    @include('stock.admin.modals.clienteTercero.storeClienteTercero')
    @include('stock.admin.modals.clienteTercero.deleteClienteTercero')
   @if (session('success'))
        <div id="successAlert" class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <script>
            setTimeout(function () { $('#successAlert').alert('close'); }, 5000);
        </script>
    @endif

    @if (session('warning'))
        <div id="warningAlert" class="alert alert-warning alert-dismissible fade show">
            {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <script>
            setTimeout(function () { $('#warningAlert').alert('close'); }, 5000);
        </script>
    @endif
    @if (session('error'))
        <div id="warningAlert" class="alert alert-warning alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <script>
            setTimeout(function () { $('#warningAlert').alert('close'); }, 5000);
        </script>
    @endif
@stop

@section('content')
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
             <tr>
                <th>Cliente</th>
                <th>Cliente 3ro</th>
                <th>Destino</th>
                <th>Remito</th>
                <th>Nombre</th>
                <th>Fecha de recepcion</th>
                <th>Bultos</th>
                <th>Pallets normales</th>
                <th>Pallets grandes</th>
                <th>Estado de envio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cargas as $carga)
                @include('stock.admin.modals.delete')

                @if ($carga->estadoActual?->estado =='ENTREGADO')
                    <tr class="bg-success">
                @elseif($carga->estadoActual?->estado =='RECHAZADO')
                    <tr class="bg-danger">
                @else
                    <tr>
                @endif   
                    <td>{{ $carga->client->name }}</td>
                    <td>{{ $carga->cliente_tercero?->nombre ?? 'no asignado' }}</td>
                    <td>{{ $carga->destino }}</td>
                    <td>{{ $carga->remito?->numero ?? 'no asignado' }}</td>
                    <td>{{ $carga->nombre }}</td>
                    <td>{{ $carga->fecha_de_recepcion?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $carga->cantidad_bulto }}</td>
                    <td>{{ $carga->cantidad_pallet_normal }}</td>
                    <td>{{ $carga->cantidad_pallet_grande }}</td>
                    <td>{{ $carga->estadoActual?->estado }}</span>  {{ $carga->estadoActual?->horario }} </td>
                    <td>
                        <a href="{{ Route('showcarga', $carga->id) }}" class="btn btn-sm btn-info">Ver</a>
                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deletecarga{{ $carga->id }}">Eliminar</button>               
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
   <div class="modal fade" id="buscarcortedeoperaciones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Corte de operaciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('cortedeoperaciones') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <label for="corte_client_id">Cliente:</label>
                    <select name="client_id" id="corte_client_id" class="form-control mb-2" required>
                        <option value="">Seleccione un cliente</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>

                    <label for="corte_fecha">Fecha de recepcion:</label>
                    <input type="date" name="fecha" id="corte_fecha" class="form-control mb-2" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-danger">Consultar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop
@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
    $('.data-table').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
        }
    });

    $('#client_id').select2({
        allowClear: true
    });
     $('#filtro_tercero').select2({
        allowClear: true
    });

    $('#client_id').on('change', function () {
        const clienteId = $(this).val();
        const $wrapper = $('#wrapper_tercero');
        const $tercero = $('#filtro_tercero');

        if (!clienteId) {
            $wrapper.hide();
            $tercero.val('');
            return;
        }

        $wrapper.show();
        $tercero.find('option[data-client]').each(function () {
            const visible = $(this).data('client') == clienteId;
            $(this).prop('hidden', !visible);
            if (!visible && $(this).is(':selected')) {
                $tercero.val('');
            }
        });
    }).trigger('change');

    @isset($corte)
        $('#cortedeoperaciones').modal('show');
    @endisset
    $('#del_cliente').on('change', function () {
        const clienteId = $(this).val();
        const $wrapper = $('#del_wrapper_tercero');
        const $tercero = $('#del_tercero');

        $('#del_info_tercero').hide();
        $('#del_cliente_tercero_id').val('');
        $('#del_btn_eliminar').prop('disabled', true);
        $tercero.val('');

        if (!clienteId) {
            $wrapper.hide();
            return;
        }

        $wrapper.show();
        $tercero.find('option[data-client]').each(function () {
            $(this).prop('hidden', $(this).data('client') != clienteId);
        });
    });

    $('#del_tercero').on('change', function () {
        const $opt = $(this).find('option:selected');
        const id = $(this).val();

        if (!id) {
            $('#del_info_tercero').hide();
            $('#del_cliente_tercero_id').val('');
            $('#del_btn_eliminar').prop('disabled', true);
            return;
        }

        $('#del_nombre_tercero').text($opt.data('nombre'));
        $('#del_info_tercero').show();
        $('#del_cliente_tercero_id').val(id);
        $('#del_btn_eliminar').prop('disabled', false);
    });
});
</script>
    
@stop