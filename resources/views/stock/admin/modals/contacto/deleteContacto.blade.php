@foreach ($carga->cliente_tercero?->contactos ?? [] as $contacto)
<div class="modal fade" id="deleteContacto-{{ $contacto->id }}-{{ $carga->cliente_tercero->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Eliminar Contacto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('eliminarContactoTercero', [$contacto->id, $carga->cliente_tercero->id]) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
            <p>Se eliminará el contacto <strong>{{ $contacto->nombre }}</strong>.<br>
            <strong class="text-danger">¡ESTA ACCIÓN ES IRREVERSIBLE!</strong></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach