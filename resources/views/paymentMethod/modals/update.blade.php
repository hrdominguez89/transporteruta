<div class="modal fade" id="updateModal{{ $paymentMethod->id }}" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Editar Medio de Pago</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('updatePaymentMethod', $paymentMethod->id) }}" class="form-group" method="POST">
            @csrf
            @method('PUT')
            <label for="name">Nombre:</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." value="{{ $paymentMethod->name }}" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-success">Actualizar</button>
        </form>
      </div>
    </div>
  </div>
</div>