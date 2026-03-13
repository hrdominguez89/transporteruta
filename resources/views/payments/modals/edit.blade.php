<div class="modal fade" id="updateModal{{ $pago->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ Route('editPayment', $pago->id) }}" method="POST">

                @csrf
                <div class="modal-header bg-danger">
                    <h5>Editar pago</h5>
                </div>
                <div class="modal-body">
                    
                <div class="modal-footer">
                    <button type="submit"  class="btn btn-sm btn-primary">Aceptar</button>
                    <button type="button" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>