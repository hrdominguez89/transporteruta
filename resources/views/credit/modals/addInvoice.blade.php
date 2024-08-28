<div class="modal fade" id="addInvoiceModal{{ $credit->id }}" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Factura</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('addInvoiceToCredit', $credit->id) }}" class="form-group" method="POST">
            @csrf
            <label for="total">Saldo a Descontar:</label>
            <input type="number" name="total" class="form-control mb-2" required>
            <label for="invoiceId">Facturas:</label>
            <select name="invoiceId" class="form-control mb-2" required>
                <option value="">---- Seleccione una opcion ----</option>
                @foreach($credit->client->invoices as $invoice)
                  <option value="{{ $invoice->id }}">{{ $invoice->number }}</option>
                @endforeach
            </select>
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Generar</button>
        </form>
      </div>
    </div>
  </div>
</div>