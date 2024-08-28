<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('storeClient') }}" class="form-group" method="POST">
            @csrf
            <label for="name">Nombre:</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." required>
            <label for="dni">DNI/CUIT:</label>
            <input type="text" name="dni" class="form-control mb-2" placeholder="Ingrese el DNI/CUIT..." required>
            <label for="address">Direccion:</label>
            <input type="text" name="address" class="form-control mb-2" placeholder="Ingrese la direccion..." required>
            <label for="city">Ciudad:</label>
            <input type="text" name="city" class="form-control mb-2" placeholder="Ingrese la ciudad..." required>
            <label for="phpne">Telefono:</label>
            <input type="text" name="phone" class="form-control mb-2" placeholder="Ingrese el telefono..." required>
            <label for="ivaType">IVA Tipo:</label>
            <select name="ivaType" class="form-control mb-2" required>
                <option value="">---- Seleccione una opcion ----</option>
                <option value="CONSUMIDOR FINAL">Consumidor Final</option>
                <option value="RESPONSABLE INSCRIPTO">Responsable Inscripto</option>
                <option value="EXENTO">Exento</option>
            </select>
            <label for="observations">Observaciones (Opcional):</label>
            <input type="text" name="observations" class="form-control mb-2" placeholder="Ingrese las observaciones...">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>