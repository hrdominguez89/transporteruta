<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title">Agregar Item de Viaje</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- NUEVO ITEM N° REMITO -->
      <div class="modal-body">
        <form action="{{ Route('storeTravelItem', $travelCertificate->id) }}" class="form-group" method="POST">
          @csrf
          <label for="type">Tipo:<span class="text-danger"> *</span></label>
          <select id="type" name="type" class="form-control mb-2" required>
            <option value="">---- Seleccione una opción ----</option>
            <option value="REMITO">N° Remito</option>   {{-- NUEVO --}}
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
            <option value="DESCUENTO">Descuento</option>
          </select>

          {{-- NUEVO: campo específico para N° Remito --}}
          <div id="remito_div" style="display:none;">
            <label for="remito_number">N° de Remito:<span class="text-danger"> *</span></label>
            <input id="remito_number" type="text" name="remito_number" class="form-control mb-2"
                   placeholder="Ej: 0001-00012345">
          </div>

          {{-- ENVOLVEMOS descripción para poder ocultarla cuando sea REMITO --}}
          <div id="description_div">
            <label for="description">Descripción:</label>
            <input id="description" type="text" name="description" class="form-control mb-2">
          </div>

          {{-- ⬇⬇⬇ NUEVO: soporte a Descuento por % sin migraciones ⬇⬇⬇ --}}
          {{-- Selector del modo de descuento (monto fijo vs porcentaje). Solo visible si type = DESCUENTO --}}
          <div id="discount_mode_div" style="display:none;">
            <label for="discount_mode">Modo de descuento:<span class="text-danger"> *</span></label>
            <select id="discount_mode" name="discount_mode" class="form-control mb-2">
              <option value="amount" selected>Monto fijo</option>
              <option value="percent">Porcentaje</option>
            </select>
          </div>

          {{-- Campo de porcentaje de descuento. Solo visible si discount_mode = percent --}}
          <div id="discount_percent_div" style="display:none;">
            <label for="discount_percent">Porcentaje:<span class="text-danger"> *</span></label>
            <input id="discount_percent" type="number" step="0.01" min="0" max="100"
                   name="discount_percent" class="form-control mb-2" placeholder="Ej: 10 = 10%">
            <small id="preview_descuento"></small>
          </div>
          {{-- ⬆⬆⬆ FIN NUEVO: soporte a Descuento por % ⬆⬆⬆ --}}

          <div style="display: none;" id="totalTime_div">
            <label for="totalHours">Tiempo Total: <span class="text-danger"> *</span></label>
            <div class="d-flex gap-2">
              {{-- FIX: valor por defecto 0 para evitar required vacío --}}
              <input id="totalHours" type="number" name="totalHours" step="1" min="0"
                     class="form-control mb-2" placeholder="Horas" value="0">

              {{-- FIX: values numéricos para pasar integer|in:0,15,30,45 --}}
              <select id="totalMinutes" name="totalMinutes" class="form-control mb-2">
                @foreach ([0, 15, 30, 45] as $min)
                  <option value="{{ $min }}">{{ str_pad($min, 2, '0', STR_PAD_LEFT) }} min</option>
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
                   name="percent" class="form-control mb-2" placeholder="Ingrese el porcentaje"
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
(function () {
  // Helpers
  const $ = id => document.getElementById(id);
  const show = id => $(id).style.display = "block";
  const hide = id => $(id).style.display = "none";
  const req  = id => $(id).setAttribute("required", "required");
  const unreq= id => $(id).removeAttribute("required");
  const text = (id, val = "") => $(id).innerHTML = val;

  const typeSel = $("type");

  function hideAll() {
    hide("remito_div");       unreq("remito_number");     // <-- NUEVO
    show("description_div");                                 // por defecto visible
    hide("totalTime_div");    unreq("totalHours"); unreq("totalMinutes");
    hide("distance_div");     unreq("distance");
    hide("price_div");        unreq("price");
    hide("porcentaje_div");   unreq("porcentaje");

    // NUEVO: esconder controles de descuento por % cuando no corresponde
    hide("discount_mode_div");    unreq("discount_mode");
    hide("discount_percent_div"); unreq("discount_percent");
    text("preview_descuento", "");

    text("textoPrecio", "");
    text("calculoPorcentaje", "");
  }

  function formatARS(n) {
    if (isNaN(n)) return "";
    return n.toLocaleString("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Listener único para porcentaje (ADICIONAL) – se mantiene tal cual
  const porcentajeInput = $("porcentaje");
  if (porcentajeInput) {
    porcentajeInput.addEventListener("input", function () {
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

  // ──────────────────────
  // NUEVO: lógica de "Descuento por %"
  // ──────────────────────
  function updateDiscountModeUI() {
    const modeSel = $("discount_mode");
    const mode = modeSel ? modeSel.value : "amount";

    if (mode === "percent") {
      // Si es porcentaje → pedimos % y ocultamos el monto "Precio"
      show("discount_percent_div");  req("discount_percent");
      hide("price_div");             unreq("price");
      text("textoPrecio", "");
    } else {
      // Si es monto fijo → mostramos "Precio" y ocultamos %
      hide("discount_percent_div");  unreq("discount_percent");
      show("price_div");             req("price");
      text("textoPrecio", "Monto del descuento (ingresá un valor positivo)");
      text("preview_descuento", "");
    }
  }

  // (Opcional) Vista previa del % de descuento. Se puede completar con base si quisieras.
  const discountPercent = $("discount_percent");
  if (discountPercent) {
    discountPercent.addEventListener("input", function () {
      const p = parseFloat((this.value || "").replace(",", "."));
      if (isNaN(p)) { text("preview_descuento", ""); return; }
      // Si querés, podés mostrar una preview estimada en base a un subtotal si lo tenés a mano.
      // text("preview_descuento", `Ejemplo: ${p}% de $ ${formatARS(base)} = $ ${formatARS(base * p / 100)}`);
    });
  }

  function updateUI() {
    const type = typeSel ? typeSel.value : "";

    switch (type) {
      case "REMITO": // <-- NUEVO
        show("remito_div");     req("remito_number");
        hide("description_div");
        hide("totalTime_div");  unreq("totalHours"); unreq("totalMinutes");
        hide("distance_div");   unreq("distance");
        hide("price_div");      unreq("price");
        hide("porcentaje_div"); unreq("porcentaje");
        // Ocultar controles de descuento si venían visibles
        hide("discount_mode_div");    unreq("discount_mode");
        hide("discount_percent_div"); unreq("discount_percent");
        text("textoPrecio", ""); text("calculoPorcentaje", ""); text("preview_descuento", "");
        break;

      case "HORA":
        hide("remito_div");     unreq("remito_number");
        show("description_div");
        show("totalTime_div");  req("totalHours"); req("totalMinutes");
        show("price_div");      req("price");
        hide("distance_div");   unreq("distance");
        hide("porcentaje_div"); unreq("porcentaje");
        // Ocultar controles de descuento si venían visibles
        hide("discount_mode_div");    unreq("discount_mode");
        hide("discount_percent_div"); unreq("discount_percent");
        text("textoPrecio", "Precio por Hora");
        text("calculoPorcentaje", ""); text("preview_descuento", "");
        break;

      case "KILOMETRO":
        hide("remito_div");     unreq("remito_number");
        show("description_div");
        show("distance_div");   req("distance");
        show("price_div");      req("price");
        hide("totalTime_div");  unreq("totalHours"); unreq("totalMinutes");
        hide("porcentaje_div"); unreq("porcentaje");
        // Ocultar controles de descuento si venían visibles
        hide("discount_mode_div");    unreq("discount_mode");
        hide("discount_percent_div"); unreq("discount_percent");
        text("textoPrecio", "Precio por Kilometro");
        text("calculoPorcentaje", ""); text("preview_descuento", "");
        break;

      case "ADICIONAL":
        hide("remito_div");     unreq("remito_number");
        show("description_div");
        show("porcentaje_div"); req("porcentaje");
        hide("totalTime_div");  unreq("totalHours"); unreq("totalMinutes");
        hide("distance_div");   unreq("distance");
        hide("price_div");      unreq("price");
        // Ocultar controles de descuento si venían visibles
        hide("discount_mode_div");    unreq("discount_mode");
        hide("discount_percent_div"); unreq("discount_percent");
        text("textoPrecio", "");
        porcentajeInput && porcentajeInput.dispatchEvent(new Event("input"));
        text("preview_descuento", "");
        break;

      case "DESCUENTO":
        hide("remito_div");     unreq("remito_number");
        show("description_div");
        // NUEVO: mostramos el selector de modo y ajustamos UI según la elección
        show("discount_mode_div"); req("discount_mode");
        updateDiscountModeUI();

        hide("totalTime_div");  unreq("totalHours"); unreq("totalMinutes");
        hide("distance_div");   unreq("distance");
        hide("porcentaje_div"); unreq("porcentaje"); // (este es el de ADICIONAL)
        text("calculoPorcentaje", "");
        break;

      case "": // opción vacía
        hideAll();
        break;

      default: // PEAJE, FIJO, MULTIDESTINO, DESCARGA, etc.
        hide("remito_div");     unreq("remito_number");
        show("description_div");
        show("price_div");      req("price");
        hide("totalTime_div");  unreq("totalHours"); unreq("totalMinutes");
        hide("distance_div");   unreq("distance");
        hide("porcentaje_div"); unreq("porcentaje");
        // Ocultar controles de descuento si venían visibles
        hide("discount_mode_div");    unreq("discount_mode");
        hide("discount_percent_div"); unreq("discount_percent");
        text("textoPrecio", "");
        text("calculoPorcentaje", ""); text("preview_descuento", "");
    }
  }

  // Listeners
  typeSel && typeSel.addEventListener("change", updateUI);
  $("discount_mode") && $("discount_mode").addEventListener("change", updateDiscountModeUI);

  updateUI(); // estado inicial
})();
</script>
