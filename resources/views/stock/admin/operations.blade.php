@extends('adminlte::page')
@section('title', 'Corte operaciones')
@section('content_header')

    <div class="row">
        <a href="{{ Route('stock') }}" class="btn btn-sm btn-secondary mr-2">Volver</a>
        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#costosModal">
        Ver costos
    </button>
    </div>
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
    @php
        $valor_declarado = 0;
        $valor_viajes = 0;
        $porcentaje = 0;
        $cargas = \app\Models\Carga::where('client_id',$data['client_id'])->get();
        foreach ($cargas as $carga)
        {
            $valor_declarado += $carga->remito?->valor_declarado;
            $valor_viajes += $carga->precio;
        }
        
        $porcentaje = (100 * $valor_viajes) / $valor_declarado;

    @endphp
    
@stop
@section('content')
        <h1 class="col-5">Corte de operaciones</h1>

    <div class="row">
        <form action="{{ route('generatealltcoperaciones') }}" method="POST" class="w-100">
            <input type="hidden" value="{{ $data['fecha'] }}" name="fecha">
            <input type="hidden" value="{{ $data['client_id'] }}" name="client_id">
            @csrf
            @method('POST')
            <div class="d-flex justify-content-end">
                <button class="btn btn-sm btn-primary" type="submit">Generar todos los viajes</button>
            </div>
        </form>
    </div>
    <div class="row">
        <div class="col-lg-9">
            <table class="table table-sm table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Cliente 3ro</th>
                <th>Fecha recepción</th>
                <th>Remito</th>
                <th>Destino</th>
                <th>Bultos</th>
                <th>Pallet normal</th>
                <th>Pallet grande</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Carga</th>
            </tr>
        </thead>
        <tbody>
          @foreach ($cargasagrupadas as $driver_id => $fechas)
            <tr class="bg-secondary text-white">
                <td colspan="10" class="text-left font-weight-bold">
                    Chofer: {{ $fechas->first()->first()?->driver?->name  ?: 'Sin asignar' }}
                </td>
            </tr>

            @foreach ($fechas as $fecha => $cargas)
                @php
                    $valor_total = 0; 
                    $bultos = 0;
                    $bultos_total = 0;
                    $pallets_normales = 0;
                    $pallets_normales_total = 0;
                    $pallets_grandes = 0;
                    $pallets_grandes_total = 0;
                    $valor_declarado = 0;
                @endphp
                <tr class="bg-light">
                    <td colspan="7" class="text-left font-weight-bold">
                        Fecha de recepcion: {{ $fecha ?: 'Sin fecha' }}
                    </td>
                    <td colspan="2">
                        <form action="{{ route('generartc', $fecha) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="driver_id" value="{{ $driver_id }}">
                            <input type="hidden" name="client_id" value="{{ $data['client_id'] }}">
                            <input type="hidden" name="fecha" value="{{ $fecha }}">
                            <button type="submit" class="btn btn-sm btn-info">Generar constancia</button>
                        </form>
                    </td>
                </tr>
                @foreach ($cargas as $carga)
                    @php
                        $valor_total += $carga->precio; 
                        $bultos += $carga->cantidad_bulto;
                        $bultos_total += $carga->cantidad_bulto * $carga->bulto_costo;//  \App\Models\Price::where('type','BULTO')->value('price');
                        $pallets_normales += $carga->cantidad_pallet_normal;
                        $pallets_normales_total += $carga->cantidad_pallet_normal* $carga->pallet_costo;//\App\Models\Price::where('type','PALLET')->value('price');
                        $pallets_grandes += $carga->cantidad_pallet_grande;
                        $pallets_grandes_total += $carga->cantidad_pallet_grande * ($carga->pallet_costo * 1.5);
                        $valor_declarado += $carga->remito->valor_declarado;
                    @endphp
                    <tr>
                        <td>{{ $carga->cliente_tercero->nombre ?? 'no asignado' }}</td>
                        <td>{{ $carga->fecha_de_recepcion ?? '-' }}</td>
                        <td>{{ $carga->remito->numero ?? 'no asignado' }}</td>
                        <td>{{ $carga->destino }}</td>
                        <td>{{ $carga->cantidad_bulto }}</td>
                        <td>{{ $carga->cantidad_pallet_normal }}</td>
                        <td>{{ $carga->cantidad_pallet_grande }}</td>
                        <td>{{ $carga->nombre }}</td>
                        <td>{{ $carga->precio }}</td>
                        <td>
                            <a href="{{ route('showcarga', $carga->id) }}" class="btn btn-sm btn-info">{{ $carga->id }}</a>
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-primary text-white ">
                        <td >Valor declarado :${{ $valor_declarado }}</td>
                        <td>Total bultos :{{ $bultos_total }}</td>
                        <td>Total pallets normales :{{ $pallets_normales_total }}</td>
                        <td>Total pallets grandes :{{  $pallets_grandes_total}}</td>
                        <td>Bultos :{{ $bultos }}</td>
                        <td>Pallets normales :{{ $pallets_normales }}</td>
                        <td>Pallets grandes :{{  $pallets_grandes}}</td>
                        <td  colspan="3">Valor total:${{ $valor_total }} </td>
                    </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
        </div>
        <div class="col-lg-3">
            <h4>Remitos no vinculados</h4>
            <table class="table table-sm table-bordered text-center">
        <thead class="bg-danger">
            <tr><th>Numero de remito</th>
            <th>Valor declarado</th></tr>
        </thead>
        <tbody>
            @foreach ($remitos as $r )
            <tr>
                <th>{{ $r->numero ?? 'sin asignar'}}</th>                
                <th>${{ $r?->valor_declarado  ?? 'sin asignar'}}</th>                
            </tr>
            @endforeach
        </tbody>
    </table>
        </div>
    </div>
    <div class="modal fade" id="costosModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Costos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-bordered text-center">
                    <thead class="bg-danger">
                        <tr>
                            <th>Total de valor declarado</th>
                            <th>Total de viajes</th>
                            <th>Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ number_format($valor_declarado, 2, ',', '.') }}</td>
                            <td>{{ number_format($valor_viajes, 2, ',', '.') }}</td>
                            <td>%{{ number_format($porcentaje, 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
    @stop