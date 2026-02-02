<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Generar Nota de Debito</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('generateDebit') }}" class="form-group" method="POST">
            @csrf
            <label for="referenceNumber">Numero:</label>
            <input type="number" name="referenceNumber" class="form-control mb-2" required>
            <label for="emissionDate">Fecha:</label>
            <input type="date" name="emissionDate" class="form-control mb-2" required>
            <label for="client">Cliente:</label>
            <select name="client" class="form-control mb-2" required>
                <option value="">---- Seleccione una opcion ----</option>
                @foreach($clients as $client)
                  <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
            <label for="balance">Monto</label>
            <input name="balance" class="form-control mb-2">
            <label for="reason">Motivo</label>
            <input name="reason" class="form-control mb-2">
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-primary">Generar</button>
        </form>
      </div>
    </div>
  </div>
</div>