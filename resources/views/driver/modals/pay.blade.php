<div class="modal fade" id="payModal{{ $travelCertificate->id }}" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">¿Desea registrar el pago al chofer?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('payDriver', $travelCertificate->id) }}" class="form-group">
            <p>Se registrara el pago de <strong>{{ $travelCertificate->driver->name }}</strong> por un total de <strong>{{ $travelCertificate->driverPayment }}</strong><br>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-primary">Confirmar</button>
        </form>
      </div>
    </div>
  </div>
</div>