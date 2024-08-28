<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Item de Viaje</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('storeTravelItem', $travelCertificate->id) }}" class="form-group" method="POST">
            @csrf
            <label for="type">Tipo:</label>
            <select name="type" class="form-control mb-2" required>
                <option value="">---- Seleccione una opcion ----</option>
                <option value="HORA">Por Hora</option>
                <option value="KILOMETRO">Por Kilometro</option>
                <option value="PEAJE">Peaje</option>
                <option value="FIJO">Tarifa Fija</option>
                <option value="MULTIDESTINO">Multidestino</option>
                <option value="DESCARGA">Descarga</option>
                <option value="ADICIONAL">Adicional</option>
            </select>
            <label for="description">Descripcion:</label>
            <input type="text" class="form-control mb-2">
            <label for="totalTime">Tiempo Total: <strong class="text-danger">Solo si es por hora</strong></label>
            <input type="text" name="totalTime" class="form-control mb-2" placeholder="Ingrese el tiempo total...">
            <label for="distance">Distancia: <strong class="text-danger">Solo si es por kilometro</strong></label>
            <input type="text" name="distance" class="form-control mb-2" placeholder="Ingrese la distancia...">
            <label for="price">Precio (En caso de ser por HORA o KILOMETRO, agregar el valor unitario):</label>
            <input type="decimal" name="price" class="form-control mb-2" placeholder="Ingrese el precio..." required>                        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>