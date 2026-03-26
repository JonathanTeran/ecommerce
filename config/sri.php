<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SRI Environment
    |--------------------------------------------------------------------------
    |
    | Values: 1 (Pruebas), 2 (Producción)
    |
    */
    'environment' => env('SRI_ENVIRONMENT', 1),

    /*
    |--------------------------------------------------------------------------
    | Invoice XML Version
    |--------------------------------------------------------------------------
    */
    'invoice_version' => env('SRI_INVOICE_VERSION', '2.1.0'),

    /*
    |--------------------------------------------------------------------------
    | XSD Validation
    |--------------------------------------------------------------------------
    */
    'validate_xsd' => env('SRI_VALIDATE_XSD', true),
    'xsd_path' => env('SRI_XSD_PATH', storage_path('app/sri/xsd/factura_V2_1_0.xsd')),
    'validate_totals' => env('SRI_VALIDATE_TOTALS', true),

    /*
    |--------------------------------------------------------------------------
    | Authorization Retry
    |--------------------------------------------------------------------------
    */
    'authorization_retry_delay' => env('SRI_AUTH_RETRY_DELAY', 120),
    'authorization_retry_attempts' => env('SRI_AUTH_RETRY_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    */
    'ruc' => env('SRI_RUC', '0999999999001'), // Default Test RUC
    'company_name' => env('APP_NAME', 'Tienda Virtual'),
    'contabilidad' => 'NO', // OBLIGADO A LLEVAR CONTABILIDAD
    'address' => 'Guayaquil, Ecuador',

    /*
    |--------------------------------------------------------------------------
    | Digital Signature (.p12)
    |--------------------------------------------------------------------------
    */
    'signature_path' => storage_path('app/private/firma_electronica.p12'),
    'signature_password' => env('SRI_SIGNATURE_PASSWORD', ''),
    'xades_driver' => env('SRI_XADES_DRIVER', 'native'),
    'xades_digest' => env('SRI_XADES_DIGEST', 'sha1'),
    'xades_signature' => env('SRI_XADES_SIGNATURE', 'sha1'),
    'soap_raw_path' => env('SRI_SOAP_RAW_PATH', 'sri/soap'),

    /*
    |--------------------------------------------------------------------------
    | Web Services URLs
    |--------------------------------------------------------------------------
    */
    'urls' => [
        1 => [ // Pruebas
            'recepcion' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
            'autorizacion' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        ],
        2 => [ // Producción
            'recepcion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
            'autorizacion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Codes
    |--------------------------------------------------------------------------
    */
    'cod_doc' => '01', // Factura
    'estab' => '001',
    'pto_emi' => '001',
];
