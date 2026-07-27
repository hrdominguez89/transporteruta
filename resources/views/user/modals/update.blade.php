<div class="modal fade" id="updateModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('updateUser', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="modal-header bg-success">
          <h5 class="modal-title">Editar Usuario</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <label for="name{{ $user->id }}">Nombre:</label>
          <input id="name{{ $user->id }}" type="text" name="name" class="form-control mb-2"
                 value="{{ old('name', $user->name) }}" required>

          <label for="email{{ $user->id }}">Email:</label>
          <input id="email{{ $user->id }}" type="email" name="email" class="form-control mb-2"
                 value="{{ old('email', $user->email) }}" required>

          <label for="password{{ $user->id }}">Contraseña:</label>
          <input id="password{{ $user->id }}" type="password" name="password" class="form-control mb-2"
                 placeholder="Dejar vacío para no modificar">

          <label for="role{{ $user->id }}">Rol:</label>
          <select id="role{{ $user->id }}" name="role" class="form-control mb-2" required>
            <option value="ADMIN"  {{ old('role', $user->role) == 'ADMIN'  ? 'selected' : '' }}>Administrador</option>
            <option value="CLIENT" {{ old('role', $user->role) == 'CLIENT' ? 'selected' : '' }}>Cliente</option>
          </select>

          <div id="clientGroup{{ $user->id }}" class="d-none">
            <label for="client_id{{ $user->id }}">Cliente:</label>
            <select id="client_id{{ $user->id }}" name="client_id" class="form-control mb-2">
              <option value="">Seleccione un cliente...</option>
              @foreach ($clients as $client)
                <option value="{{ $client->id }}"
                  {{ old('client_id', $user->client_id) == $client->id ? 'selected' : '' }}>
                  {{ $client->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-sm btn-success">Actualizar</button>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
(function () {
    const rol     = document.getElementById('role{{ $user->id }}');
    const grupo   = document.getElementById('clientGroup{{ $user->id }}');
    const cliente = document.getElementById('client_id{{ $user->id }}');

    function toggle() {
        const esCliente = rol.value === 'CLIENT';

        grupo.classList.toggle('d-none', !esCliente);
        cliente.required = esCliente;

        if (!esCliente) cliente.value = '';
    }

    rol.addEventListener('change', toggle);
    toggle();
})();
</script>