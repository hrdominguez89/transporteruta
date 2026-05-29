<div class="modal fade" id="vehicleModal{{ $driver->id }}" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar vehiculo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ Route('setVehicleToDriver', $driver->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <label for="vehicleId">Vehiculo:<span class="text-danger"> *</span></label>
            <select name="vehicleId" class="form-control mb-2">
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" >{{ $vehicle->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-sm btn-success">Agregar</button>
        </div>
      </form>
    </div>
  </div>
</div>
