<div class="modal fade" id="updateContacto-{{ $contacto->id }}-{{ $client->id }}" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Editar Contacto</h5>
          <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('editarContacto', [$contacto->id, $client->id]) }}" method="POST">
            @csrf
            <label for="name">Nombre:</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." value="{{ $contacto->nombre ?? "" }}" >
            <label for="lastname">Apellido:</label>
            <input type="text" name="lastname" class="form-control mb-2" placeholder="Ingrese el apellido..." value="{{ $contacto->apellido ?? "" }}" >
           @php $selected = $contacto->categorias->pluck('categoria')->toArray(); @endphp
<label class="d-block">Departamento:</label>
<div class="mb-2">
    @foreach([
        'Cobros y Pagos' => 'Depto. Cobros y Pagos',
        'administracion'  => 'Administracion',
        'proveedores'     => 'Proveedores',
        'oficina'         => 'Oficina',
        'contable'        => 'Area contable',
        'compras'         => 'Compras',
        'ventas'          => 'Ventas',
    ] as $value => $label)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="category[]"
                   value="{{ $value }}"
                   id="cat-{{ $contacto->id }}-{{ Str::slug($value) }}"
                   @checked(in_array($value, $selected))>
            <label class="form-check-label" for="cat-{{ $contacto->id }}-{{ Str::slug($value) }}">{{ $label }}</label>
        </div>
    @endforeach
</div>
            <label for="mail">Mail:</label>
            <input type="text" name="mail" class="form-control mb-2" placeholder="Ingrese un mail..." value="{{ $contacto->mail }}" >
            <label for="phone">Telefono:</label>
            <input type="text" name="telefono" class="form-control mb-2" placeholder="Ingrese el telefono..." value="{{ $contacto->telefono }}">
            <label for="comentarios">Observaciones </label>
            <input type="text" name="comentarios" class="form-control mb-2" placeholder="Ingrese las observaciones..." value="{{ $contacto->comentario }}">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>
{{-- <script>
  $('.select-categorias').select2({
    width: '100%',
    dropdownParent: $('#updateContacto-...'), // ver nota abajo
    placeholder: 'Seleccione departamentos',
});
</script> --}}