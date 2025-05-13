<div class="modal fade" id="updateModal{{ $user->id }}" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success">
        <h5 class="modal-title">Editar Usuario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('updateUser', $user->id) }}" class="form-group" method="POST">
            @csrf
            @method('PUT')
            <label for="name">Nombre:</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." value="{{ $user->name }}" required>
            <label for="email">Email:</label>
            <input type="text" name="email" class="form-control mb-2" placeholder="Ingrese el E-Mail..." value="{{ $user->email }}" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-success">Actualizar</button>
        </form>
      </div>
    </div>
  </div>
</div>