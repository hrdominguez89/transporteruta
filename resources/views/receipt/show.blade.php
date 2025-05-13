@extends('adminlte::page')

@section('title', 'Recibos')

@section('content_header')
    <div class="row">
        <a href="{{ Route('receipts') }}" class="btn btn-sm btn-sm btn-secondary">Volver</a>
        <h1 class="col-7">Recibo N°<strong>{{ number_format($receipt->number, 0, ',', '.') }}</strong></h1>
        @if ($receipt->paid == 'NO')
            <button class="btn btn-sm btn-sm btn-warning col-4" data-toggle="modal"
                data-target="#paidModal{{ $receipt->id }}">Marcar
                como
                Pagado</button>
        @else
            <button class="btn btn-sm btn-sm btn-danger col-2 mr-2" data-toggle="modal"
                data-target="#cancelModal{{ $receipt->id }}">Anular
                Pago</button>
            <a href="{{ Route('receiptPdf', $receipt->id) }}" class="btn btn-sm btn-sm btn-info col-2">Generar PDF</a>
        @endif
    </div>
    @if ($receipt->paid == 'SI')
        <h5 class="text-danger">El recibo se marco como pagado y se desconto el saldo de la cuenta corriente</h5>
    @endif
    @include('receipt.modals.paid')
    @include('receipt.modals.cancel')
@stop

@section('content')
    <table class="table table-bordered text-center">
        <thead class="bg-danger">
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Pagado</th>
                <th>Retenciones</th>
                <th>Saldo total recibido</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ \Carbon\Carbon::parse($receipt->date)->format('d/m/Y') }}</td>
                <td>
                    <a href="{{ Route('showClient', $receipt->client->id) }}">{{ $receipt->client->name }}</a>
                </td>
                <td>{{ $receipt->paid }}</td>
                <td>$&nbsp;{{ number_format($receipt->taxTotal, 2, ',', '.') }}</td>
                <td>$&nbsp;{{ number_format($receipt->total, 2, ',', '.') }}</td>
                <td>$&nbsp;{{ number_format($receipt->total + $receipt->taxTotal, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    <h4>Facturas Agregadas</h4>
    <table class="table table-sm table-bordered text-center data-table">
        <thead class="bg-danger">
            <tr>
                <th>Número</th>
                <th>Cliente</th>
                <th>Medio de pago</th>
                <th>Valor de la factura (Con IVA)</th>
                <th>Saldo recibido</th>
                <th>Retenciones</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receiptInvoices as $ri)
                <tr>
                    <td data-order="{{ $ri->invoice->number ?? 0 }}">
                        <a href="{{ route('showInvoice', $ri->invoice->id) }}">
                            {{ number_format($ri->invoice->number, 0, ',', '.') }}
                        </a>
                    </td>
                    <td>{{ $ri->invoice->client->name ?? 'N/A' }}</td>
                    <td>{{ $ri->paymentMethod->name ?? 'N/A' }}</td>
                    <td data-order="{{ $ri->invoice->totalWithIva ?? 0 }}">
                        $&nbsp;{{ number_format($ri->invoice->totalWithIva ?? 0, 2, ',', '.') }}
                    </td>
                    <td data-order="{{ $ri->total }}">
                        $&nbsp;{{ number_format($ri->total, 2, ',', '.') }}
                    </td>
                    <td data-order="{{ $ri->taxAmount ?? 0 }}" class="text-center">
                        @if($ri->taxAmount > 0)
                        <table style="width:100%" class="table table-striped table-bordered">
                            <thead>
                                <tr style="background-color:#dedede;">
                                    <th colspan="3">
                                        Total: $&nbsp;{{ number_format($ri->taxAmount ?? 0, 2, ',', '.') }}
                                    </th>
                                </tr>
                                <tr style="background-color:#dedede;">
                                    <th>
                                        Impuesto
                                    </th>

                                    <th>
                                        Monto
                                    </th>

                                    <th>
                                        Acción
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ri->taxes as $tax)
                                    <tr>
                                        <td>
                                            {{ $tax->tax->name ?? 'N/A' }}
                                        </td>

                                        <td>
                                            $&nbsp;{{ number_format($tax->taxAmount ?? 0, 2, ',', '.') }}
                                        </td>

                                        <td>
                                            @if ($receipt->paid == 'NO')
                                                <button class="btn btn-sm btn-danger" data-toggle="modal"
                                                    data-target="#deleteTaxModal{{ $tax->id }}">Eliminar</button>
                                                    
                                                @include('receipt.modals.delete')
                                            @else
                                                <strong class="text-danger">¡No se pueden realizar cambios!</strong>
                                            @endif

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                        @else
                            <b>Total: $ 0,00</b>
                        @endif
                    </td>
                    <td data-order="{{ $ri->taxAmount ?? 0 }}">
                        $&nbsp;{{ number_format($ri->taxAmount + $ri->total ?? 0, 2, ',', '.') }}
                    </td>
                    <td>
                        @if ($receipt->paid == 'NO')
                            <div class="d-flex justify-content-center align-items-center">
                                <button class="btn btn-sm btn-warning ml-2 mr-2" data-toggle="modal"
                                                    data-target="#deleteReceiptModal{{ $ri->id }}">Quitar del Recibo</button>

                                <button class="btn btn-sm btn-info ml-2 mr-2" data-toggle="modal"
                                    data-target="#addTax{{ $ri->id }}">
                                    Agregar retención
                                </button>
                            </div>
                            @include('receipt.modals.deleteReceipt')
                            @include('receipt.modals.addTax')
                        @else
                            <strong class="text-danger">¡No se pueden realizar cambios!</strong>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    @if ($receipt->paid == 'NO')
        <h4>Facturas del Cliente sin Pagar</h4>
        <table class="table table-sm table-bordered text-center data-table">
            <thead class="bg-danger">
                <tr>
                    <th>Numero</th>
                    <th>Cliente</th>
                    <th>Precio (Con IVA)</th>
                    <th>Saldo pendiente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoicesToAdd as $invoice)
                    <tr>
                        <td data-order="{{ $invoice->number }}">
                            <a
                                href="{{ Route('showInvoice', $invoice->id) }}">{{ number_format($invoice->number, 0, ',', '.') }}</a>
                        </td>
                        <td>{{ $invoice->client->name }}</td>
                        <td data-order="{{ $invoice->totalWithIva }}">
                            $&nbsp;{{ number_format($invoice->totalWithIva, 2, ',', '.') }}</td>
                        <td data-order="{{ $invoice->balance }}">
                            $&nbsp;{{ number_format($invoice->balance, 2, ',', '.') }}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-sm btn-success" data-toggle="modal"
                                data-target="#addInvoiceModal{{ $invoice->id }}{{ $receipt->id }}">Agregar al
                                Recibo</button>
                        </td>
                    </tr>
                    @include('receipt.modals.addInvoice')
                @endforeach
            </tbody>
        </table>
    @endif
