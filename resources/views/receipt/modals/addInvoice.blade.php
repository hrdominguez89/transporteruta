<div class="modal fade" id="addInvoiceModal{{ $invoice->id }}{{ $receipt->id }}" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Factura</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('addToReceipt', $invoice->id) }}" class="form-group" method="POST">
            @csrf
            <p>Saldo Restante: <strong>{{ $invoice->balance }}</strong></p>
            <input type="hidden" name="receiptId" value="{{ $receipt->id }}">
            <label for="balanceToPay">Saldo a Pagar:</label>
            <input type="number" step="0.01" name="balanceToPay" class="form-control mb-2" required>
            <label for="paymentMethodId">Medio de Pago:</label>
            <select name="paymentMethodId" class="form-control mb-2" required>
                <option value="">---- Seleccione una opcion ----</option>
                @foreach($paymentMethods as $paymentMethod)
                   <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</option>
                @endforeach
            </select>
            <label for="taxId">Impuesto:</label>
            <select name="taxId" class="form-control mb-2" required>
                <option value="">---- Seleccione una opcion ----</option>
                @foreach($taxes as $tax)
                   <option value="{{ $tax->id }}">{{ $tax->name }}</option>
                @endforeach
            </select>
            <label for="taxAmount">Monto de Impuesto:</label>
            <input type="number" step="0.01" name="taxAmount" class="form-control mb-2" required>

    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-success">Agregar</button>
        </form>
      </div>
    </div>
  </div>
</div>