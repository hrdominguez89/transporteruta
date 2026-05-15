<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Generar liquidacion</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('generateSettlement') }}" class="form-group" method="POST">
                    @csrf
                    <label for="clientId">Chofer:</label>
                    <select name="clientId" class="form-control mb-2 @error('clientId') is-invalid @enderror" required>
                        <option value="">---- Seleccione una opcion ----</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ old('clientId') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                    <label>Periodo</label>
                    <input type="month" name="periodo" value="{{ old('periodo') }}" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary">Generar</button>
                </form>
            </div>
        </div>  
    </div>
</div>
<script>
  
</script>