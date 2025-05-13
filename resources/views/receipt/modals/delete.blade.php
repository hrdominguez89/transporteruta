<div class="modal fade" id="deleteTaxModal{{ $tax->id }}" tabindex="-1" aria-labelledby="deleteTaxModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Eliminar Retención</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('removeTaxFromInvoiceReceipt', $tax->id) }}" class="form-group" method="POST">
            @csrf
            @method('DELETE')
            <p>Se eliminara la retención.<br>
            <strong class="text-warning">¡ESTA ACCION ES IRREVERSIBLE!</strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
        </form>
      </div>
    </div>
  </div>
</div>