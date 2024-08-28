<div class="modal fade" id="updateModal{{ $driver->id }}" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Editar Chofer</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('updateDriver', $driver->id) }}" class="form-group" method="POST">
            @csrf
            @method('PUT')
            <label for="name">Nombre:</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." value="{{ $driver->name }}" required>
            <label for="dni">DNI/CUIT:</label>
            <input type="text" name="dni" class="form-control mb-2" placeholder="Ingrese el DNI/CUIT..." value="{{ $driver->dni }}" required>
            <label for="address">Direccion:</label>
            <input type="text" name="address" class="form-control mb-2" placeholder="Ingrese la direccion..." value="{{ $driver->address }}" required>
            <label for="city">Ciudad:</label>
            <input type="text" name="city" class="form-control mb-2" placeholder="Ingrese la ciudad..." value="{{ $driver->city }}" required>
            <label for="phone">Telefono:</label>
            <input type="text" name="phone" class="form-control mb-2" placeholder="Ingrese el telefono..." value="{{ $driver->phone }}" required>
            <label for="percent">Porcentaje:</label>
            <input type="number" name="percent" class="form-control mb-2" placeholder="Ingrese el porcentaje de viaje..." value="{{ $driver->percent }}" required>            
            <label for="type">Tipo:</label>
            <select name="type" class="form-control mb-2" required>
                <option  value="{{ $driver->type }}">{{ $driver->type }}</option>
                <option value="PROPIO">Propio</option>
                <option value="TERCERO">Tercero</option>
            </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-success">Actualizar</button>
        </form>
      </div>
    </div>
  </div>
</div>