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
                    <select name="client_id" class="form-control mb-2 @error('clientId') is-invalid @enderror" required>
                        <option value="">---- Seleccione una opcion ----</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ old('clientId') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                    <label for="cliente_tercero_id">Cliente tercero:</label>
                    <select id="cliente_tercero_id" name="cliente_tercero_id" class="form-control mb-2">
                        <option value="">---- Sin tercero ----</option>
                        @foreach ($clientes_terceros as $tercero)
                            <option value="{{ $tercero->id }}" data-client="{{ $tercero->client->id }}">
                                {{ $tercero->nombre }}
                            </option>
                        @endforeach
                    </select>
               
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
                    
                    {{-- <label for="cantidad">Cantidad:</label>
                    <input id="cantidad" type="number" name="cantidad" class="form-control mb-2"
                        placeholder="Ingrese la cantidad..." > --}}
                        {{-- <label for="fecha_de_entrega">Fecha de entrega:</label>
                        <input id="fecha_de_entrega" type="date" name="fecha_de_entrega" class="form-control mb-2"
                            placeholder="Ingrese el telefono..." > --}}
                    {{-- <label for="tipo">Tipo:</label> --}}
                    {{-- <select id="tipo" name="tipo" class="form-control mb-2" required>
                        <option value="">---- Seleccione una opcion ----</option>
                        <option value="PALLET">Pallet</option>
                        <option value="BULTO">Bulto</option>
                    </select>
                    <div id="tamaño_pallet_div" style="display: none">
                    </div> --}}
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
  // --- toggle tamaño pallet (lo que ya tenías) ---
    // document.getElementById("tipo").addEventListener("change", function () {
    //     var type = this.value;
    //     if (type === "PALLET") {
    //         document.getElementById("tamaño_pallet_div").style.display = "block";
    //         document.getElementById("tamaño_pallet").setAttribute("required", "required");
    //     } else {
    //         document.getElementById("tamaño_pallet_div").style.display = "none";
    //         document.getElementById("tamaño_pallet").removeAttribute("required");
    //     }
    // });
    window.addEventListener('load', function () {
    var tercero = $('#cliente_tercero_id');
    var htmlOriginal = tercero.html();

    function filtrar(clientId) {
        var temp = $('<select>').html(htmlOriginal);
        var nuevo = '<option value="">---- Sin tercero ----</option>';

        temp.find('option').each(function () {
            var op = $(this);
            if (op.val() === '') return;
            if (op.attr('data-client') === clientId) {
                nuevo += '<option value="' + op.val() + '">' + op.text() + '</option>';
            }
        });

        tercero.html(nuevo);
    }

    $('select[name="client_id"]').on('change', function () {
        filtrar($(this).val());
    });

    filtrar($('select[name="client_id"]').val()); // filtrado inicial
});
</script>