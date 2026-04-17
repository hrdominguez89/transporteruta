<?php

return [
    'cuit'      => env('ARCA_CUIT'),
    'cert_path' => env('ARCA_CERT_PATH'),
    'key_path'  => env('ARCA_KEY_PATH'),
    'wsaa_wsdl' => env('ARCA_WSAA_WSDL'),
    'wsfe_wsdl' => env('ARCA_WSFE_WSDL'),
    'env'       => env('ARCA_ENV', 'testing'),
];