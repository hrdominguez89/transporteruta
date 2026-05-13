<div class="modal fade" id="updateTime{{ $travelCertificate->id }}" tabindex="-1" aria-labelledby="updateTime"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Actualizar Constancia de Viaje</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
        
       </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('updateTimeTravelCertificate', $travelCertificate->id) }}" class="form-group"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <small id="number_help_{{ $travelCertificate->id }}" class="text-danger" style="display:none;"></small>
                    <div id="hora_salida_div">
                        <label>Hora de salida:</label>
                        <input class="form-control mb-2" type="datetime-local" name="horaSalida" value="{{ $travelCertificate->horaLLegada ? \Carbon\Carbon::parse($travelCertificate->horaLLegada)->format('Y-m-d\TH:i') : '' }}">
                    </div>

                    <div id="hora_llegada_div">
                        <label>Hora de llegada:</label>
                        <input class="form-control mb-2" type="datetime-local" name="horaLlegada" value="{{ $travelCertificate->horaLLegada ? \Carbon\Carbon::parse($travelCertificate->horaLLegada)->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    <button class="btn btn-sm btn-success">
                        Aceptar
                    </button>
                   <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </form>
            </div>
        </div>
    </div>
</div>