<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Generar Liquidacion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('generateDriverSettlement') }}" class="form-group" method="POST">
                    @csrf
                    <label for="number">Numero (Sistema antiguo):</label>
                    <input type="number" name="number" id="number" placeholder="Ingrese número del sistema antiguo" class="form-control mb-2">
                    <label for="driverId">Chofer:<span class="text-danger"> *</span></label>
                    <select name="driverId" class="form-control mb-2" required>
                        <option value="">---- Seleccione una opcion ----</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                        @endforeach
                    </select>
                    <label for="dateFrom">Obtener datos desde:<span class="text-danger"> *</span></label>
                    <input type="date" name="dateFrom" class="form-control mb-2" required>
                    <label for="dateTo">Obtener datos hasta:<span class="text-danger"> *</span></label>
                    <input type="date" name="dateTo" class="form-control mb-2" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Generar</button>
                </form>
            </div>
        </div>
    </div>
</div>
