<div class="modal fade" id="addPayment{{ $pago->id  }}">
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title " >
                    Agregar pago
                </h5>
            </div>
            <form action="{{ Route('addPaymentToReceipt', $receipt->id) }}" method="POST">
                @csrf
                <input type="hidden" name="payment_id" value="{{ $pago->id }}">
                <div class="modal-body">
                    <label for="monto{{ $pago->id }}">Monto</label>
                    <input name="monto" id="monto{{ $pago->id }}" type="number" step="0.01" required class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-success">Agregar</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>