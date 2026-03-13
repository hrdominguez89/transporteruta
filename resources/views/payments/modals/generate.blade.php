<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Ingresar pago</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ Route('generatePayment') }}" class="form-group" method="POST">
                    @csrf

                    <label for="clientId">Cliente:</label>
                    <select name="clientId" class="form-control mb-2 @error('clientId') is-invalid @enderror" required>
                        <option value="">---- Seleccione una opcion ----</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ old('clientId') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>

                    <label for="metodo">Metodo:</label>
                    <select requierd name="metodo" id="metodo" class="form-control mb-2">
                        <option> Seleccione un metodo</option>
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TRANSFERENCIA">Transferencia</option>
                        <option value="CHEQUE">Cheque</option>
                        <option value="E-CHEQ">E-cheq</option>
                        <option value="TARJETACREDITO">Tarjeta de credito</option>
                        <option value="TARJETADEBITO">Tarjeta de debito</option>
                        <option value="PAGARE">Pagare</option>
                    </select>

                    <div id="tipochque_div" style="display:none">
                        <label for="tipoCheque">Tipo de cheque:</label>
                        <select name="tipoCheque" id="tipoCheque" class="form-control mb-2">
                            <option value="PROPIO">Propio</option>
                            <option value="TERCERO">Tercero</option>
                        </select>
                    </div>
                    
                    <div id="fecharecepcion_div"  style="display:none">
                        <label for="fecharecepcion">Fecha de recepcion</label>
                        <input type="date" id="fecharecepcion" name="fecharecepcion" class="form-control mb-2">
                    </div>

                    <div id="fechadeacreditacion_div"  style="display:none">
                        <label for="fechadeacreditacion">Fecha de acreditacion</label>
                        <input type="date" id="fechadeacreditacion" name="fechadeacreditacion" class="form-control mb-2">
                    </div>
                    
                    <div id="banco_div" style="display:none"> 
                        <label for="banco">Banco*:</label>
                        <input type="text" name="banco" class="form-control mb-2 " id ="banco">
                    </div>

                    <div id="nro_cheq_div" style="display:none"> 
                        <label for="nro_cheq">Numero de cheque*:</label>
                        <input type="text" name="nro_cheq" class="form-control mb-2 " id ="nro_cheq">
                    </div>


                    <label for="monto">*Monto:</label>
                    <input type="text" id="monto" name="monto" class="form-control mb-2" required>

                    <label  for="comentarios">Comentarios</label>
                    <input type="text" id="comentarios" name="comentarios" class="form-control mb-2">
                    
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
    function validarmetodo()
    {
        document.getElementById('metodo').addEventListener('change', function () {
            document.getElementById('tipochque_div').style.display = this.value === 'CHEQUE' ? 'block' : 'none';
            document.getElementById('fecharecepcion_div').style.display = this.value === 'EFECTIVO' ? 'block' : 'none';
            document.getElementById('fechadeacreditacion_div').style.display = this.value === 'TRANSFERENCIA' ? 'block' : 'none';
            document.getElementById('banco_div').style.display = ( this.value === 'TRANSFERENCIA' || this.value === 'CHEQUE' || this.value === 'E-CHEQ' ) ? 'block' : 'none';
            document.getElementById('nro_cheq_div').style.display = ( this.value === 'CHEQUE' || this.value === 'E-CHEQ' ) ? 'block' : 'none';
            document.getElementById('nro_cheq_div').required = (this.value === 'CHEQUE' || this.value === 'E-CHEQ');
        });
    }
    validarmetodo();
</script>