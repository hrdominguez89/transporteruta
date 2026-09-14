@if ($carga->cliente_tercero)
<div class="modal fade" id="storeContacto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('crearContactoTercero', $carga->cliente_tercero?->id) }}" method="POST">
        @csrf
        <div class="modal-header bg-danger">
          <h5 class="modal-title">Agregar contacto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <label>Nombre:</label>
          <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." required>
          <label>Apellido:</label>
          <input type="text" name="lastname" class="form-control mb-2" placeholder="Ingrese el apellido...">
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
                            id="store-tercero-cat-{{ Str::slug($value) }}">
                      <label class="form-check-label" for="store-tercero-cat-{{ Str::slug($value) }}">{{ $label }}</label>
                  </div>
              @endforeach
          </div>
          <label>Mail:</label>
          <input type="text" name="mail" class="form-control mb-2" placeholder="Ingrese un mail...">
          <label>Telefono:</label>
          <input type="text" name="telefono" class="form-control mb-2" placeholder="Ingrese el telefono..." required>
          <label>Observaciones</label>
          <input type="text" name="comentarios" class="form-control mb-2" placeholder="Ingrese las observaciones...">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif