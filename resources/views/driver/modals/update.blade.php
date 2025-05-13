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
            <label for="name">Nombre:<span class="text-danger"> *</span></label>
            <input id="name" type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." value="{{ $driver->name }}" required>
            <label for="dni">DNI/CUIT:<span class="text-danger"> *</span></label>
            <input id="dni" type="text" name="dni" class="form-control mb-2" placeholder="Ingrese el DNI/CUIT..." value="{{ $driver->dni }}" required>
            <label for="address">Direccion:<span class="text-danger"> *</span></label>
            <input id="address" type="text" name="address" class="form-control mb-2" placeholder="Ingrese la direccion..." value="{{ $driver->address }}" required>
            <label for="city">Ciudad:<span class="text-danger"> *</span></label>
            <input id="city" type="text" name="city" class="form-control mb-2" placeholder="Ingrese la ciudad..." value="{{ $driver->city }}" required>
            <label for="phone">Telefono:<span class="text-danger"> *</span></label>
            <input id="phone" type="text" name="phone" class="form-control mb-2" placeholder="Ingrese el telefono..." value="{{ $driver->phone }}" required>
            <label for="type">Tipo:<span class="text-danger"> *</span></label>
            <select id="type"  name="type" class="form-control mb-2" required>
                <option value="PROPIO" {{ $driver->type == 'PROPIO' ?'selected':'' }}>Propio</option>
                <option value="TERCERO" {{ $driver->type == 'TERCERO' ?'selected':'' }}>Tercero</option>
            </select>
            <div id="porcentaje_div" style="display: {{ $driver->type == 'TERCERO' ?'block':'none' }}">
                <label for="porcentaje">Porcentaje de la Agencia:<span class="text-danger"> *</span></label>
                <input id="porcentaje" type="number" step="0.01" name="percent" class="form-control mb-2"
                    placeholder="Ingrese el porcentaje de la agencia..." value="{{ $driver->percent }}" {{ $driver->type == 'TERCERO' ?'required':'' }}>
            </div>
            <label for="vehicleId">Vehiculo:<span class="text-danger"> *</span></label>
            <select name="vehicleId" class="form-control mb-2">
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" {{$vehicle->id == $driver->vehicleId ?'selected':'' }}>{{ $vehicle->name }}</option>
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

<script>
  document.getElementById("type").addEventListener("change", function() {
      var type = this.value;
      if (type === "TERCERO") {
          // Mostrar campo de porcentaje y ocultar monto fijo
          document.getElementById("porcentaje_div").style.display = "block";
          document.getElementById("porcentaje").setAttribute("required", "required");
      } else {
          // Si no se selecciona ninguna opción, ocultar ambos campos
          document.getElementById("porcentaje_div").style.display = "none";
          document.getElementById("porcentaje").removeAttribute("required");
      }
  });
</script>