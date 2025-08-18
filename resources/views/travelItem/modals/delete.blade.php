<div class="modal fade" id="deleteItemModal{{ $travelItem->id }}" tabindex="-1" aria-labelledby="deleteItemModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Eliminar Item de Viaje</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('deleteTravelItem', [$travelItem->id, $travelCertificate->id]) }}" class="form-group" method="POST">
            @csrf
            @method('DELETE')
            <p>Se eliminara el item de viaje seleccionado.<br>
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