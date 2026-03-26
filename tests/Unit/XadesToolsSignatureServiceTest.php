<?php

use App\Services\XadesToolsSignatureService;

function makeToolsTestCerts(): array
{
    $privateKey = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);

    if (! $privateKey) {
        throw new RuntimeException('Unable to generate private key for test.');
    }

    $csr = openssl_csr_new([
        'commonName' => 'Test',
        'organizationalUnitName' => 'IT',
        'organizationName' => 'Example',
        'countryName' => 'EC',
    ], $privateKey, ['digest_alg' => 'sha256']);
    $certificate = openssl_csr_sign($csr, null, $privateKey, 365, ['digest_alg' => 'sha256']);

    openssl_x509_export($certificate, $certificatePem);
    openssl_pkey_export($privateKey, $privateKeyPem);

    return [
        'cert' => $certificatePem,
        'pkey' => $privateKeyPem,
    ];
}

it('builds a signature using the xades tools driver', function () {
    $certs = makeToolsTestCerts();
    $service = new XadesToolsSignatureService;

    $xml = '<?xml version="1.0" encoding="UTF-8"?><factura id="comprobante" version="2.1.0"><infoTributaria></infoTributaria></factura>';

    $signedXml = $service->sign($xml, $certs);

    $doc = new DOMDocument;
    $doc->loadXML($signedXml);

    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

    expect($xpath->evaluate('count(//ds:Signature)'))->toBeGreaterThan(0);
});
