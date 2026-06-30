<div class="modal fade" id="updateContacto-{{ $contacto->id }}-{{ $client->id }}" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Editar Contacto</h5>
          <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('editarContacto', [$contacto->id, $client->id]) }}" method="POST">
            @csrf
            <label for="name">Nombre:</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." value="{{ $contacto->nombre ?? "" }}" >
            <label for="lastname">Apellido:</label>
            <input type="text" name="lastname" class="form-control mb-2" placeholder="Ingrese el apellido..." value="{{ $contacto->apellido ?? "" }}" >
            <label for="address">Departamento:</label>
            <select name="category" class="form-control mb-2">
              <option value="{{ $contacto->categoria ?? "" }}" selected>{{ $contacto->categoria ?? "Seleccione un departamento" }}</option>
              <option value="Cobros y Pagos">Depto. Cobros y Pagos</option>
              <option value="administracion">Administracion</option>
              <option value="proveedores">Proveedores</option>
              <option value="oficina">Oficina</option>
              <option value="contable">Area contable</option>
              <option value="compras">Compras</option>
              <option value="ventas">Ventas</option>
            </select>
            <label for="mail">Mail:</label>
            <input type="text" name="mail" class="form-control mb-2" placeholder="Ingrese un mail..." value="{{ $contacto->mail }}" >
            <label for="phone">Telefono:</label>
            <input type="text" name="telefono" class="form-control mb-2" placeholder="Ingrese el telefono..." value="{{ $contacto->telefono }}">
            <label for="comentarios">Observaciones </label>
            <input type="text" name="comentarios" class="form-control mb-2" placeholder="Ingrese las observaciones..." value="{{ $contacto->comentario }}">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>