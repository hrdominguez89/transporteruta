<div class="modal fade" id="verClienteTercero" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Ver Clientes 3ros</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group mb-0">
          <label for="ver_client_id">Cliente:</label>
          <select id="ver_client_id" class="form-control">
            <option value="">Seleccione un cliente...</option>
            @foreach ($clients as $client)
              <option value="{{ $client->id }}">{{ $client->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-sm btn-primary" id="btnVerClienteTercero" disabled>Aceptar</button>
        {{-- Botón oculto: abre el modal de datos con el data-api de Bootstrap 4 --}}
        <button type="button" class="d-none" id="btnAbrirDatosClienteTercero" data-toggle="modal" data-target="#datosClienteTercero"></button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="datosClienteTercero" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Clientes 3ros de <strong id="datos_cliente_nombre"></strong></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <table class="table table-sm table-bordered text-center mb-0">
          <thead class="bg-danger">
            <tr>
              <th>Nombre</th>
              <th>Dirección</th>
              <th>CUIT</th>
              <th>Horario de entrega</th>
            </tr>
          </thead>
          <tbody id="datos_terceros_body"></tbody>
        </table>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
    var $modal      = $('#verClienteTercero');
    var $cliente    = $('#ver_client_id');
    var $btnAceptar = $('#btnVerClienteTercero');
    var $tbody      = $('#datos_terceros_body');

    @php
        // Se arma antes porque @json corta el argumento en las comas
        $tercerosVer = $clientes_terceros->map->only(['client_id', 'nombre', 'direccion', 'cuit', 'horario_entrega'])->values();
    @endphp
    var terceros = @json($tercerosVer);

    $cliente.select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        placeholder: 'Seleccione un cliente...',
        dropdownParent: $modal
    });

    $cliente.on('change', function () {
        $btnAceptar.prop('disabled', !$(this).val());
    });

    function celda(valor) {
        return $('<td>').text(valor || '-');
    }

    $btnAceptar.on('click', function () {
        var clientId = String($cliente.val() || '');
        if (!clientId) { return; }

        var lista = terceros.filter(function (t) { return String(t.client_id) === clientId; });

        $('#datos_cliente_nombre').text($cliente.find('option:selected').text().trim());
        $tbody.empty();

        if (lista.length === 0) {
            $tbody.append($('<tr>').append($('<td colspan="4">').text('Este cliente no tiene clientes 3ros asociados.')));
        } else {
            lista.forEach(function (t) {
                $tbody.append($('<tr>').append(
                    celda(t.nombre),
                    celda(t.direccion),
                    celda(t.cuit),
                    celda(t.horario_entrega)
                ));
            });
        }

        // Abrir el modal de datos cuando termine de cerrarse el de selección.
        // Se usan los botones data-dismiss/data-toggle (Bootstrap 4) porque el
        // .modal() de jQuery lo pisa el Bootstrap 5 que carga AdminLTE.
        $modal.one('hidden.bs.modal', function () {
            $('#btnAbrirDatosClienteTercero').trigger('click');
        });
        $modal.find('[data-dismiss="modal"]').first().trigger('click');
    });

    // Al cerrar el modal, dejarlo limpio para la próxima apertura
    $modal.on('hidden.bs.modal', function () {
        $cliente.val('').trigger('change');
    });
});
</script>
