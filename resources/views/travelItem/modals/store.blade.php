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
                        <option value="DESCARGA">Descarga</option>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById("type").addEventListener("change", function() {
        var type = this.value;
        if (type === "HORA") {
            document.getElementById("totalTime_div").style.display = "block";
            document.getElementById("totalHours").setAttribute("required", "required");
            document.getElementById("totalMinutes").setAttribute("required", "required");
            document.getElementById("price_div").style.display = "block";
            document.getElementById("price").setAttribute("required", "required");

            document.getElementById("distance_div").style.display = "none";
            document.getElementById("distance").removeAttribute("required");
            document.getElementById("porcentaje_div").style.display = "none";
            document.getElementById("porcentaje").removeAttribute("required");

            document.getElementById("textoPrecio").innerHTML = "Precio por Hora";
        } else if (type === "KILOMETRO") {
            document.getElementById("distance_div").style.display = "block";
            document.getElementById("distance").setAttribute("required", "required");
            document.getElementById("price_div").style.display = "block";
            document.getElementById("price").setAttribute("required", "required");

            document.getElementById("totalTime_div").style.display = "none";
            document.getElementById("totalHours").removeAttribute("required");
            document.getElementById("totalMinutes").removeAttribute("required");
            document.getElementById("porcentaje_div").style.display = "none";
            document.getElementById("porcentaje").removeAttribute("required");
            document.getElementById("textoPrecio").innerHTML = "Precio por Kilometro";

        } else if (type === "ADICIONAL") {
            document.getElementById("porcentaje_div").style.display = "block";
            document.getElementById("porcentaje").setAttribute("required", "required");

            document.getElementById("totalTime_div").style.display = "none";
            document.getElementById("totalHours").removeAttribute("required");
            document.getElementById("totalMinutes").removeAttribute("required");
            document.getElementById("distance_div").style.display = "none";
            document.getElementById("distance").removeAttribute("required");
            document.getElementById("price_div").style.display = "none";
            document.getElementById("price").removeAttribute("required");
            document.getElementById("textoPrecio").innerHTML = "";

            document.getElementById('porcentaje').addEventListener('input', function() {
                var porcentaje = parseFloat(this.value.replace(',', '.'));
                var tarifaFija = parseFloat(this.dataset.tarifaFija);

                if (!isNaN(porcentaje) && !isNaN(tarifaFija)) {
                    var calculo = (porcentaje / 100) * tarifaFija;
                    var montoFormateado = calculo.toLocaleString('es-AR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    document.getElementById('calculoPorcentaje').innerHTML = "El monto es: $ " +
                        montoFormateado;
                } else {
                    document.getElementById('calculoPorcentaje').innerHTML = "";
                }
            });



        } else if (type === "") {
            document.getElementById("totalTime_div").style.display = "none";
            document.getElementById("totalHours").removeAttribute("required");
            document.getElementById("totalMinutes").removeAttribute("required");
            document.getElementById("distance_div").style.display = "none";
            document.getElementById("distance").removeAttribute("required");
            document.getElementById("price_div").style.display = "none";
            document.getElementById("price").removeAttribute("required");
            document.getElementById("porcentaje_div").style.display = "none";
            document.getElementById("porcentaje").removeAttribute("required");
            document.getElementById("textoPrecio").innerHTML = "";

        } else {
            document.getElementById("price_div").style.display = "block";
            document.getElementById("price").setAttribute("required", "required");

            document.getElementById("totalTime_div").style.display = "none";
            document.getElementById("totalHours").removeAttribute("required");
            document.getElementById("totalMinutes").removeAttribute("required");
            document.getElementById("distance_div").style.display = "none";
            document.getElementById("distance").removeAttribute("required");
            document.getElementById("porcentaje_div").style.display = "none";
            document.getElementById("porcentaje").removeAttribute("required");
            document.getElementById("textoPrecio").innerHTML = "";

        }
    });
</script>
