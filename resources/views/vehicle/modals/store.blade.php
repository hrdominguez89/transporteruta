<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Vehiculo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('storeVehicle') }}" class="form-group" method="POST">
            @csrf
            <label for="name">Nombre:</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." required>
            <label for="tipo">Tipo:</label>
            <input type="text" name="tipo" class="form-control mb-2" placeholder="Ingrese el tipo..." >

            <label for="marca">Marca:</label>
            <input type="text" name="marca" class="form-control mb-2" placeholder="Ingrese la marca..." >

            <label for="modelo">Modelo:</label>
            <input type="text" name="modelo" class="form-control mb-2" placeholder="Ingrese el modelo..." >

            <label for="anio">Año:</label>
            <input type="number" min="1900" max="2100" name="anio" class="form-control mb-2" placeholder="Ingrese el año..." >

            <label for="driverId">Chofer:<span class="text-danger"> *</span></label>
                    <select name="driverId" class="form-control mb-2" required>
                        <option value="">Seleccione un chofer</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                  @endforeach
            </select>
          </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>