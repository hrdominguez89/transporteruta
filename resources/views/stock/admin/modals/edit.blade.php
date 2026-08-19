<div class="modal fade" id="updateModal{{ $carga->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('editstock') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $carga->id }}">

                <div class="modal-header bg-danger">
                    <h5 class="modal-title">Actualizar mercaderia</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <label for="nombre{{ $carga->id }}">Nombre:<span class="text-danger"> *</span></label>
                    <input id="nombre{{ $carga->id }}" type="text" name="nombre" class="form-control mb-2"
                        value="{{ old('nombre', $carga->nombre) }}" required>

                    <label for="destino{{ $carga->id }}">Destino:</label>
                    <input id="destino{{ $carga->id }}" type="text" name="destino" class="form-control mb-2"
                        value="{{ old('destino', $carga->destino) }}">

                    <label for="fecha_de_recepcion{{ $carga->id }}">Fecha de recepcion:</label>
                    <input id="fecha_de_recepcion{{ $carga->id }}" type="date" name="fecha_de_recepcion"
                        class="form-control mb-2"
                        value="{{ old('fecha_de_recepcion', $carga->fecha_de_recepcion?->format('Y-m-d')) }}">

                    <label for="fecha_de_entrega{{ $carga->id }}">Fecha de entrega:</label>
                    <input id="fecha_de_entrega{{ $carga->id }}" type="date" name="fecha_de_entrega"
                        class="form-control mb-2"
                        value="{{ old('fecha_de_entrega', $carga->fecha_de_entrega?->format('Y-m-d')) }}">


                    <label for="cliente_tercero_id{{ $carga->id }}">Cliente tercero:</label>
                    <select id="cliente_tercero_id{{ $carga->id }}" name="cliente_tercero_id" class="form-control mb-2">
                        <option value="">---- Sin tercero ----</option>
                        @foreach ($clientes_terceros as $tercero)
                            <option value="{{ $tercero->id }}"
                                {{ old('cliente_tercero_id', $carga->cliente_tercero_id) == $tercero->id ? 'selected' : '' }}>
                                {{ $tercero->nombre }}
                            </option>
                        @endforeach
                    </select>

                    <label for="driver{{ $carga->id }}">Chofer:</label>
                    <select id="driver{{ $carga->id }}" name="driver_id" class="form-control mb-2" required>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}"
                                {{ old('estado_de_envio', $carga->driver) == $driver ? 'selected' : '' }}>
                                {{ ucfirst(strtolower($driver->name)) }}
                            </option>
                        @endforeach
                    </select>
                                        
                    <div id="motivoDiv{{ $carga->id }}" style="display: none">
                        <label for="motivo{{ $carga->id }}">Motivo de rechazo:</label>
                        <input id="motivo{{ $carga->id }}" type="text" name="motivo" class="form-control mb-2"
                            value="{{ old('motivo', $carga->motivo) }}">
                    </div>

                    <label for="liquidado{{ $carga->id }}">Liquidado:</label>
                    <select id="liquidado{{ $carga->id }}" name="liquidado" class="form-control mb-2">
                        <option value="0" {{ old('liquidado', $carga->liquidado) == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('liquidado', $carga->liquidado) == 1 ? 'selected' : '' }}>Sí</option>
                    </select>

                    <label for="travel_certificate_id{{ $carga->id }}">Constancia de viaje:</label>
                    <select id="travel_certificate_id{{ $carga->id }}" name="travel_certificate_id" class="form-control mb-2">
                        <option value="">---- Sin constancia ----</option>
                        @foreach ($travel_certificates as $tc)
                            <option value="{{ $tc->id }}"
                                {{ old('travel_certificate_id', $carga->travel_certificate_id) == $tc->id ? 'selected' : '' }}>
                                N° {{ $tc->number ?? $tc->id }} 
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>

(function () {
    const estado = document.getElementById('estado_de_envio{{ $carga->id }}');
    const motivo = document.getElementById('motivoDiv{{ $carga->id }}');

    function toggleMotivo() {
        motivo.style.display = estado.value === 'RECHAZADO' ? 'block' : 'none';
    }

    estado.addEventListener('change', toggleMotivo);
    toggleMotivo();
})();
</script>