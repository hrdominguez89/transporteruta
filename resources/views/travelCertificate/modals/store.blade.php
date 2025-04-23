<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Agregar Constancia de Viaje</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('storeTravelCertificate') }}" class="form-group" method="POST">
                    @csrf
                    <label for="number">Numero(Sistema Antiguo):</label>
                    <input type="number" placeholder="Ingrese numero del sistema antiguo" name="number" class="form-control mb-2">

                    <label for="date">Fecha:<span class="text-danger"> *</span></label>
                    <input type="date" name="date" class="form-control mb-2" required>

                    <label for="destiny">Destino:<span class="text-danger"> *</span></label>
                    <input type="text" placeholder="Ingrese destino" name="destiny" class="form-control mb-2" required>

                    <label for="clientId">Cliente:<span class="text-danger"> *</span></label>
                    <select name="clientId" class="form-control mb-2" required>
                        <option value="">Seleccione un cliente</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>

                    <label for="driverId">Chofer:<span class="text-danger"> *</span></label>
                    <select name="driverId" class="form-control mb-2" required>
                        <option value="">Seleccione un cofer</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }} - {{ $driver->percent }} %</option>
                        @endforeach
                    </select>

                    <!-- Tipo de comisión -->
                    <label for="commission_type">Tipo de Comisión:<span class="text-danger"> *</span></label>
                    <select name="commission_type" id="commission_type" class="form-control mb-2" required>
                        <option value="porcentaje pactado">Porcentaje pactado con del chofer</option>
                        <option value="porcentaje">Otro porcentaje</option>
                        <option value="monto fijo">Otro Monto Fijo</option>
                    </select>

                    <!-- Porcentaje o Monto Fijo -->
                    <div id="percent_div" style="display: none;">
                        <label for="percent">Porcentaje %:<span class="text-danger"> *</span></label>
                        <input type="number" id="percent" step="0.01" name="percent" placeholder="Ej: 30,25" class="form-control mb-2">
                    </div>
        
                    <div id="fixed_amount_div" style="display: none;">
                        <label for="fixed_amount">Monto Fijo en $:<span class="text-danger"> *</span></label>
                        <input type="number" step="0.01" id="fixed_amount" name="fixed_amount" placeholder="Ej: 15000" class="form-control mb-2">
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Mostrar u ocultar los campos dependiendo de la selección del tipo de comisión
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
</script>
