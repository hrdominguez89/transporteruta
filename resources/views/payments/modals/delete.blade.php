<div class="modal fade" id="deleteModal{{ $payment->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ ROUTE('deletePayment') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger">
                    <h5 >¿Desea eliminar este pago?</h5>
                </div>
                <div>
                    <input type="hidden" name="payment" value="{{ $payment->id }}">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Aceptar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>