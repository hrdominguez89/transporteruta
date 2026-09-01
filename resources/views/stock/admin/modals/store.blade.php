<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Agregar mercaderia</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('generatestock') }}" class="form-group" method="POST">
                    @csrf
                    <label for="client_id">Cliente:</label>
                    <select name="client_id"  id="client_id_2"  class="form-control mb-2 @error('clientId') is-invalid @enderror" required>
                        <option value="">---- Seleccione una opcion ----</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ old('clientId') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                        
                    <div id="wrapper_tercero_2" style="display: none">
                        <label for="cliente_tercero_id">Cliente tercero:</label>
                        <select id="cliente_tercero_id_2" name="cliente_tercero_id" class="form-control mb-2">
                            <option value="">---- Sin tercero ----</option>
                            @foreach ($clientes_terceros as $tercero)
                                <option value="{{ $tercero->id }}" data-client="{{ $tercero->client->id }}">
                                    {{ $tercero->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                                
                    <label for="destino">Destino:</label>
                    <input id="destino" type="text" name="destino" class="form-control mb-2"
                    placeholder="Ingrese el destino..." >
                    <label for="fecha_de_recepcion">Fecha de recepcion:</label>
                    <input id="fecha_de_recepcion" type="date" name="fecha_de_recepcion" class="form-control mb-2"
                    placeholder="Ingrese la ciudad..." >
                    <label for="nombre">Mercaderia:<span class="text-danger"> *</span></label>
                    <input id="nombre" type="text" name="nombre" class="form-control mb-2"
                    placeholder="Ingrese el nombre..." required>
                    <label for="cantidad_bulto">Bultos cantidad:</label>
                    <input id="cantidad_bulto" type="number" name="cantidad_bulto" class="form-control mb-2"
                    placeholder="Ingrese la cantidad de bultos..." >
                    
                    <label for="cantidad_pallet_normal">Pallet normales:</label>
                    <input id="cantidad_pallet_normal" type="number" name="cantidad_pallet_normal" class="form-control mb-2"
                    placeholder="Ingrese la cantidad de pallet..." >
                    
                    <label for="cantidad_pallet_grande">Pallet grandes:</label>
                    <input id="cantidad_pallet_grande" type="number" name="cantidad_pallet_grande" class="form-control mb-2"
                    placeholder="Ingrese la cantidad de pallet..." >
                    
                    <label for="driver">Chofer:<span class="text-danger"> *</span></label>
                    <select id="driver" name="driver_id" class="form-control">
                        <option>Seleccione una opcion</option>
                        @foreach ($drivers as $d )
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                        
                    </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
window.addEventListener('load', function () {
    const $client   = $('#client_id_2');
    const $tercero  = $('#cliente_tercero_id_2');
    const $wrapper  = $('#wrapper_tercero_2');
    const htmlOrig  = $tercero.html(); 

    $client.select2({ allowClear: true, width: '100%' });
    $tercero.select2({ allowClear: true, width: '100%' });

    function filtrar(clienteId) {
        if (!clienteId) {
            $wrapper.hide();
            return;
        }

        const $temp = $('<select>').html(htmlOrig);
        let nuevo = '<option value="">---- Sin tercero ----</option>';

        $temp.find('option[data-client]').each(function () {
            if ($(this).data('client') == clienteId) {
                nuevo += '<option value="' + $(this).val() + '">' + $(this).text().trim() + '</option>';
            }
        });

        $tercero.html(nuevo).trigger('change');
        $wrapper.show();
    }

    $client.on('change', function () {
        filtrar($(this).val());
    });

    filtrar($client.val());
});
</script>