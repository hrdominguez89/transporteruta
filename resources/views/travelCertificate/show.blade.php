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
                <button class="btn btn-sm btn-danger col-2 mr-2" data-toggle="modal" data-target="#storeModal">
                    Agregar Nuevo Item
                </button>

                {{-- NUEVO (10/2025): botón para carga MÚLTIPLE de remitos (no afectan importes) --}}
                <button class="btn btn-sm btn-primary col-2 mr-2" data-toggle="modal" data-target="#remitosMultipleModal">
                    Cargar Remitos
                </button>

                <button class="btn btn-sm btn-success col-2" data-toggle="modal"
                    data-target="#updateModal{{ $travelCertificate->id }}">Actualizar Constancia</button>
            </div>
            <div class="col-12 text-right mb-2">
                <a href="{{ Route('travelCertificatePdf', $travelCertificate->id) }}" class="btn btn-sm btn-info col-4">
                    Generar PDF
                </a>
            </div>
        @else
            <div class="col-12 text-right mb-2">
                <a href="{{ Route('travelCertificatePdf', $travelCertificate->id) }}" class="btn btn-sm btn-info col-4">
                    Generar PDF
                </a>
            </div>
            <div class="col-12 text-left mb-2">
                <strong class="text-danger">La constancia ha sido agregada a la factura
                    <a href="{{ Route('showInvoice', $travelCertificate->invoice->id) }}">
                        {{ number_format($travelCertificate->invoice->id, 0, ',', '.') }}
                    </a>,
                    no se pueden realizar modificaciones.</strong>
            </div>
        @endif

        @include('travelItem.modals.store')
        @include('travelCertificate.modals.update')

        
        <div class="modal fade" id="remitosMultipleModal" tabindex="-1" aria-labelledby="remitosMultipleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="{{ route('travelItems.storeMultipleRemitos', $travelCertificate->id) }}" class="modal-content">
              @csrf
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="remitosMultipleModalLabel">Cargar Remitos (múltiples)</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>

              <div class="modal-body" id="div_remitos">
                @if(session('remitos_result'))
                  <div class="alert alert-info mb-2">
                    {{ session('remitos_result') }}
                  </div>
                @endif

                <p class="mb-2">Ingrese un numero de remito <button type="button" class="btn btn-sm btn-primary"id="agregar_remitos">+</button></p>
                <small class="text-muted d-block mt-2">
                  Duplicados dentro de la misma constancia serán ignorados automáticamente.
                </small>
                <input name="remitos[]" class="form-control col-md-10" id="remito_0" pattern="\S*" ></input>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
              </div>
            </form>
          </div>
        </div>
@stop

@section('content')
    <h4>Información del viaje</h4>
    <table class="table table-sm table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Cliente</th>
                <th>Chofer</th>
                <th>Vehiculo</th>
                <th>Destino</th>
                <th>Fecha</th>
                <th>Horario de salida</th>
                <th>Horario de llegada</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $travelCertificate->client->name }}</td>
                <td>{{ $travelCertificate->driver->name }}</td>
                <td>{{ $travelCertificate->vehicle?->name }}</td>
                <td>{{ $travelCertificate->destiny }}</td>
                <td>{{ \Carbon\Carbon::parse($travelCertificate->date)->format('d/m/Y') }}</td>
                <td>{{ $travelCertificate->horaSalida }}</td>
                <td>{{ $travelCertificate->horaLLegada }}</td>
            </tr>
        </tbody>
    </table>
    <h4>Resumen de costos</h4>
    <table class="table table-sm table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Pago al Chofer</th>
                <th>Precio (Sin IVA)</th>
                <th>IVA</th>
                <th>Precio Total (Con IVA)</th>
                <th>Facturado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>$&nbsp;{{ number_format($travelCertificate->driverPayment, 2, ',', '.') }}</td>
                <td>$&nbsp;{{ number_format($travelCertificate->total, 2, ',', '.') }}</td>
                <td>$&nbsp;{{ number_format($travelCertificate->iva, 2, ',', '.') }}</td>
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
                @php
                    // REFACTORIZACIÓN: monto mostrado por ítem
                    // 1) DESCUENTO → computed_price (negativo)
                    // 2) ADICIONAL → display_price (% de FIJO)
                    // 3) Resto → price
                    $monto = $travelItem->computed_price ?? $travelItem->display_price ?? ($travelItem->price ?? 0);
                @endphp
                <tr>
                    <td>{{ $travelItem->type }}</td>

                    {{-- REFACTORIZACIÓN: descripción enriquecida para DESCUENTO, fallback al texto original --}}
                    <td class="text-center">
                        {{ $travelItem->computed_description ?? $travelItem->description }}
                    </td>

                    {{-- REFACTORIZACIÓN: precio calculado (descuento negativo en rojo) --}}
                    <td data-order="{{ $monto }}">
                        <span class="{{ $monto < 0 ? 'text-danger' : '' }}">
                            $&nbsp;{{ ($monto < 0 ? '-' : '') . number_format(abs($monto), 2, ',', '.') }}
                        </span>
                    </td>

                    <td >
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
        $(document).ready(function() {
            const agregarRemito = document.getElementById('agregar_remitos');
            agregarRemito.addEventListener('click',
                function(){
                    input = document.createElement('input');
                    numero = Number(obtenerUltimoNumero());
                    numero += 1;
                    input = crearInput(numero);
                    btnDelet = crearBtnEleminar(input.id);
                    divRemitos = document.getElementById('div_remitos');
                    let row = config(input,btnDelet);
                    divRemitos.appendChild(row);
                }
            );
            function obtenerUltimoNumero()
            {
                remitos = document.querySelectorAll('[name="remitos[]"]');
                idMax = 0;
                remitos.forEach((r)=>{
                    idStr = r.id;
                    id = idStr.split('_')[1];
                    if (idMax<id)
                    {
                        idMax = id;
                    }
                });
                return idMax;
            }
            function crearInput(numero)
            {
                input.id = "remito_" + numero;
                input.name ="remitos[]";
                input.className ="form-control col-md-12";
                input.style.marginTop = "10px";
                input.style.marginBottom = "10px";
                return input;
            }
            function crearBtnEleminar(idInput)
            {
                btn = document.createElement('button');
                btn.name = idInput;
                btn.type = "button";
                btn.className = "btn btn-danger col-md-2";
                btn.innerText = "-";
                btn.style.marginTop = "10px";
                btn.style.marginBottom = "10px";
                btn.addEventListener('click',function(){
                    const idInput = this.name; 
                    const remito = document.getElementById(idInput);
                    this.remove();
                    remito.remove();
                });
                return btn;
            }
            function config(input,btnDelet)
            {
                row = document.createElement('div');
                row.className = "row";
                div_input = document.createElement('div');
                div_input.className= "col-md-10";
                div_input.appendChild(input);
                div_btn = document.createElement('div');
                div_btn.className = "col-ms-2";
                div_btn.style.textAlign = "center";
                div_btn.appendChild(btnDelet);
                row.appendChild(div_input);
                row.appendChild(div_btn);
                return row;
            }
        });
    </script>
@stop


