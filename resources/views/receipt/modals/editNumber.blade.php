<div class="modal fade" id="editNumberModal{{ $receipt->id }}" tabindex="-1" aria-labelledby="editNumberModalLabel{{ $receipt->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title" id="editNumberModalLabel{{ $receipt->id }}">Editar numero de recibo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ Route('updateReceiptNumber', $receipt->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <label for="edit_receipt_number">Numero:</label>
          <input type="number" id="edit_receipt_number" name="number" class="form-control mb-2" min="1" max="2147483647" step="1" value="{{ old('number', $receipt->number) }}" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
