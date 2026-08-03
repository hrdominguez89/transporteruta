<div class="modal fade" id="remitoModal" tabindex="-1" aria-labelledby="remitoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('storeRemito') }}" method="POST" enctype="multipart/form-data">
        @csrf
         <input type="hidden" name="carga_id" id="carga_id" value="{{ $carga->id }}">
        <div class="modal-header bg-danger">
          <h5 class="modal-title">Cargar Remito</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <label for="numero">N° de Remito*:</label>
          <input type="text" name="numero" id="numero" class="form-control mb-3"
                 placeholder="Ingrese el número de remito..." required>

          <label for="valor_declarado">Valor declarado:</label>
          <input type="text" name="valor_declarado" id="valor_declarado" class="form-control mb-3"
                 placeholder="Ingrese el valor declarado...">    

          <label for="image">Imagen:</label>
          <input type="file" name="image" id="image" class="form-control-file mb-2"
                 accept="image/*">

          <small class="text-muted d-block mb-2">Formatos: JPG, PNG. Máximo 4 MB.</small>
          <img id="preview" class="img-fluid d-none border rounded" alt="Vista previa">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
        </div>

      </form>
    </div>
  </div>
</div>
@section('js')
<script>
document.getElementById('image').addEventListener('change', function (e) {
    const file    = e.target.files[0];
    const preview = document.getElementById('preview');

    if (!file) {
        preview.classList.add('d-none');
        return;
    }

    preview.src = URL.createObjectURL(file);
    preview.classList.remove('d-none');
});
</script>
@endsection