<div class="modal fade" id="deleteClienteTercero" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Eliminar cliente tercero</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('clienteTercero.destroy') }}" method="POST"
                  onsubmit="return confirm('¿Seguro que querés eliminar este cliente tercero?');">
                @csrf
                @method('DELETE')

                <div class="modal-body">
                    <label for="del_cliente">Cliente:</label>
                    <select id="del_cliente" class="form-control mb-2">
                        <option value="">---- Seleccione un cliente ----</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>

                    <div id="del_wrapper_tercero" style="display: none;">
                        <label for="del_tercero">Cliente tercero:</label>
                        <select id="del_tercero" class="form-control mb-2">
                            <option value="">---- Seleccione un tercero ----</option>
                            @foreach ($clientes_terceros as $tercero)
                                <option value="{{ $tercero->id }}"
                                        data-client="{{ $tercero->client_id }}"
                                        data-nombre="{{ $tercero->nombre }}">
                                    {{ $tercero->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="del_info_tercero" style="display: none;" class="alert alert-secondary">
                        <p class="mb-0"><strong>Nombre:</strong> <span id="del_nombre_tercero"></span></p>
                    </div>

                    <input type="hidden" name="cliente_tercero_id" id="del_cliente_tercero_id">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" id="del_btn_eliminar" class="btn btn-sm btn-danger" disabled>Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>