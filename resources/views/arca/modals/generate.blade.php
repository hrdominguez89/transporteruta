<div class="modal fade" id="generateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('generateArcaInvoice') }}" method="POST">
                @csrf

                <div class="modal-header bg-danger">
                    <h5 class="modal-title">Generar Factura ARCA</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <label for="invoiceId">Factura:</label>
                    <select name="invoiceId" id="invoiceId"
                            class="form-control mb-2 @error('invoiceId') is-invalid @enderror" required>
                        <option value="">---- Seleccione una factura ----</option>
                        @foreach ($invoices as $invoice)
                            <option value="{{ $invoice->id }}" {{ old('invoiceId') == $invoice->id ? 'selected' : '' }}>
                                #{{ $invoice->number }} —
                                {{ $invoice->client->name ?? 's/cliente' }} —
                                $ {{ number_format($invoice->totalWithIva, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('invoiceId')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <label for="punto_vta">Punto de Venta:</label>
                    <input type="number" name="punto_vta" id="punto_vta"
                           class="form-control mb-2 @error('punto_vta') is-invalid @enderror"
                           value="{{ old('punto_vta', 3) }}" min="1" required>
                    @error('punto_vta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <small class="text-muted">
                        El monto, la fecha y el cliente se toman de la factura seleccionada.
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Generar</button>
                </div>
            </form>
        </div>
    </div>
</div>