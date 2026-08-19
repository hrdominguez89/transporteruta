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
                    <label for="precio_bulto{{ $carga->id }}">Precio por bulto:</label>
                    <input id="precio_bulto{{ $carga->id }}" type="number" step="0.01" name="precio_bulto" class="form-control mb-2"
                    value="{{ old('precio_bulto', $carga->bulto_costo) }}">

                    <label for="precio_pallet{{ $carga->id }}">Precio por pallet:</label>
                    <input id="precio_pallet{{ $carga->id }}" type="number" step="0.01" name="precio_pallet" class="form-control mb-2"
                    value="{{ old('precio_pallet', $carga->pallet_costo) }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>