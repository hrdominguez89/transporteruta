<div class="modal fade" id="addTax{{ $ri->id }}" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Agregar Retención</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('addTaxToReceiptInvoice', $ri->id) }}" class="form-group" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12 text-left">

                            <label for="taxId">Impuesto:</label>
                            <select name="taxId" class="form-control mb-2" required>
                                <option value="">---- Seleccione una opción ----</option>
                                @foreach ($taxes as $tax)
                                    <option value="{{ $tax->id }}">{{ $tax->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-left">
                            <label for="taxAmount">Monto de Impuesto: <span class="saldo_restante"
                                    id="saldo_restante_invoice_{{ $ri->id }}"
                                    data-id="saldo_restante_invoice_{{ $ri->id }}"></span></label>
                            <input type="number" id="balanceToPay_invoice_{{ $ri->id }}"
                                data-id="balanceToPay_invoice_{{ $ri->id }}" step="0.01" name="taxAmount"
                                class="form-control mb-2 balanceToPay" required>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-success">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>
