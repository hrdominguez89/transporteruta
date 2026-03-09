<div class="modal fade" id="modalQuitPayment"> 
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ Route('quitPaymentToReceipt', $receipt->id) }}" class="form-group" method="POST">
                @csrf
                <div class="modal-header bg-danger">
                    <h5> ¿Desea quitar este pago del recibo?</h5>
                </div>
                <input type="hidden" name="payment_id" value="{{ $pago->id }}">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-danger">Quitar</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>