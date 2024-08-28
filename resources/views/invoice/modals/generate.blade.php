<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Generar Factura</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('generateInvoice') }}" class="form-group" method="POST">
            @csrf
            <label for="number">Numero:</label>
            <input type="number" name="number" class="form-control mb-2" required>
            <label for="date">Fecha:</label>
            <input type="date" name="date" class="form-control mb-2" required>
            <label for="clientId">Cliente:</label>
            <select name="clientId" class="form-control mb-2" required>
                <option value="">---- Seleccione una opcion ----</option>
                @foreach($clients as $client)
                  <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Generar</button>
        </form>
      </div>
    </div>
  </div>
</div>