@stop
@section('js')
    <script>
        $(document).ready(function() {
            $('.data-table').DataTable();
        });
        var table = new DataTable('.data-table', {
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            }
        });
        $('.select2').select2();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cuando un modal se abre
            document.querySelectorAll('.modal').forEach(function(modal) {
                modal.addEventListener('shown.bs.modal', function() {
                    // Para cada input dentro del modal recién abierto
                    modal.querySelectorAll('.balanceToPay').forEach(function(inputEl) {
                        const formEl = inputEl.closest('form');
                        if (!formEl) return;

                        const spanEl = formEl.querySelector('.saldo_restante');
                        if (!spanEl) return;

                        function updateBalanceDisplay() {
                            const value = parseFloat(inputEl.value);
                            if (!isNaN(value) && value > 0) {
                                const formatted = value.toLocaleString('es-AR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                spanEl.innerHTML = `$&nbsp;${formatted}`;
                            } else {
                                spanEl.innerHTML = '';
                            }
                        }

                        inputEl.addEventListener('input', updateBalanceDisplay);
                        updateBalanceDisplay();
                    });
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.balanceToPay').forEach(function(inputEl) {
                // Obtener el ID del input, que sigue la forma balanceToPay_invoice_123
                const inputId = inputEl.getAttribute('id');
                const invoiceId = inputId.replace('balanceToPay_invoice_', '');

                // Usar invoiceId para encontrar el span correspondiente
                const spanEl = document.getElementById(`saldo_restante_invoice_${invoiceId}`);
                if (!spanEl) return;

                function updateBalanceDisplay() {
                    const value = parseFloat(inputEl.value);
                    if (!isNaN(value) && value > 0) {
                        const formatted = value.toLocaleString('es-AR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        spanEl.innerHTML = `$&nbsp;${formatted}`;
                    } else {
                        spanEl.innerHTML = '';
                    }
                }

                inputEl.addEventListener('input', updateBalanceDisplay);
                updateBalanceDisplay();
            });
        });
    </script>

@stop
