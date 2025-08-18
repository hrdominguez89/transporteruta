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
                    <input type="number" placeholder="Ingrese numero del sistema antiguo" name="number" id="tc_number" class="form-control mb-2">
                    <small id="number_help" class="text-danger" style="display:none;"></small>

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
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
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

    // Validar número único vía AJAX antes de enviar el formulario
    (function(){
        var numberInput = document.getElementById('tc_number');
        var numberHelp = document.getElementById('number_help');
        var form = numberInput ? numberInput.closest('form') : null;

        function checkNumber(done){
            var val = numberInput.value;
            if (!val) {
                numberHelp.style.display = 'none';
                return done && done(false);
            }
            fetch("{{ route('checkTravelCertificateNumber') }}?number="+encodeURIComponent(val), {
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
                // On error, allow submit and hide message
                numberHelp.style.display = 'none';
                return done && done(false);
            });
        }

        if (numberInput) {
            // Check on blur and change
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
                    // submit the form normally
                    form.submit();
                });
            });
        }
    })();

    // Reset form and UI state when opening the modal (so fields are cleared each time)
    (function(){
        function resetStoreModal(){
            var modal = document.getElementById('storeModal');
            if (!modal) return;
            var form = modal.querySelector('form');
            if (form) form.reset();
            var numberHelp = modal.querySelector('#number_help');
            if (numberHelp){ numberHelp.style.display = 'none'; numberHelp.textContent = ''; }
            var percentDiv = modal.querySelector('#percent_div');
            if (percentDiv) percentDiv.style.display = 'none';
            var fixedDiv = modal.querySelector('#fixed_amount_div');
            if (fixedDiv) fixedDiv.style.display = 'none';
            var percentInput = modal.querySelector('#percent');
            if (percentInput) { percentInput.removeAttribute('required'); percentInput.value = ''; }
            var fixedInput = modal.querySelector('#fixed_amount');
            if (fixedInput) { fixedInput.removeAttribute('required'); fixedInput.value = ''; }
            var commission = modal.querySelector('#commission_type');
            if (commission) commission.value = 'porcentaje pactado';
            var first = modal.querySelector('input, select, textarea');
            if (first) first.focus();
        }

        // Attach to any element that opens the modal (button[data-target="#storeModal"]) to avoid relying on jQuery
        var openButtons = document.querySelectorAll('button[data-target="#storeModal"], [data-toggle="modal"][data-target="#storeModal"]');
        if (openButtons.length) {
            openButtons.forEach(function(btn){
                btn.addEventListener('click', function(){
                    // small timeout to allow modal open animation to start
                    setTimeout(resetStoreModal, 10);
                });
            });
        } else {
            // fallback: listen document clicks and detect target
            document.addEventListener('click', function(e){
                var trigger = e.target.closest('button[data-target="#storeModal"], [data-toggle="modal"][data-target="#storeModal"]');
                if (trigger) setTimeout(resetStoreModal, 10);
            });
        }
    })();
</script>
