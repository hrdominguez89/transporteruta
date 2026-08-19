<div class="modal fade" id="editClienteTercero" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Editar Cliente 3ro</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       <form action="{{ route('updateClientTercero') }}" method="POST">
        @csrf
        <input type="hidden" name="id" id="edit_tercero_id">

        <label for="edit_client_id">Cliente:</label>
        <select id="edit_client_id" class="form-control mb-2" required>
          <option value="">Seleccione un cliente...</option>
          @foreach ($clients as $client)
            <option value="{{ $client->id }}">{{ $client->name }}</option>
          @endforeach
        </select>

        <label for="edit_tercero_select">Cliente 3ro:</label>
        <select id="edit_tercero_select" class="form-control mb-2" required>
          <option value="">Seleccione un cliente 3ro...</option>
          @foreach ($clientes_terceros as $tercero)
            <option value="{{ $tercero->id }}"
              data-client="{{ $tercero->client_id }}"
              data-nombre="{{ $tercero->nombre }}"
              data-numero_cliente="{{ $tercero->numero_cliente }}"
              data-cuit="{{ $tercero->cuit }}"
              data-condicion_venta="{{ $tercero->condicion_venta }}"
              data-codigo_postal="{{ $tercero->codigo_postal }}"
              data-direccion="{{ $tercero->direccion }}"
              data-horario_entrega="{{ $tercero->horario_entrega }}">
              {{ $tercero->nombre }}
            </option>
          @endforeach
        </select>

        <hr>

        <label for="edit_nombre">Nombre (de 3ro):</label>
        <input type="text" name="nombre" id="edit_nombre" class="form-control mb-2" required>

        <label for="edit_numero_cliente">N° de cliente:</label>
        <input type="text" name="numero_cliente" id="edit_numero_cliente" class="form-control mb-2">

        <label for="edit_cuit">CUIT:</label>
        <input type="text" name="cuit" id="edit_cuit" class="form-control mb-2">

        <label for="edit_condicion_venta">Condición de venta:</label>
        <input type="text" name="condicion_venta" id="edit_condicion_venta" class="form-control mb-2">

        <label for="edit_codigo_postal">Código postal:</label>
        <input type="text" name="codigo_postal" id="edit_codigo_postal" class="form-control mb-2">

        <label for="edit_direccion">Domicilio de entrega:</label>
        <input type="text" name="direccion" id="edit_direccion" class="form-control mb-2">

        <label for="edit_horario_entrega">Horario de entrega:</label>
        <input type="text" name="horario_entrega" id="edit_horario_entrega" class="form-control mb-2">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', function () {
    var $cliente = $('#edit_client_id');
    var $tercero = $('#edit_tercero_select');
    var htmlOriginal = $tercero.html();   // guarda todas las options con sus data-*

    function filtrar(clientId) {
        var $temp = $('<select>').html(htmlOriginal);
        var $nuevo = $('<option value="">Seleccione un cliente 3ro...</option>');

        $temp.find('option[data-client]').each(function () {
            if ($(this).attr('data-client') === clientId) {
                $nuevo = $nuevo.add($(this).clone());   // clone conserva los data-*
            }
        });

        $tercero.html($nuevo);
        limpiarCampos();
    }

    $cliente.on('change', function () {
        filtrar($(this).val());
    });

    $tercero.on('change', function () {
        var o = $(this).find('option:selected');
        if (!o.val()) { limpiarCampos(); return; }
        $('#edit_tercero_id').val(o.val());
        $('#edit_nombre').val(o.data('nombre'));
        $('#edit_numero_cliente').val(o.data('numero_cliente'));
        $('#edit_cuit').val(o.data('cuit'));
        $('#edit_condicion_venta').val(o.data('condicion_venta'));
        $('#edit_codigo_postal').val(o.data('codigo_postal'));
        $('#edit_direccion').val(o.data('direccion'));
        $('#edit_horario_entrega').val(o.data('horario_entrega'));
    });

    function limpiarCampos() {
        $('#edit_tercero_id, #edit_nombre, #edit_numero_cliente, #edit_cuit, #edit_condicion_venta, #edit_codigo_postal, #edit_direccion, #edit_horario_entrega').val('');
    }
});
</script>