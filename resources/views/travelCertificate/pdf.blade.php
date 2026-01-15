{{-- resources/views/travelCertificate/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancias</title>

    {{-- ==================== REFACTORIZACIÓN (compactación 1 carilla) ==================== --}}
    <style>
        @page { margin: 18mm 12mm; }
        html, body { font-size: 11.5px; line-height: 1.15; }
        .container { border: 1px solid #000; padding: 32px; }
        h5, p { margin: 4px 0; line-height: 1.15; }
        .kv { display: flex; justify-content: space-between; margin: 3px 0; line-height: 1.5; }
        .header-img { max-width: 100%; height: auto; display: block; margin: 6px 0 10px; }

        /* Tabla compacta de conceptos */
        .conceptos-table { width: 100%; border-collapse: collapse; font-size: 1rem; line-height: 1.5; }
        .conceptos-table th, .conceptos-table td { padding: 2px 4px; border-bottom: 1px solid #ccc; vertical-align: top; }

        /* Totales compactos */
        .totales p { margin: 2px 0; line-height: 1.5; font-size: 1rem; }
        
    </style>
    {{-- ============================================================================ --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
          integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N"
          crossorigin="anonymous">
</head>

<body>
<div class="container text-center">
    <div class="table-bordered">
        <p style="width: 30px; height: 30px; border: 2px solid #000; font-size: 20px; font-weight: bold; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto;">X</p>
        <div>
            <div style="display: inline-block; width: 48%; vertical-align: top; margin-right: 2%;">
                <img    class="header-img" 
                src="https://media.licdn.com/dms/image/C4D1BAQF9AP8K9M-0WQ/company-background_10000/0/1625358131993/transportes_ruta_s_r_l_cover?e=2147483647&v=beta&t=DMcRvoePh7phfXc3qOGVvqPwkBOIDx37opmL1OcJizM">
                <p style="font-size: 8.5px;">CUIT:30-70908352-5</p>
                <p style="font-size: 8.5px;">Ing. Brutos C.M.: 902-829006-8</p>
                <p style="font-size: 8.5px;">Inicio de actividades: 01-04-05</p>
                <p style="font-size: 8.5px;">Santa Maria de Oro 1020</p>
                <p style="font-size: 8.5px;">B1646AZB San Fernando-PCIA. Bs. As.</p>
                <p style="font-size: 8.5px;">info@transportesruta.com.ar</p>
                <p style="font-size: 8.5px;">Teléfono 4745-1515/4744-7999 / Wpp. 1154033940;</p>
            </div>
            <div style="display: inline-block; width: 48%; vertical-align: top;">
                <h5>CONSTANCIA DE VIAJE N° {{ number_format($travelCertificate->number, 0, ',', '.') }}</h5>
                <p>Documento no valido como factura.</p>
                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($travelCertificate->date)->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>

    <div class="table-bordered text-left mt-3 mb-3 p-2">
        <p class="kv"><strong>Cliente:</strong> <span>{{ $travelCertificate->client->name }}</span></p>
        <p class="kv"><strong>Chofer:</strong> <span>{{ $travelCertificate->driver->name }}</span></p>
        <p class="kv"><strong>Vehículo:</strong> <span>{{ $travelCertificate->driver?->vehicle?->name }}</span></p>
        <p class="kv"><strong>Hora de Salida:</strong> <span></span></p>
        <p class="kv"><strong>Hora de Llegada:</strong> <span></span></p>
    </div>

    <div class="col-12 table-bordered text-left p-2">
        <p><strong>CONCEPTOS:</strong></p>

        {{-- ================= REFACTORIZACIÓN (Conceptos) =================
             Mostrar el monto *real* de cada ítem:
             - DESCUENTO % → monto negativo (computed_price)
             - ADICIONAL % sobre FIJO → display_price
             - Resto → price
             También usamos computed_description cuando exista.
        ---------------------------------------------------------------- --}}
        <table class="conceptos-table">
            <thead>
            <tr>
                <th>Tipo</th>
                <th>Descripción</th>
                <th style="text-align:right;">Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($travelCertificate->travelItems as $travelItem)
                @php
                    // Monto y descripción “inteligentes” (con fallback)
                    $monto = $travelItem->computed_price
                          ?? $travelItem->display_price
                          ?? ($travelItem->price ?? 0);

                    $desc  = $travelItem->computed_description
                          ?? $travelItem->description;

                    $isNeg = $monto < 0;
                @endphp
                <tr>
                    <td>{{ $travelItem->type }}</td>
                    <td>{{ $desc }}</td>
                    <td style="text-align:right; {{ $isNeg ? 'color:#c00' : '' }}">
                        $&nbsp;{{ number_format($monto, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{-- ================= /REFACTORIZACIÓN (Conceptos) ================= --}}
    </div>

    {{-- =====================  REFACTORIZACIÓN (Totales)  =====================
         Usamos los accessors del modelo para mantener una única “fuente de verdad”:
         - subtotal_sin_peajes, total_peajes, descuento_aplicable, monto_adicional
         - total_calculado e iva_calculado para coherencia con la vista HTML
       ---------------------------------------------------------------------- --}}
    @php
        // Peajes: si el controller pasó $totalTolls lo usamos, sino calculamos acá.
        $peajes = isset($totalTolls)
            ? (float) $totalTolls
            : (float) ($travelCertificate->travelItems->where('type', 'PEAJE')->sum('price'));

        // Neto = base gravada sin peajes (subtotal_sin_peajes - descuento + adicional)
        $importeNeto = max(0,
            (($travelCertificate->subtotal_sin_peajes ?? 0)
            - ($travelCertificate->descuento_aplicable ?? 0)
            + ($travelCertificate->monto_adicional ?? 0))
        );

        // IVA calculado por el modelo (21% sobre base gravada)
        $ivaCalculado = (float) ($travelCertificate->iva_calculado ?? 0);

        // Total final
        $totalFinal = $importeNeto + $peajes + $ivaCalculado;
    @endphp

    <div class=" table-bordered text-left mt-2 p-2 totales">
        <p class="kv"><strong>IMPORTE NETO:</strong>
            <span>$&nbsp;{{ number_format($importeNeto, 2, ',', '.') }}</span>
        </p>
        <p class="kv"><strong>PEAJES:</strong>
            <span>$&nbsp;{{ number_format($peajes, 2, ',', '.') }}</span>
        </p>
        <p class="kv"><strong>IVA:</strong>
            <span>$&nbsp;{{ number_format($ivaCalculado, 2, ',', '.') }}</span>
        </p>
        <p class="kv"><strong>TOTAL:</strong>
            <span>$&nbsp;{{ number_format($totalFinal, 2, ',', '.') }}</span>
        </p>
    </div>
    <div class="table-bordered">
        <p>LA MERCADERIA VIAJA POR CUENTA Y RIESGO DEL CLIENTE.</p>
        <P>NOTA: El horario rige desde que el vehiculo sale de la agencia hasta que regresa a la misma.</P>
    </div>
    <div class =" text-center table-bordered">
        <p>La presente no tiene valor como recibo oficial. Se emitirá la factura correspondiente por la suma de los valores de los viajes devengados en las constancias de viaje.</p>
        <p>Conforme:</p>
        <p>________________________</p>
    </div>
</div>
</body>
</html>