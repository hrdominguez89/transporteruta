<?php

namespace App\Services;

use App\Models\Invoice;
use App\Services\WsaaService;
use SoapClient;
use SoapFault;
use Exception;

class WsfeService
{
    private WsaaService $wsaa;
    private SoapClient  $client;
    private int         $cuit;
    private string      $wsdl;

    // Fechas de servicio — ajustar desde el controlador según necesidad
    private string $fechaServDesde;
    private string $fechaServHasta;
    private string $fechaVencPago;

    public function __construct(WsaaService $wsaa)
    {
        $this->wsaa  = $wsaa;
        $this->cuit  = (int) config('afip.cuit');
        $this->wsdl  = config('afip.wsfe_wsdl');

        // Por defecto: primer y último día del mes actual
        $this->fechaServDesde = now()->startOfMonth()->format('Ymd');
        $this->fechaServHasta = now()->endOfMonth()->format('Ymd');
        $this->fechaVencPago  = now()->endOfMonth()->format('Ymd');
    }

    // Setters para las fechas de servicio
    public function setFechaServDesde(string $fecha): void { $this->fechaServDesde = $fecha; }
    public function setFechaServHasta(string $fecha): void { $this->fechaServHasta = $fecha; }
    public function setFechaVencPago(string $fecha): void  { $this->fechaVencPago  = $fecha; }

    private function getClient(): SoapClient
    {
        if (!isset($this->client)) {
            $this->client = new SoapClient($this->wsdl, [
                'soap_version' => SOAP_1_1,
                'exceptions'   => true,
                'trace'        => config('app.debug'),
            ]);
        }
        return $this->client;
    }

    private function getAuth(): array
    {
        $credentials = $this->wsaa->getCredentials();
        return [
            'Token' => $credentials['token'],
            'Sign'  => $credentials['sign'],
            'cuit'  => $this->cuit,
        ];
    }

    public function dummy(): array
    {
        try {
            $result = $this->getClient()->FEDummy();
            return [
                'appserver'  => $result->FEDummyResult->appserver,
                'dbserver'   => $result->FEDummyResult->dbserver,
                'authserver' => $result->FEDummyResult->authserver,
            ];
        } catch (SoapFault $e) {
            throw new Exception('Error SOAP en FEDummy: ' . $e->getMessage());
        }
    }

    public function ultimoComprobante(int $puntoVenta): int
    {
        try {
            $result = $this->getClient()->FERecuperaLastCMPRequest([
                'argAuth' => $this->getAuth(),
                'argTCMP' => [
                    'PtoVta'   => $puntoVenta,
                    'TipoCbte' => 11, // Factura C
                ],
            ]);

            $response = $result->FERecuperaLastCMPRequestResult;

            if ($response->RError->percode !== 0) {
                throw new Exception('Error WSFE: ' . $response->RError->perrmsg);
            }

            return (int) $response->cbte_nro;

        } catch (SoapFault $e) {
            throw new Exception('Error SOAP en ultimoComprobante: ' . $e->getMessage());
        }
    }

    public function autorizarComprobante(Invoice $invoice): array
    {
        $puntoVenta = $invoice->pointOfSale;
        $siguiente  = $this->ultimoComprobante($puntoVenta) + 1;

        $detalle = [
            'tipo_doc'             => 96,                              // DNI por defecto
            'nro_doc'              => $invoice->client->documento ?? 0,
            'tipo_cbte'            => 11,                              // Factura C
            'punto_vta'            => $puntoVenta,
            'cbt_desde'            => $siguiente,
            'cbt_hasta'            => $siguiente,
            'imp_total'            => round($invoice->totalWithIva, 2),
            'imp_tot_conc'         => 0,
            'imp_neto'             => round($invoice->total, 2),
            'impto_liq'            => round($invoice->iva, 2),
            'impto_liq_rni'        => 0,
            'imp_op_ex'            => 0,
            'fecha_cbte'           => now()->format('Ymd'),
            'fecha_serv_desde'     => $this->fechaServDesde,
            'fecha_serv_hasta'     => $this->fechaServHasta,
            'fecha_venc_pago'      => $this->fechaVencPago,
            'Cond_IVA_Receptor_Id' => 5,                              // Consumidor Final
        ];

        try {
            $result = $this->getClient()->FEAutRequest([
                'argAuth' => $this->getAuth(),
                'Fer'     => [
                    'Fecr' => [
                        'id'          => time(),
                        'cantidadreg' => 1,
                        'presta_serv' => 1,
                    ],
                    'Fedr' => [
                        'FEDetalleRequest' => $detalle,
                    ],
                ],
            ]);

            $response    = $result->FEAutRequestResult;
            $cabecera    = $response->FecResp;
            $detalleResp = $response->FedResp->FEDetalleResponse;

            if ($cabecera->resultado === 'R') {
                throw new Exception('Comprobante rechazado: ' . $cabecera->motivo);
            }

            return [
                'cae'       => $detalleResp->cae,
                'fecha_vto' => $detalleResp->fecha_vto,
                'nro_cbte'  => $detalleResp->cbt_desde,
                'resultado' => $detalleResp->resultado,
            ];

        } catch (SoapFault $e) {
            throw new Exception('Error SOAP en autorizarComprobante: ' . $e->getMessage());
        }
    }
}