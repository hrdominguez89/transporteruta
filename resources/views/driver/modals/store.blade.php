<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Chofer</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('storeDriver') }}" class="form-group" method="POST">
            @csrf
            <label for="name">Nombre:<span class="text-danger"> *</span></label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." required>
            <label for="dni">DNI/CUIT:<span class="text-danger"> *</span></label>
            <input type="text" name="dni" class="form-control mb-2" placeholder="Ingrese el DNI/CUIT..." required>
            <label for="address">Direccion:<span class="text-danger"> *</span></label>
            <input type="text" name="address" class="form-control mb-2" placeholder="Ingrese la direccion..." required>
            <label for="city">Ciudad:<span class="text-danger"> *</span></label>
            <input type="text" name="city" class="form-control mb-2" placeholder="Ingrese la ciudad..." required>
            <label for="phpne">Telefono:<span class="text-danger"> *</span></label>
            <input type="text" name="phone" class="form-control mb-2" placeholder="Ingrese el telefono..." required>
            <label for="phpne">Porcentaje de la Agencia:<span class="text-danger"> *</span></label>
            <input type="number" step="0.01" name="percent" class="form-control mb-2" placeholder="Ingrese el porcentaje de la agencia..." required>
            <label for="type">Tipo:</label>
            <select name="type" class="form-control mb-2" required>
                <option value="">---- Seleccione una opcion ----</option>
                <option value="PROPIO">Propio</option>
                <option value="TERCERO">Tercero</option>
            </select>
            <label for="vehicleId">Vehiculo:</label>
            <select name="vehicleId" class="form-control mb-2">
                <option value="">---- Seleccione una opcion ----</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                @endforeach
            </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>