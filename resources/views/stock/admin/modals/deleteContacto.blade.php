@if ( $carga->cliente_tercero?->contacto)
<div class="modal fade" id="deleteContacto-{{ $carga->cliente_tercero->contacto->id }}-{{ $carga->cliente_tercero->id }}" tabindex="-1" aria-labelledby="deleteContactoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Eliminar Contacto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('eliminarContactoTercero', [$carga->cliente_tercero->contacto->id, $carga->cliente_tercero->id]) }}" class="form-group" method="POST">
            @csrf
            @method('DELETE')
            <p>Se eliminara el contacto seleccionado.<br>
            <strong class="text-danger">¡ESTA ACCION ES IRREVERSIBLE!</strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endif
