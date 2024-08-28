<div class="modal fade" id="cancelModal{{ $travelCertificate->id }}" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">¿Desea anular el pago al chofer?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('cancelPay', $travelCertificate->id) }}" class="form-group">
            <p>Se anulara el pago a <strong>{{ $travelCertificate->driver->name }}</strong> por un total de <strong>{{ $travelCertificate->driverPayment }}</strong><br>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-warning">Anular</button>
        </form>
      </div>
    </div>
  </div>
</div>