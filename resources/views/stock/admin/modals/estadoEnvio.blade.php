<div class="modal fade" id="updateEstadoEnvioModal{{ $carga->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('editEstadoEnviostock') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $carga->id }}">

                <div class="modal-header bg-danger">
                    <h5 class="modal-title">Actualizar estado de envio de mercaderia</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <label for="horario{{ $carga->id }}">Fecha de estado:</label>
                    <input id="horario{{ $carga->id }}" type="datetime-local" name="horario"
                    class="form-control mb-2"
                    value="{{ old('horario', $estado_envio?->horario?->format('Y-m-d\TH:i')) }}" required>



                    <label for="estado_de_envio{{ $carga->id }}">Estado de envio:</label>
                    <select id="estado_de_envio{{ $carga->id }}" name="estado_de_envio" class="form-control mb-2" required>
                        @foreach (['DEPOSITO',  'AVISADO','COORDINADO','TRANSITO','ENTREGADO', 'RECHAZADO'] as $estado)
                            <option value="{{ $estado }}"
                                {{ old('estado_de_envio', $estado_envio?->estado) == $estado ? 'selected' : '' }}>
                                {{ ucfirst(strtolower($estado)) }}
                            </option>
                        @endforeach
                    </select>
                                        
                    <div id="motivoDiv{{ $carga->id }}" style="display: none">
                        <label for="motivo{{ $carga->id }}">Motivo de rechazo:</label>
                        <input id="motivo{{ $carga->id }}" type="text" name="motivo" class="form-control mb-2"
                            value="{{ old('motivo', $carga->motivo) }}">
                    </div>
                   
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