<div class="modal fade" id="updateModal{{ $client->id }}" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Editar Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('updateClient', $client->id) }}" class="form-group" method="POST">
            @csrf
            @method('PUT')
            <label for="name">Nombre:</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." value="{{ $client->name }}" required>
            <label for="dni">DNI/CUIT:</label>
            <input type="text" name="dni" class="form-control mb-2" placeholder="Ingrese el DNI/CUIT..." value="{{ $client->dni }}" required>
            <label for="address">Direccion:</label>
            <input type="text" name="address" class="form-control mb-2" placeholder="Ingrese la direccion..." value="{{ $client->address }}" required>
            <label for="city">Ciudad:</label>
            <input type="text" name="city" class="form-control mb-2" placeholder="Ingrese la ciudad..." value="{{ $client->city }}" required>
            <label for="phpne">Telefono:</label>
            <input type="text" name="phone" class="form-control mb-2" placeholder="Ingrese el telefono..." value="{{ $client->phone }}" required>
            <label for="ivaType">IVA Tipo:</label>
            <select name="ivaType" class="form-control mb-2" required>
                <option  value="{{ $client->ivaType }}">{{ $client->ivaType }}</option>
                <option value="CONSUMIDOR FINAL">Consumidor Final</option>
                <option value="RESPONSABLE INSCRIPTO">Responsable Inscripto</option>
                <option value="EXENTO">Exento</option>
            </select>
            <label for="observations">Observaciones (Opcional):</label>
            <input type="text" name="observations" class="form-control mb-2" placeholder="Ingrese las observaciones..." value="{{ $client->observations }}">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-success">Actualizar</button>
        </form>
      </div>
    </div>
  </div>
</div>