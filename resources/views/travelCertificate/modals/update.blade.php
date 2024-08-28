<div class="modal fade" id="updateModal{{ $travelCertificate->id }}" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Actualizar Constancia de Viaje</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('updateTravelCertificate', $travelCertificate->id) }}" class="form-group" method="POST">
            @csrf
            @method('PUT')
            <label for="number">Numero:</label>
            <input type="number" name="number" class="form-control mb-2" value="{{ $travelCertificate->number }}" required>
            <label for="date">Fecha:</label>
            <input type="date" name="date" class="form-control mb-2" value="{{ $travelCertificate->date }}" required>
            <label for="clientId">Cliente:</label>
            <select name="clientId" class="form-control mb-2" required>
                <option value="{{ $travelCertificate->client->id }}">{{ $travelCertificate->client->name }}</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
            <label for="driverId">Chofer:</label>
            <select name="driverId" class="form-control mb-2" required>
                <option value="{{ $travelCertificate->driver->id }}">{{ $travelCertificate->driver->name }}</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                @endforeach
            </select>
            <label for="destiny">Destino:</label>
            <input type="text" name="destiny" class="form-control mb-2" value="{{ $travelCertificate->destiny }}">
            <label for="driverPayment">Pago a Chofer:</label>
            <input type="number" name="driverPayment" class="form-control mb-2" placeholder="Ingrese el monto del pago al chofer.." value="{{ $travelCertificate->driverPayment }}" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-success">Actualizar</button>
        </form>
      </div>
    </div>
  </div>
</div>