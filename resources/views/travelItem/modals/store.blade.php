<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Agregar Item de Viaje</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('storeTravelItem', $travelCertificate->id) }}" class="form-group" method="POST">
                    @csrf
                    <label for="type">Tipo:<span class="text-danger"> *</span></label>
                    <select id="type" name="type" class="form-control mb-2" required>
                        <option value="">---- Seleccione una opción ----</option>
                        <option value="HORA">Por Hora</option>
                        <option value="KILOMETRO">Por Kilometro</option>
                        <option value="PEAJE">Peaje</option>
                        @if ($tarifa_fija)
                            <option value="ADICIONAL">Adicional</option>
                        @else
                            <option value="FIJO">Tarifa Fija</option>
                        @endif
                        <option value="MULTIDESTINO">Multidestino</option>
                        <option value="DESCARGA">Carga/Descarga</option>
                        <!--AGREGAMOS LA OPCIÓN DESCUENTO-->
                         <option value="DESCUENTO">Descuento</option> 
                    </select>

                    <label for="description">Descripción:</label>
                    <input id="description" type="text" name="description" class="form-control mb-2">

                    <div style="display: none;" id="totalTime_div">
                        <label for="totalHours">Tiempo Total: <span class="text-danger"> *</span></label>
                        <div class="d-flex gap-2">
                            <input id="totalHours" type="number" name="totalHours" step="1" min="0"
                                class="form-control mb-2" placeholder="Horas">

                            <select id="totalMinutes" name="totalMinutes" class="form-control mb-2">
                                @foreach (['00', '15', '30', '45'] as $min)
                                    <option value="{{ $min }}">{{ $min }} min</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="display: none;" id="distance_div">

                        <label for="distance">Distancia:<span class="text-danger"> *</span></label>
                        <input id="distance" type="number" step="1" min="0" name="distance" class="form-control mb-2"
                            placeholder="Ingrese la distancia">
                    </div>
                    <div style="display: none;" id="price_div">
                        <label for="price">Precio:<span class="text-danger"> * </span><small id="textoPrecio"
                                class="text-danger"></small></label>
                        <input id="price" type="number" name="price" step="0.01" class="form-control mb-2"
                            placeholder="Ingrese el precio">
                    </div>
                    <div style="display: none;" id="porcentaje_div">
                        <label for="porcentaje">Porcentaje de $ {{ number_format($tarifa_fija, 2, ',', '.') }}:<span
                                class="text-danger"> *</span></label>
                        <input id="porcentaje" type="number" step="0.01" min="0" max="100"
                            name="porcentaje" class="form-control mb-2" placeholder="Ingrese el porcentaje"
                            data-tarifa-fija="{{ $tarifa_fija }}">
                        <small id="calculoPorcentaje"></small>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
  // Helpers
  const $ = id => document.getElementById(id);
  const show = id => $(id).style.display = "block";
  const hide = id => $(id).style.display = "none";
  const req  = id => $(id).setAttribute("required", "required");
  const unreq= id => $(id).removeAttribute("required");
  const text = (id, val="") => $(id).innerHTML = val;

  const typeSel = $("type");

  function hideAll() {
    hide("totalTime_div");  unreq("totalHours"); unreq("totalMinutes");
    hide("distance_div");   unreq("distance");
    hide("price_div");      unreq("price");
    hide("porcentaje_div"); unreq("porcentaje");
    text("textoPrecio", "");
    text("calculoPorcentaje", "");
  }

  function formatARS(n) {
    if (isNaN(n)) return "";
    return n.toLocaleString("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Listener único para porcentaje (fix de duplicados)
  const porcentajeInput = $("porcentaje");
  if (porcentajeInput) {
    porcentajeInput.addEventListener("input", function() {
      // Solo calcula si el tipo seleccionado es ADICIONAL
      if (!typeSel || typeSel.value !== "ADICIONAL") {
        text("calculoPorcentaje", "");
        return;
      }
      const porc   = parseFloat(this.value.replace(",", "."));
      const tarifa = parseFloat(this.dataset.tarifaFija);
      if (!isNaN(porc) && !isNaN(tarifa)) {
        const monto = (porc / 100) * tarifa;
        text("calculoPorcentaje", "El monto es: $ " + formatARS(monto));
      } else {
        text("calculoPorcentaje", "");
      }
    });
  }

  function updateUI() {
    const type = typeSel ? typeSel.value : "";

    switch (type) {
      case "HORA":
        show("totalTime_div"); req("totalHours"); req("totalMinutes");
        show("price_div");     req("price");
        hide("distance_div");  unreq("distance");
        hide("porcentaje_div");unreq("porcentaje");
        text("textoPrecio", "Precio por Hora");
        text("calculoPorcentaje", "");
        break;

      case "KILOMETRO":
        show("distance_div");  req("distance");
        show("price_div");     req("price");
        hide("totalTime_div"); unreq("totalHours"); unreq("totalMinutes");
        hide("porcentaje_div");unreq("porcentaje");
        text("textoPrecio", "Precio por Kilometro");
        text("calculoPorcentaje", "");
        break;

      case "ADICIONAL":
        show("porcentaje_div"); req("porcentaje");
        hide("totalTime_div");  unreq("totalHours"); unreq("totalMinutes");
        hide("distance_div");   unreq("distance");
        hide("price_div");      unreq("price");
        text("textoPrecio", "");
        // recalcular por si ya había un valor cargado
        porcentajeInput && porcentajeInput.dispatchEvent(new Event("input"));
        break;

      case "DESCUENTO":
        show("price_div");     req("price");
        hide("totalTime_div"); unreq("totalHours"); unreq("totalMinutes");
        hide("distance_div");  unreq("distance");
        hide("porcentaje_div");unreq("porcentaje");
        text("textoPrecio", "Monto del descuento (ingresá un valor positivo)");
        text("calculoPorcentaje", "");
        break;

      case "": // opción vacía
        hideAll();
        break;

      default: // PEAJE, FIJO, MULTIDESTINO, DESCARGA, etc.
        show("price_div");     req("price");
        hide("totalTime_div"); unreq("totalHours"); unreq("totalMinutes");
        hide("distance_div");  unreq("distance");
        hide("porcentaje_div");unreq("porcentaje");
        text("textoPrecio", "");
        text("calculoPorcentaje", "");
    }
  }

  // Cambios de tipo
  typeSel && typeSel.addEventListener("change", updateUI);

  // Estado inicial (por si el modal abre con algo seleccionado)
  updateUI();
})();
</script>
