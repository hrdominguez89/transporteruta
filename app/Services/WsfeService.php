<?php

namespace App\Services;

use App\Models\Invoice;
use App\Services\WsaaService;
use SoapClient;
use SoapFault;
use Exception;

class WsfeService
{
    private const CBTE_TIPO_FACTURA_A = 1;
    private const CONCEPTO_SERVICIOS  = 2;
    private const DOC_TIPO_CUIT       = 80;
    private const IVA_ID_21           = 5;
    private const COND_IVA_RECEPTOR_RI = 1;
    private const MONEDA_PESOS        = 'PES';

    private WsaaService $wsaa;
    private SoapClient  $client;
    private int         $cuit;
    private string      $wsdl;

    private string $fechaServDesde;
    private string $fechaServHasta;
    private string $fechaVencPago;

    public function __construct(WsaaService $wsaa)
    {
        $this->wsaa = $wsaa;
        $this->cuit = (int) config('afip.cuit');
        $this->wsdl = config('afip.wsfe_wsdl');

        $this->fechaServDesde = now()->startOfMonth()->format('Ymd');
        $this->fechaServHasta = now()->endOfMonth()->format('Ymd');
        $this->fechaVencPago  = now()->endOfMonth()->format('Ymd');
    }

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
            'Cuit'  => $this->cuit,
        ];
    }

    /**
     * SoapClient devuelve un objeto si hay 1 elemento y un array si hay varios.
     * Normalizamos siempre a array.
     */
    private function toArray($nodo): array
    {
        if ($nodo === null)   return [];
        if (is_array($nodo))  return $nodo;
        return [$nodo];
    }

    private function checkErrors($response): void
    {
        if (!isset($response->Errors)) {
            return;
        }

        $errores = $this->toArray($response->Errors->Err ?? null);

        if (empty($errores)) {
            return;
        }

        $mensajes = array_map(
            fn($e) => "[{$e->Code}] {$e->Msg}",
            $errores
        );

        throw new Exception('WSFEv1: ' . implode(' | ', $mensajes));
    }

    public function dummy(): array
    {
        try {
            $result = $this->getClient()->FEDummy();

            return [
                'appserver'  => $result->FEDummyResult->AppServer,
                'dbserver'   => $result->FEDummyResult->DbServer,
                'authserver' => $result->FEDummyResult->AuthServer,
            ];
        } catch (SoapFault $e) {
            throw new Exception('Error SOAP en FEDummy: ' . $e->getMessage());
        }
    }

    public function ultimoComprobante(int $puntoVenta, int $tipoCbte = self::CBTE_TIPO_FACTURA_A): int
    {
        try {
            $result = $this->getClient()->FECompUltimoAutorizado([
                'Auth'     => $this->getAuth(),
                'PtoVta'   => $puntoVenta,
                'CbteTipo' => $tipoCbte,
            ]);

            $response = $result->FECompUltimoAutorizadoResult;

            $this->checkErrors($response);

            return (int) $response->CbteNro;

        } catch (SoapFault $e) {
            throw new Exception('Error SOAP en ultimoComprobante: ' . $e->getMessage());
        }
    }

    public function autorizarComprobante(Invoice $invoice, ?int $puntoVenta = null): array
    {
        $puntoVenta = $puntoVenta ?? (int) $invoice->pointOfSale;
        $siguiente  = $this->ultimoComprobante($puntoVenta) + 1;

        // Importes: ImpTotal debe ser exactamente la suma de los componentes
        // $neto  = round((float) $invoice->total, 2);
        // $iva   = round((float) $invoice->iva, 2);
        // $total = round($neto + $iva, 2);
        // MOCK TEMPORAL — el desglose real de IVA se resuelve en el módulo de Invoice
        $neto  = 100000.00;
        $iva   = 21000.00;
        $total = 121000.00;
        $detalle = [
            'Concepto'                => self::CONCEPTO_SERVICIOS,
            'DocTipo'                 => self::DOC_TIPO_CUIT,
            'DocNro'                  => 20111111112,//$invoice->client->documento ?? 0,
            'CbteDesde'               => $siguiente,
            'CbteHasta'               => $siguiente,
            'CbteFch'                 => now()->format('Ymd'),
            'ImpTotal'                => $total,
            'ImpTotConc'              => 0,
            'ImpNeto'                 => $neto,
            'ImpOpEx'                 => 0,
            'ImpTrib'                 => 0,
            'ImpIVA'                  => $iva,
            'FchServDesde'            => $this->fechaServDesde,
            'FchServHasta'            => $this->fechaServHasta,
            'FchVtoPago'              => $this->fechaVencPago,
            'MonId'                   => self::MONEDA_PESOS,
            'MonCotiz'                => 1,
            'CondicionIVAReceptorId'  => self::COND_IVA_RECEPTOR_RI,
            'Iva' => [
                'AlicIva' => [
                    [
                        'Id'      => self::IVA_ID_21,
                        'BaseImp' => $neto,
                        'Importe' => $iva,
                    ],
                ],
            ],
        ];
        // dd([
        //     'neto'      => $neto,
        //     'iva'       => $iva,
        //     'iva_21'    => round($neto * 0.21, 2),
        //     'coincide'  => $iva == round($neto * 0.21, 2),
        // ]);
        try {
            $result = $this->getClient()->FECAESolicitar([
                'Auth'     => $this->getAuth(),
                'FeCAEReq' => [
                    'FeCabReq' => [
                        'CantReg'  => 1,
                        'PtoVta'   => $puntoVenta,
                        'CbteTipo' => self::CBTE_TIPO_FACTURA_A,
                    ],
                    'FeDetReq' => [
                        'FECAEDetRequest' => $detalle,
                    ],
                ],
            ]);

            $response = $result->FECAESolicitarResult;

            // 1° errores a nivel request
            $this->checkErrors($response);

            $cabecera    = $response->FeCabResp;
            // $detalleResp = $response->FeDetResp->FEDetResponse;
            $detalleResp = $this->toArray($response->FeDetResp->FECAEDetResponse)[0];
            // 2° rechazo a nivel comprobante
            if ($cabecera->Resultado === 'R') {
                // $obs = $this->toArray($detalleResp->Obs->Observaciones ?? null);
                $obs = $this->toArray($detalleResp->Observaciones->Obs ?? null);
                $motivos = array_map(
                    fn($o) => "[{$o->Code}] {$o->Msg}",
                    $obs
                );

                throw new Exception(
                    'Comprobante rechazado: ' . (empty($motivos) ? 'sin detalle' : implode(' | ', $motivos))
                );
            }

            return [
                'cae'       => $detalleResp->CAE,
                'fecha_vto' => $detalleResp->CAEFchVto,
                'nro_cbte'  => (int) $detalleResp->CbteDesde,
                'resultado' => $detalleResp->Resultado,
            ];

        } catch (SoapFault $e) {
            throw new Exception('Error SOAP en autorizarComprobante: ' . $e->getMessage());
        }
    }
}