<div class="modal fade" id="editType-{{ $carga->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('edittypeofcarga') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $carga->id }}">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title">Actualizar carga (se modificara el precio segun lo guardado en base de datos)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label for="bulto{{ $carga->id }}">Bultos:</label>
                    <input id="bulto{{ $carga->id }}" type="number" name="cantidad_bulto" class="form-control mb-2"
                        value="{{ old('cantidad_bulto', $carga->cantidad_bulto) }}">
                    
                    <label for="pallet_normal{{ $carga->id }}">Pallet normales:</label>
                    <input id="pallet_normal{{ $carga->id }}" type="number" name="cantidad_pallet_normal" class="form-control mb-2"
                        value="{{ old('cantidad_pallet_normal', $carga->cantidad_pallet_normal) }}">
                    
                    <label for="pallet_grande{{ $carga->id }}">Pallet grandes:</label>
                    <input id="pallet_grande{{ $carga->id }}" type="number" name="cantidad_pallet_grande" class="form-control mb-2"
                        value="{{ old('cantidad_pallet_grande', $carga->cantidad_pallet_grande) }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>