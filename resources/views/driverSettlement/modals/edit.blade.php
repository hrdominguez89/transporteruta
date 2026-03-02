<div class="modal fade" id="liquidatedEditModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="col-12">
                <form id="formEditarLiquidacion" action="{{ Route('editarDriverSettlement') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edicion</h5>
                    </div>
                    <div class="modal-body">
                        <input readonly name="id" value="{{ $driverSettlement->id }}" type="hidden">
                        <label class="form-label">Desde</label>
                        <input class="form-control" type="date" id="editDesde" name="desde" value="{{ $driverSettlement->dateFrom ?? null }}">
                        <label class="form-label">Hasta</label>
                        <input class="form-control" type="date" id="editHasta" name="hasta" value="{{ $driverSettlement->dateTo ?? null }}">
                        <label class="form-label">Tipo</label>
                        <select id="editTipoLiquidacion" class="custom-select form-select-lg mb-3" name="tipo">
                            <option selected value="{{ $driverSettlement->tipo ?? '' }}">{{ $driverSettlement->tipo ?? 'Seleccione una opcion' }}</option>
                            <option value="semanal">Semanal</option>
                            <option value="quincenal">Quincenal</option>
                            <option value="mensual">Mensual</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary btn-sm" type="submit">Aceptar</button>
                        <button class="btn btn-danger btn-sm" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
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
    document.getElementById("formEditarLiquidacion").addEventListener("submit", function (e) {
        e.preventDefault();

        const tipo  = document.getElementById("editTipoLiquidacion").value;
        const desde = document.getElementById("editDesde").value;
        const hasta = document.getElementById("editHasta").value;

        if (!tipo) {
            this.submit();
            return;
        }

        const diferencia = (new Date(hasta) - new Date(desde)) / (1000 * 60 * 60 * 24);
        const rango = rangoValido[tipo];

        if (!rango || diferencia < rango.min || diferencia > rango.max) {
            showToast("No coincide el tipo con el rango de fechas");
        } else {
            this.submit();
        }
    });
</script>