<div class="modal fade" id="storeContacto" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('crearContacto',$client->id) }}" class="form-group" method="POST">
            @csrf
            <label for="name">Nombre:</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." >
            <label for="lastname">Apellido:</label>
            <input type="text" name="lastname" class="form-control mb-2" placeholder="Ingrese el apellido..." >
            <label for="address">Departamento:</label>
            <input type="text" name="category" class="form-control mb-2" placeholder="Ingrese la direccion..." >
            <label for="mail">Mail:</label>
            <input type="text" name="mail" class="form-control mb-2" placeholder="Ingrese la ciudad..." >
            <label for="phone">Telefono:</label>
            <input type="text" name="telefono" class="form-control mb-2" placeholder="Ingrese el telefono..." >
            <label for="comentarios">Observaciones </label>
            <input type="text" name="comentarios" class="form-control mb-2" placeholder="Ingrese las observaciones...">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>