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
                    <input id="name" type="text" name="name" class="form-control mb-2"
                        placeholder="Ingrese el nombre..." required>
                    <label for="dni">DNI/CUIT:<span class="text-danger"> *</span></label>
                    <input id="dni" type="text" name="dni" class="form-control mb-2"
                        placeholder="Ingrese el DNI/CUIT..." required>
                    <label for="address">Direccion:<span class="text-danger"> *</span></label>
                    <input id="address" type="text" name="address" class="form-control mb-2"
                        placeholder="Ingrese la direccion..." required>
                    <label for="city">Ciudad:<span class="text-danger"> *</span></label>
                    <input id="city" type="text" name="city" class="form-control mb-2"
                        placeholder="Ingrese la ciudad..." required>
                    <label for="phone">Telefono:<span class="text-danger"> *</span></label>
                    <input id="phone" type="text" name="phone" class="form-control mb-2"
                        placeholder="Ingrese el telefono..." required>
                    <label for="type">Tipo:</label>
                    <select id="type" name="type" class="form-control mb-2" required>
                        <option value="">---- Seleccione una opcion ----</option>
                        <option value="PROPIO">Propio</option>
                        <option value="TERCERO">Tercero</option>
                    </select>

                    <div id="porcentaje_div" style="display: none">
                        <label for="porcentaje">Porcentaje de la Agencia:<span class="text-danger"> *</span></label>
                        <input id="porcentaje" type="number" step="0.01" name="percent" class="form-control mb-2"
                            placeholder="Ingrese el porcentaje de la agencia..." required>
                    </div>

                     {{-- Nueva refactorizacion si no se ingresa ninguna opcion --}}
                    <label for="vehicleId">Vehículo:</label>
                        <select id="vehicleId" name="vehicleId" class="form-control mb-2">
                            <option value="" selected>---- Seleccione una opción ----</option>
                            @forelse ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}"
                                    {{ old('vehicleId') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->plate ?? $vehicle->name }}
                                </option>
                            @empty
                                {{-- No hay vehículos cargados --}}
                            @endforelse
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