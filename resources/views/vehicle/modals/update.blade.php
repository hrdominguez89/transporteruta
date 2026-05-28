<div class="modal fade" id="updateModal{{ $vehicle->id }}" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Editar Vehiculo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('updateVehicle', $vehicle->id) }}" class="form-group" method="POST">
            @csrf
            @method('PUT')
            <label for="name">Nombre:</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." value="{{ $vehicle->name }}" required>

            <label for="tipo">Tipo:</label>
            <input type="text" name="tipo" class="form-control mb-2" placeholder="Ingrese el tipo..." value="{{ $vehicle->tipo }}">

            <label for="marca">Marca:</label>
            <input type="text" name="marca" class="form-control mb-2" placeholder="Ingrese la marca..." value="{{ $vehicle->marca }}">

            <label for="modelo">Modelo:</label>
            <input type="text" name="modelo" class="form-control mb-2" placeholder="Ingrese el modelo..." $vehicle->modelo>

            <label for="anio">Año:</label>
            <input type="number" min="1900" max="2100" name="anio" class="form-control mb-2" placeholder="Ingrese el año..." value="{{ (int)$vehicle->anio?->format('Y') }}">

            <label for="driverId">Chofer:<span class="text-danger"> *</span></label>
            <select name="driverId" class="form-control mb-2" required>
                <option value="{{ $vehicle->driver?->name }}">{{ $vehicle?->driver?->name ?? "Seleccione un cofer"}}</option>
                @foreach ($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }} </option>
                @endforeach
            </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-success">Actualizar</button>
        </form>
      </div>
    </div>
  </div>
</div>