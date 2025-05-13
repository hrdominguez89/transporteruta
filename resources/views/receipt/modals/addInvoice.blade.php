<div class="modal fade" id="addInvoiceModal{{ $invoice->id }}{{ $receipt->id }}" tabindex="-1"
    aria-labelledby="storeModalLabel" aria-hidden="true">
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
                    <p>Saldo Restante: <strong>$&nbsp;{{ number_format($invoice->balance, 2, ',', '.') }}</strong></p>
                    <input type="hidden" name="receiptId" value="{{ $receipt->id }}">
                    <label for="balanceToPay_invoice_{{ $invoice->id }}">Saldo a Pagar:<span class="saldo_restante" id="saldo_restante_invoice_{{ $invoice->id }}" data-id="saldo_restante_invoice_{{ $invoice->id }}"></span></label>
                    <input type="number" step="0.01" id="balanceToPay_invoice_{{ $invoice->id }}" name="balanceToPay" data-id="balanceToPay_invoice_{{ $invoice->id }}" class="form-control mb-2 balanceToPay"
                        required>
                    <label for="paymentMethodId">Medio de Pago:</label>
                    <select name="paymentMethodId" class="form-control mb-2" required>
                        <option value="">---- Seleccione una opcion ----</option>
                        @foreach ($paymentMethods as $paymentMethod)
                            <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</option>
                        @endforeach
                    </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-success">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

