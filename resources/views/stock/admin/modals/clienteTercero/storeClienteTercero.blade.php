<div class="modal fade" id="storeClienteTercero" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="{{ route('storeClientTercero') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label for="store_client_id">Cliente:</label>
            <select id="store_client_id" name="client_id" class="form-control" required>
              <option value="">Seleccione un cliente...</option>
              @foreach ($clients as $client)
                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                  {{ $client->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label for="store_nombre">Nombre (de 3ro):</label>
            <input type="text" name="nombre" id="store_nombre" class="form-control" placeholder="Ingrese el nombre..." required>
          </div>

          <div class="form-group">
            <label for="store_numero_cliente">N° de cliente:</label>
            <input type="text" name="numero_cliente" id="store_numero_cliente" class="form-control" placeholder="Ingrese el N° de cliente..." required>
          </div>

          <div class="form-group">
            <label for="store_cuit">CUIT:</label>
            <input type="text" name="cuit" id="store_cuit" class="form-control" placeholder="Ingrese el CUIT...">
          </div>

          <div class="form-group">
            <label for="store_condicion_venta">Condición de venta:</label>
            <input type="text" name="condicion_venta" id="store_condicion_venta" class="form-control" placeholder="Ingrese la condición de venta...">
          </div>

          <div class="form-group">
            <label for="store_codigo_postal">Código postal:</label>
            <input type="text" name="codigo_postal" id="store_codigo_postal" class="form-control" placeholder="Ingrese el código postal...">
          </div>

          <div class="form-group">
            <label for="store_direccion">Domicilio de entrega:</label>
            <input type="text" name="direccion" id="store_direccion" class="form-control" placeholder="Ingrese el domicilio de entrega...">
          </div>

          <div class="form-group mb-0">
            <label for="store_horario_entrega">Horario de entrega:</label>
            <input type="text" name="horario_entrega" id="store_horario_entrega" class="form-control" placeholder="Ingrese el horario de entrega...">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
    $('#store_client_id').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        placeholder: 'Seleccione un cliente...',
        dropdownParent: $('#storeClienteTercero')
    });
});
</script>