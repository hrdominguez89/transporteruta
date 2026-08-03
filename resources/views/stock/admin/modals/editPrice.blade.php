<div class="modal fade" id="editPrice-{{ $carga->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('editpriceoncarga') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $carga->id }}">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title">Actualizar precio</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label>Ingrese el precio de la carga</label>
                    <input type="number" name="precio" class="form-control mb-2">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>