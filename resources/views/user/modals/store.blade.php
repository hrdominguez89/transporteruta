<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Usuario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ Route('storeUser') }}"  method="POST">
            @csrf
            <label for="name">Nombre:</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="Ingrese el nombre..." required>
            <label for="email">Email:</label>
            <input type="text" name="email" class="form-control mb-2" placeholder="Ingrese el E-Mail..." required>
            <label for="password">Contraseña:</label>
            <input type="password" name="password" class="form-control mb-2" placeholder="Ingrese la contraseña..." required>
            <label for="role">Rol:</label>
            <select name="role" id="role" class="form-control mb-2" required>
                <option value="ADMIN" >Administrador</option>
                <option value="CLIENT">Cliente</option>
            </select>
            <div id="clientGroup" class="d-none">
              <label for="client_id">Cliente:</label>
              <select name="client_id" id="client_id" class="form-control mb-2">
                  <option value="">Seleccione un cliente...</option>
                  @foreach ($clients as $client)
                      <option value="{{ $client->id }}">{{ $client->name }}</option>
                  @endforeach
              </select>
          </div>
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
function roles()
{
    const rol     = document.getElementById('role');
    const grupo   = document.getElementById('clientGroup');
    const cliente = document.getElementById('client_id');

    function toggle() {
        const esCliente = rol.value === 'CLIENT';

        grupo.classList.toggle('d-none', !esCliente);
        cliente.required = esCliente;

        if (!esCliente) cliente.value = '';
    }

    rol.addEventListener('change', toggle);
    toggle();
}
roles();
</script>
@endsection