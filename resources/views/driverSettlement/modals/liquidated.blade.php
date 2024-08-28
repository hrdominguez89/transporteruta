<div class="modal fade" id="liquidatedModal{{ $driverSettlement->id }}" tabindex="-1" aria-labelledby="liquidatedModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">¿Desea confirmar la liquidacion?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('liquidatedDriverSettlement', $driverSettlement->id) }}" class="form-group">
            <p class="text-danger">Se liquidaran los viajes al chofer: <strong>{{ $driverSettlement->driver->name }}</strong><br>
            <label for="paymentMethodId">Medio de Pago:</label>
            <select name="paymentMethodId" class="form-control mb-2" required>
                <option value="">---- Seleccione una opcion ----</option>
                @foreach($paymentMethods as $paymentMethod)
                   <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</option>
                @endforeach
            </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Confirmar</button>
        </form>
      </div>
    </div>
  </div>
</div>