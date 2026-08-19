<div class="modal fade" id="editRemitoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('updateRemito') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{ $carga->remito?->id }}">
        <div class="modal-header bg-danger">
          <h5 class="modal-title">Editar Remito</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <label for="edit_numero">N° de Remito:</label>
          <input type="text" name="numero" id="edit_numero" class="form-control mb-3"
                 value="{{ $carga->remito?->numero }}"
                 placeholder="Ingrese el número de remito..." required>

          <label for="edit_valor_declarado">Valor declarado:</label>
          <input type="text" name="valor_declarado" id="edit_valor_declarado" class="form-control mb-3"
                 value="{{ $carga->remito?->valor_declarado }}"
                 placeholder="Ingrese el valor declarado...">

          <label for="edit_image">Imagen (dejar vacío para mantener la actual):</label>
          <input type="file" name="image" id="edit_image" class="form-control-file mb-2" accept="image/*">
          <small class="text-muted d-block mb-2">Formatos: JPG, PNG. Máximo 4 MB.</small>

          @if ($carga->remito?->path)
            <p class="mb-1 text-muted">Imagen actual:</p>
            <img src="{{ asset('storage/' . $carga->remito->path) }}"
                 class="img-fluid border rounded mb-2" style="max-height:150px" alt="Remito actual">
          @endif

          <img id="edit_preview" class="img-fluid d-none border rounded" alt="Vista previa">
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
document.getElementById('edit_image').addEventListener('change', function (e) {
    const file    = e.target.files[0];
    const preview = document.getElementById('edit_preview');
    if (!file) { preview.classList.add('d-none'); return; }
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('d-none');
});
</script>