<div class="modal fade" id="paidModal{{ $receipt->id }}" tabindex="-1" aria-labelledby="invoicedModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">¿Desea marcar como pagado el recibo?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('paidReceipt', $receipt->id) }}" class="form-group">
            <p>Se actualizara la cuenta corriente del cliente: <strong>{{ $receipt->client->name }}</strong><p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-primary">Confirmar</button>
        </form>
      </div>
    </div>
  </div>
</div>