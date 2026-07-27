<div class="modal fade" id="buscarcortedeoperaciones" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('cortedeoperaciones') }}" method="POST">
        @csrf

        <div class="modal-header bg-danger">
          <h5 class="modal-title">Corte de operaciones</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <label for="corte_client_id">Cliente:</label>
          <select id="corte_client_id" name="client_id" class="form-control mb-2" required>
            <option value="">Seleccione un cliente...</option>
            @foreach ($clients as $client)
              <option value="{{ $client->id }}"
                {{ old('client_id') == $client->id ? 'selected' : '' }}>
                {{ $client->name }}
              </option>
            @endforeach
          </select>

          <label for="corte_fecha">Fecha de corte:</label>
          <input id="corte_fecha" type="date" name="fecha" class="form-control mb-2"
                 value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-sm btn-primary">Consultar</button>
        </div>
      </form>
    </div>
  </div>
</div>