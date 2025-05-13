<div class="modal fade" id="cancelModal{{ $driverSettlement->id }}" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">¿Desea anular la liquidacion?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('cancelDriverSettlement', $driverSettlement->id) }}" class="form-group">
            <p>Se anulara la liquidacion <strong>{{ $driverSettlement->id }}</strong> del chofer <strong>{{ $driverSettlement->driver->name }}</strong><br>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-warning">Anular</button>
        </form>
      </div>
    </div>
  </div>
</div>