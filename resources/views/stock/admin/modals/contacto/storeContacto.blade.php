@if ($carga->cliente_tercero)
<div class="modal fade" id="storeContacto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('crearContactoTercero', $carga->cliente_tercero?->id) }}" method="POST">
        @csrf
        <div class="modal-header bg-danger">
          <h5 class="modal-title">Agregar contacto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <label>Nombre:</label>
          <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." required>
          <label>Apellido:</label>
          <input type="text" name="lastname" class="form-control mb-2" placeholder="Ingrese el apellido...">
          <label>Departamento:</label>
          <select name="category" class="form-control mb-2">
            <option value="-">Seleccione un departamento</option>
            <option value="Cobros y Pagos">Depto. Cobros y Pagos</option>
            <option value="administracion">Administracion</option>
            <option value="proveedores">Proveedores</option>
            <option value="oficina">Oficina</option>
            <option value="contable">Area contable</option>
            <option value="compras">Compras</option>
            <option value="ventas">Ventas</option>
          </select>
          <label>Mail:</label>
          <input type="text" name="mail" class="form-control mb-2" placeholder="Ingrese un mail...">
          <label>Telefono:</label>
          <input type="text" name="telefono" class="form-control mb-2" placeholder="Ingrese el telefono..." required>
          <label>Observaciones</label>
          <input type="text" name="comentarios" class="form-control mb-2" placeholder="Ingrese las observaciones...">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif