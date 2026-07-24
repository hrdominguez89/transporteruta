<div class="modal fade" id="pricemodal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Agregar mercaderia</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('updateprice') }}" class="form-group" method="POST">
                    @csrf
                    
                    <label for="type">Tipo:</label>
                    <select id="type" name="type" class="form-control mb-2" required>
                        <option value="">---- Seleccione una opcion ----</option>
                        <option value="PALLET">Pallet</option>
                        <option value="BULTO">Bulto</option>
                    </select>
                    <label for="precio">Precio:</label>
                    <input id="precio" type="number" name="price" class="form-control mb-2"
                        placeholder="Ingrese el telefono..." required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
