<div class="modal fade" id="updateModal{{ $travelCertificate->id }}" tabindex="-1" aria-labelledby="updateModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Actualizar Constancia de Viaje</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('updateTravelCertificate', $travelCertificate->id) }}" class="form-group"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <label for="number">Numero(Sistema Antiguo):</label>
                    <input type="number" name="number" id="tc_number_{{ $travelCertificate->id }}" class="form-control mb-2"
                        value="{{ $travelCertificate->number }}">
                    <small id="number_help_{{ $travelCertificate->id }}" class="text-danger" style="display:none;"></small>
                    <label for="date">Fecha:<span class="text-danger"> *</span></label>
                    <input type="date" name="date" class="form-control mb-2"
                        value="{{ $travelCertificate->date }}" required>
                    <label for="destiny">Destino:<span class="text-danger"> *</span></label>
                    <input type="text" name="destiny" class="form-control mb-2"
                        value="{{ $travelCertificate->destiny }}" required>
                    <label for="clientId">Cliente:<span class="text-danger"> *</span></label>
                    <select name="clientId" class="form-control mb-2" required>
                        <option value="{{ $travelCertificate->client->id }}">{{ $travelCertificate->client->name }}
                        </option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ $travelCertificate->client->id == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                    <label for="driverId">Chofer:<span class="text-danger"> *</span></label>
                    <select name="driverId" class="form-control mb-2" required>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ $driver->id == $travelCertificate->driverId ?'selected':'' }}>{{ $driver->name }} - {{ $driver->percent }} %
                            </option>
                        @endforeach
                    </select>


                    <!-- Tipo de comisión -->
                    <label for="commission_type">Tipo de Comisión:<span class="text-danger"> *</span></label>
                    <select name="commission_type" id="commission_type" class="form-control mb-2" required>
                        <option value="porcentaje pactado"
                            {{ $travelCertificate->commission_type == 'porcentaje pactado' ? 'selected' : '' }}>
                            Porcentaje pactado con el chofer</option>
                        <option value="porcentaje"
                            {{ $travelCertificate->commission_type == 'porcentaje' ? 'selected' : '' }}>Otro
                            porcentaje</option>
                        <option value="monto fijo"
                            {{ $travelCertificate->commission_type == 'monto fijo' ? 'selected' : '' }}>Otro Monto
                            Fijo</option>
                    </select>

                    <!-- Porcentaje o Monto Fijo -->
                    <div id="percent_div"
                        style="display: {{ $travelCertificate->commission_type == 'porcentaje' ? 'block' : 'none' }}">
                        <label for="percent">Porcentaje %:<span class="text-danger"> *</span></label>
                        <input type="number" value="{{ @$travelCertificate->percent }}" id="percent" step="0.01"
                            name="percent" placeholder="Ej: 30,25" class="form-control mb-2"
                            {{ $travelCertificate->commission_type == 'porcentaje' ? 'required' : '' }}>
                    </div>

                    <div id="fixed_amount_div"
                        style="display: {{ $travelCertificate->commission_type == 'monto fijo' ? 'block' : 'none' }};">
                        <label for="fixed_amount">Monto Fijo en $:<span class="text-danger"> *</span></label>
                        <input type="number" value="{{ @$travelCertificate->fixed_amount }}" step="0.01"
                            id="fixed_amount" name="fixed_amount" placeholder="Ej: 15000" class="form-control mb-2"
                            {{ $travelCertificate->commission_type == 'monto fijo' ? 'required' : '' }}>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-success">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById("commission_type").addEventListener("change", function() {
        var commissionType = this.value;
        if (commissionType === "porcentaje") {
            // Mostrar campo de porcentaje y ocultar monto fijo
            document.getElementById("percent_div").style.display = "block";
            document.getElementById("fixed_amount_div").style.display = "none";
            document.getElementById("percent").setAttribute("required", "required");
            document.getElementById("fixed_amount").removeAttribute("required");
        } else if (commissionType === "monto fijo") {
            // Mostrar campo de monto fijo y ocultar porcentaje
            document.getElementById("percent_div").style.display = "none";
            document.getElementById("fixed_amount_div").style.display = "block";
            document.getElementById("fixed_amount").setAttribute("required", "required");
            document.getElementById("percent").removeAttribute("required");

        } else {
            // Si no se selecciona ninguna opción, ocultar ambos campos
            document.getElementById("percent_div").style.display = "none";
            document.getElementById("fixed_amount_div").style.display = "none";
            document.getElementById("percent").removeAttribute("required");
            document.getElementById("fixed_amount").removeAttribute("required");

        }
    });
    // Validación de número único en el modal de actualizar
    (function(){
        var id = '{{ $travelCertificate->id }}';
        var numberInput = document.getElementById('tc_number_' + id);
        var numberHelp = document.getElementById('number_help_' + id);
        var form = numberInput ? numberInput.closest('form') : null;

        function checkNumber(done){
            var val = numberInput.value;
            if (!val) {
                numberHelp.style.display = 'none';
                return done && done(false);
            }
            fetch("{{ route('checkTravelCertificateNumber') }}?number="+encodeURIComponent(val)+"&id="+encodeURIComponent(id), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r){ return r.json(); })
            .then(function(json){
                if (json.exists) {
                    numberHelp.textContent = 'El número ya existe en la base de datos.';
                    numberHelp.style.display = 'block';
                    return done && done(true);
                } else {
                    numberHelp.style.display = 'none';
                    return done && done(false);
                }
            })
            .catch(function(){
                numberHelp.style.display = 'none';
                return done && done(false);
            });
        }

        if (numberInput) {
            numberInput.addEventListener('blur', function(){ checkNumber(); });
            numberInput.addEventListener('change', function(){ checkNumber(); });
        }

        if (form) {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                checkNumber(function(exists){
                    if (exists) {
                        numberInput.focus();
                        return; // don't submit
                    }
                    form.submit();
                });
            });
        }
    })();
</script>
