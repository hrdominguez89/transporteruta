<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Generar Factura</h5>
                <!-- Keep both BS4 and BS5 attributes for compatibility -->
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('generateInvoice') }}" class="form-group" method="POST">
                    @csrf
                    <label for="pointOfSale">Punto de Venta:</label>
                    <input type="number" name="pointOfSale" class="form-control mb-2 @error('pointOfSale') is-invalid @enderror" value="{{ old('pointOfSale', 3) }}" min="1"
                        required>
                    @error('pointOfSale')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <label for="number">Numero:</label>
                    <input type="number" name="number" class="form-control mb-2 @error('number') is-invalid @enderror" value="{{ old('number') }}" required>
                    @error('number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    
                    <label for="date">Fecha:</label>
                    <input type="date" name="date" class="form-control mb-2 @error('date') is-invalid @enderror" value="{{ old('date') }}" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    
                    <label for="clientId">Cliente:</label>
                    <select name="clientId" class="form-control mb-2 @error('clientId') is-invalid @enderror" required>
                        <option value="">---- Seleccione una opcion ----</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ old('clientId') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>

                    <label>Referencia:</label>
                    <input type="text" name="reference"class="form-control mb-2">

                    @error('clientId')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
            </div>
            <div class="modal-footer">
                <!-- Keep both BS4 and BS5 attributes for compatibility -->
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary">Generar</button>
                </form>
            </div>
        </div>
    </div>
</div>
