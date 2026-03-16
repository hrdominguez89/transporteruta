<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use SoapClient;
use SoapFault;
use Exception;

class WsaaService
{
    private string $certPath;
    private string $keyPath;
    private string $wsdl;
    private string $service = 'wsfe';
    private int $cuit;

    public function __construct()
    {
        $this->certPath = base_path(config('afip.cert_path'));
        $this->keyPath  = base_path(config('afip.key_path'));
        $this->wsdl     = config('afip.wsaa_wsdl');
        // $this->service  = 'wsfe';
        $this->cuit     = (int) config('afip.cuit');
    }

    public function getCredentials(): array
    {
        $cacheKey = "afip_wsaa_credentials_{$this->cuit}";

        return Cache::remember($cacheKey, now()->addHours(11), function () {
            $tra      = $this->buildTra();
            $cms      = $this->signTra($tra);
            $response = $this->callWsaa($cms);
            return $this->parseResponse($response);
        });
    }

    private function buildTra(): string
    {
        $uniqueId        = time();
        $generationTime  = date('c', strtotime('-10 minutes'));
        $expirationTime  = date('c', strtotime('+10 minutes'));

        return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <loginTicketRequest version="1.0">
          <header>
            <uniqueId>{$uniqueId}</uniqueId>
            <generationTime>{$generationTime}</generationTime>
            <expirationTime>{$expirationTime}</expirationTime>
          </header>
          <service>{$this->service}</service>
        </loginTicketRequest>
        XML;
    }

    private function signTra(string $tra): string
    {
        $traFile  = tempnam(sys_get_temp_dir(), 'tra');
        $cmsFile  = tempnam(sys_get_temp_dir(), 'cms');

        file_put_contents($traFile, $tra);

        $signed = openssl_pkcs7_sign(
            $traFile,
            $cmsFile,
            'file://' . $this->certPath,
            ['file://' . $this->keyPath, ''],
            [],
            PKCS7_BINARY | PKCS7_NOCERTS
        );

        if (!$signed) {
            unlink($traFile);
            unlink($cmsFile);
            throw new Exception('Error al firmar el TRA: ' . openssl_error_string());
        }

        $cms = file_get_contents($cmsFile);

        unlink($traFile);
        unlink($cmsFile);

        // El SoapClient necesita solo el bloque base64, sin headers MIME
        $parts = explode("\n\n", $cms);
        return trim(end($parts));
    }

    private function callWsaa(string $cms): string
    {
        try {
            $client = new SoapClient($this->wsdl, [
                'soap_version' => SOAP_1_1,
                'exceptions'   => true,
                'trace'        => config('app.debug'),
            ]);

            $result = $client->loginCms(['in0' => $cms]);

            return $result->loginCmsReturn;

        } catch (SoapFault $e) {
            throw new Exception('Error SOAP al llamar al WSAA: ' . $e->getMessage());
        }
    }

    private function parseResponse(string $xml): array
    {
        $ta = simplexml_load_string($xml);

        if ($ta === false) {
            throw new Exception('No se pudo parsear la respuesta del WSAA.');
        }

        return [
            'token' => (string) $ta->credentials->token,
            'sign'  => (string) $ta->credentials->sign,
        ];
    }
}