<div class="modal fade" id="editPaymentFromReceipt">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ Route('editPaymentFromReceipt', $receipt->id) }}" method="POST">

                @csrf
                <div class="modal-header bg-danger">
                    <h5>Edicion del monto del pago aplicado al recibo</h5>
                </div>
                <div class="modal-body">
                    <input name="payment_id"type="hidden" value="{{ $pago->id }}">
                    <label for="monto"> Nuevo monto:</label>
                    <input name="monto" class="form-class" type="number" step="0.01">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-succes btn-sm">Aceptar</button>
                    <button type="button" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>