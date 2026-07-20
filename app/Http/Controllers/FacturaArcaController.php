<?php

namespace App\Http\Controllers;

use App\Models\ArcaFacturas;
use App\Models\Invoice;
use App\Services\WsfeService;
use Illuminate\Http\Request;
use Exception;

class FacturaArcaController extends Controller
{
    private WsfeService $wsfe;

    public function __construct(WsfeService $wsfe)
    {
        $this->wsfe = $wsfe;
    }

    public function index()
    {
        $facturasArca = ArcaFacturas::with('invoice.client')->latest()->get();

        // Solo invoices que todavía no fueron facturadas en ARCA
        $invoices = Invoice::with('client')
            ->whereDoesntHave('arcaFactura')
            ->get();

        return view('arca.index', compact('facturasArca', 'invoices'));
    }

    public function show($id)
    {
        $facturaArca = ArcaFacturas::with('invoice.client')->findOrFail($id);

        return view('arca.show', compact('facturaArca'));
    }

    public function generar(Request $request)
    {
        $validated = $request->validate([
            'invoiceId' => 'required|exists:invoices,id',
            'punto_vta' => 'required|integer|min:1',
        ]);

        $invoice = Invoice::with('client')->findOrFail($validated['invoiceId']);

        try {
            $respuesta = $this->wsfe->autorizarComprobante($invoice, $validated['punto_vta']);

            $facturaArca = ArcaFacturas::create([
                'invoiceId'  => $invoice->id,
                'cae'        => $respuesta['cae'],
                'fecha_vto'  => $respuesta['fecha_vto'],
                'fecha_cbte' => now()->format('Ymd'),
                'punto_vta'  => $validated['punto_vta'],
                'tipo_cbte'  => 1,
                'cbt_desde'  => $respuesta['nro_cbte'],
                'imp_total'  => $invoice->totalWithIva,
                'resultado'  => $respuesta['resultado'],
            ]);

        } catch (Exception $e) {
            return redirect()
                ->route('facturasArca')
                ->with('flag', false)
                ->with('message', 'Error al facturar: ' . $e->getMessage());
        }

        return redirect()
            ->route('showFacturaArca', $facturaArca->id)
            ->with('flag', true)
            ->with('message', 'Comprobante autorizado. CAE: ' . $facturaArca->cae);
    }
}