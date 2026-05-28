<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet">

<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="generateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="generateModalLabel">Generar Liquidacion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ Route('generateDriverSettlement') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="d-block">Generar para:<span class="text-danger"> *</span></label>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="modo" id="modoTodos" value="todos" checked>
                                <label class="form-check-label" for="modoTodos">Todos</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="modo" id="modoPropios" value="propios">
                                <label class="form-check-label" for="modoPropios">Propios</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="modo" id="modoEventuales" value="eventuales">
                                <label class="form-check-label" for="modoEventuales">Eventuales</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="modo" id="modoAlgunos" value="algunos">
                                <label class="form-check-label" for="modoAlgunos">Algunos</label>
                            </div>
                        </div>

                        <label for="driverId">Chofer:</label>
                        <select name="driverId[]" id="driverId" class="form-control mb-2" multiple disabled style="width:100%;">
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                            @endforeach
                        </select>

                        <label for="dateFrom">Obtener datos desde:<span class="text-danger"> *</span></label>
                        <input type="date" id="dateFrom" name="dateFrom" class="form-control mb-2" required>

                        <label for="dateTo">Obtener datos hasta:<span class="text-danger"> *</span></label>
                        <input type="date" id="dateTo" name="dateTo" class="form-control mb-2" required>

                        <label for="tipoliquidacion">Tipo</label>
                        <select id="tipoliquidacion" name="tipoliquidacion" class="custom-select mb-3">
                            <option value="" selected>Seleccione una opcion</option>
                            <option value="semanal">Semanal</option>
                            <option value="quincenal">Quincenal</option>
                            <option value="mensual">Mensual</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" id="btngenerar" class="btn btn-sm btn-primary">Generar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
    $(function () {

    const driverSelect = document.getElementById("driverId");

    $('#driverId').select2({
        placeholder: 'Seleccione choferes',
        width: '100%',
        dropdownParent: $('#generateModal .modal-body')
    });

    $(document).on('change', 'input[name="modo"]', function () {
    const esAlgunos = this.value === "algunos";
    
    if (!esAlgunos) {
        $('#driverId').val(null).trigger('change');
    }
    $('#driverId').prop('disabled', !esAlgunos);
});

    function showToast(mensaje) {
        const existente = document.getElementById("toastError");
        if (existente) existente.remove();

        document.body.insertAdjacentHTML("beforeend", `
            <div id="toastError" role="alert" aria-live="assertive" aria-atomic="true"
                 class="toast" data-autohide="false"
                 style="position:fixed; top:20px; right:20px; z-index:9999; min-width:300px;">
                <div class="toast-header bg-danger text-white">
                    <strong class="mr-auto">Atención</strong>
                    <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body">${mensaje}</div>
            </div>
        `);

        $("#toastError").toast("show");
    }

    const rangoValido = {
        semanal:   { min: 1, max: 8 },
        quincenal: { min: 9, max: 16 },
        mensual:   { min: 27, max: 31 },
    };

    document.getElementById("btngenerar").addEventListener("click", function (e) {
        e.preventDefault();

        const modo = document.querySelector('input[name="modo"]:checked').value;

        if (modo === "algunos" && driverSelect.selectedOptions.length === 0) {
            showToast("Seleccione al menos un chofer");
            return;
        }

        const tipo  = document.getElementById("tipoliquidacion").value;
        const desde = document.getElementById("dateFrom").value;
        const hasta = document.getElementById("dateTo").value;

        if (!tipo) {
            this.closest("form").submit();
            return;
        }

        const diferencia = (new Date(hasta) - new Date(desde)) / (1000 * 60 * 60 * 24);
        const rango = rangoValido[tipo];

        if (diferencia < rango.min || diferencia > rango.max) {
            showToast("No coincide el tipo con el rango de fechas");
        } else {
            this.closest("form").submit();
        }
    });
});
</script>