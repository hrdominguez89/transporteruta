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
            <label for="number">Numero:</label>
            <input type="number" name="number" class="form-control mb-2" required>
            <label for="date">Fecha:</label>
            <input type="date" name="date" class="form-control mb-2" required>
            <label for="driverId">Chofer:</label>
            <select name="driverId" class="form-control mb-2" required>
                <option value="">---- Seleccione una opcion ----</option>
                @foreach($drivers as $driver)
                  <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                @endforeach
            </select>
            <label for="dateFrom">Obetener datos desde:</label>
            <input type="date" name="dateFrom" class="form-control mb-2" required>
            <label for="dateTo">Obtener datos hasta:</label>
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