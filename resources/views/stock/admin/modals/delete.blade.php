 <div class="modal fade" id="deletecarga{{ $carga?->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title">Eliminar carga</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                </div>
                <form action="{{ Route('deletestock',  $carga->id) }}"  method="POST">
                <div class="modal-body">
                    @csrf
                    @method('DELETE')
                    <p>Se eliminara la carga seleccionada.<br>
                    <strong class="text-danger">¡ESTA ACCION ES IRREVERSIBLE!</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                </div>
                </form>
            </div>
        </div>
    </div>