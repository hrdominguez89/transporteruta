{{-- resources/views/travelCertificate/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancias</title>

    {{-- ==================== REFACTORIZACIÓN (compactación 1 carilla) ====================
         - Márgenes de página más chicos (@page)
         - Fuentes y line-height más compactos
         - Menos padding en el contenedor
    ------------------------------------------------------------------------------- --}}
    <style>
        @page {
            margin: 18mm 12mm; /* top-bottom / left-right */
        }

        html, body {
            font-size: 11.5px;
            line-height: 1.15;
        }

        .container {
            border: 1px solid #000;
            padding: 32px; /* antes 65px */
        }

        h5, p {
            margin: 4px 0;
            line-height: 1.15;
        }

        .kv {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            line-height: 1.5;
        }

        .header-img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 6px 0 10px;
        }

        /* ============== REFACTORIZACIÓN (tabla conceptual compacta) ============== */
        .conceptos-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 1rem;
            line-height: 1.5;  /* MUY importante para ganar renglones */
        }
        .conceptos-table th, .conceptos-table td {
            padding: 2px 4px;   /* menos padding vertical */
            border-bottom: 1px solid #ccc;
            vertical-align: top; /* evita “estirar” filas */
        }

        /* Totales compactos */
        .totales p {
            margin: 2px 0;
            line-height: 1.5;
            font-size: 1rem;
        }
    </style>
    {{-- ============================================================================ --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
          integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N"
          crossorigin="anonymous">
</head>

<body>
<div class="container text-center">
    <div class="col-12">
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($travelCertificate->date)->format('d/m/Y') }}</p>
        <h5>CONSTANCIA DE VIAJE N° {{ number_format($travelCertificate->number, 0, ',', '.') }}</h5>
    </div>

    <div class="row">
        <img class="col-7 header-img"
             src="https://media.licdn.com/dms/image/C4D1BAQF9AP8K9M-0WQ/company-background_10000/0/1625358131993/transportes_ruta_s_r_l_cover?e=2147483647&v=beta&t=DMcRvoePh7phfXc3qOGVvqPwkBOIDx37opmL1OcJizM">
    </div>

    <div class="col-12 table-bordered text-left mt-3 mb-3 p-2">
        <p class="kv"><strong>Cliente:</strong> <span>{{ $travelCertificate->client->name }}</span></p>
        <p class="kv"><strong>Chofer:</strong> <span>{{ $travelCertificate->driver->name }}</span></p>
        <p class="kv"><strong>Vehículo:</strong> <span>{{ $travelCertificate->driver?->vehicle?->name }}</span></p>
        <p class="kv"><strong>Hora de Salida:</strong> <span></span></p>
        <p class="kv"><strong>Hora de Llegada:</strong> <span></span></p>
    </div>

    <div class="col-12 table-bordered text-left p-2">
        <p><strong>CONCEPTOS:</strong></p>

        {{-- ================= REFACTORIZACIÓN (PDF Conceptos) =================
             Para que el ADICIONAL imprima el monto real (% * FIJO) aunque price=0 en BD,
             usamos $travelItem->display_price (accessor en el modelo TravelItem).
             Además mostramos los conceptos en una tabla compacta para evitar 2da hoja.
        ------------------------------------------------------------------- --}}
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
                <tr>
                    <td>{{ $travelItem->type }}</td>
                    <td>
                        {{-- Si querés mostrar el % al lado del adicional (opcional) --}}
                        @if($travelItem->type === 'ADICIONAL' && !is_null($travelItem->percent))
                            ({{ (float)$travelItem->percent }}%)
                        @endif
                        {{ $travelItem->description }}
                    </td>
                    <td style="text-align:right;">$&nbsp;{{ number_format($travelItem->display_price, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{-- ================= /REFACTORIZACIÓN (PDF Conceptos) ================= --}}
    </div>

    {{-- =====================  REFACTORIZACIÓN (PDF Totales)  =====================
       - Etiquetas pedidas: IMPORTE NETO, PEAJES, IVA y TOTAL.
       - Usamos cálculos del modelo (total_calculado / iva_calculado) para que
         ADICIONALES (% sobre FIJO) y DESCUENTOS impacten sin depender de DB.
       - TOTAL = IMPORTE NETO + IVA + PEAJES (según requerimiento).
       --------------------------------------------------------------------------- --}}
    @php
        // total_calculado = suma de ítems (incluye peajes) calculada dinámicamente
        $subtotalCalculado = $travelCertificate->total_calculado ?? ($travelCertificate->total ?? 0);
        $ivaCalculado      = $travelCertificate->iva_calculado   ?? ($travelCertificate->iva   ?? 0);
        $peajes            = $totalTolls ?? 0;

        // Importe Neto = subtotal sin IVA NI PEAJES (base imponible)
        $importeNeto = max(0, $subtotalCalculado - $peajes);

        // Total final = Neto + IVA + Peajes
        $totalFinal  = $importeNeto + $ivaCalculado + $peajes;
    @endphp

    <div class="col-12 table-bordered text-left mt-2 p-2 totales">
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
    {{-- ===================  /REFACTORIZACIÓN (PDF Totales)  ===================== --}}
</div>
</body>
</html>


