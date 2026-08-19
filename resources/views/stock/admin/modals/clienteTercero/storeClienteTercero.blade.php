<div class="modal fade" id="storeClienteTercero" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
     <div class="modal-body">
 <form action="{{ route('storeClientTercero') }}" method="POST">
  @csrf
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

  <label for="nombre">Nombre (de 3ro):</label>
  <input type="text" name="nombre" class="form-control mb-2" placeholder="Ingrese el nombre..." required>

  <label for="numero_cliente">N° de cliente:</label>
  <input type="text" name="numero_cliente" class="form-control mb-2" placeholder="Ingrese el N° de cliente...">

  <label for="cuit">CUIT:</label>
  <input type="text" name="cuit" class="form-control mb-2" placeholder="Ingrese el CUIT...">

  <label for="condicion_venta">Condición de venta:</label>
  <input type="text" name="condicion_venta" class="form-control mb-2" placeholder="Ingrese la condición de venta...">

  <label for="codigo_postal">Código postal:</label>
  <input type="text" name="codigo_postal" class="form-control mb-2" placeholder="Ingrese el código postal...">

  <label for="direccion">Domicilio de entrega:</label>
  <input type="text" name="direccion" class="form-control mb-2" placeholder="Ingrese el domicilio de entrega...">

  <label for="horario_entrega">Horario de entrega:</label>
  <input type="text" name="horario_entrega" class="form-control mb-2" placeholder="Ingrese el horario de entrega...">
  </div>
  <div class="modal-footer">
      <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
      <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
  </form>
</div>
    </div>
  </div>
</div>