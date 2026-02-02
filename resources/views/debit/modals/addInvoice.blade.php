<div class="modal fade" id="addInvoiceModal{{ $debit->id }}" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Factura</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('addInvoiceToDebit', $debit->id) }}" class="form-group" method="POST">
            @csrf
            {{-- <label for="total">Saldo a Sumar:</label>
            <input type="number" name="total" class="form-control mb-2" required step="any"> --}}
            <label for="invoiceId">Facturas:</label>
            <select name="invoiceId" class="form-control mb-2" required>
                <option value="">---- Seleccione una opcion ----</option>
                @foreach($debit->client->invoices as $invoice)
                  <option value="{{ $invoice->id }}">{{ $invoice->number }}</option>
                @endforeach
            </select>
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-primary">Generar</button>
        </form>
      </div>
    </div>
  </div>
</div>