<div class="modal fade" id="invoicedModal{{ $invoice->id }}" tabindex="-1" aria-labelledby="invoicedModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">¿Desea confirmar la factura?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('invoicedInvoice', $invoice->id) }}" class="form-group">
            <p>Se facturara al cliente: <strong>{{ $invoice->client->name }}</strong><p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Confirmar</button>
        </form>
      </div>
    </div>
  </div>
</div>