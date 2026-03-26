<?php

use App\Services\XadesSignatureService;

function buildTestCertificatePair(): array
{
    $privateKey = openssl_pkey_new([
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'private_key_bits' => 2048,
    ]);

    $csr = openssl_csr_new([
        'commonName' => 'Test Certificate',
    ], $privateKey, [
        'digest_alg' => 'sha256',
    ]);

    $certificate = openssl_csr_sign($csr, null, $privateKey, 1, [
        'digest_alg' => 'sha256',
    ]);

    openssl_x509_export($certificate, $certOut);
    openssl_pkey_export($privateKey, $pkeyOut);

    return [
        'cert' => $certOut,
        'pkey' => $pkeyOut,
    ];
}

it('canonicalizes the document reference using the root element', function () {
    $certs = buildTestCertificatePair();

    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<factura id="comprobante" version="2.1.0">
  <infoTributaria>
    <ambiente>1</ambiente>
    <tipoEmision>1</tipoEmision>
    <razonSocial>Test</razonSocial>
    <nombreComercial>Test</nombreComercial>
    <ruc>9999999999999</ruc>
    <claveAcceso>123</claveAcceso>
    <codDoc>01</codDoc>
    <estab>001</estab>
    <ptoEmi>001</ptoEmi>
    <secuencial>000000001</secuencial>
    <dirMatriz>Test</dirMatriz>
  </infoTributaria>
</factura>
XML;

    $service = new XadesSignatureService();
    $signedXml = $service->sign($xml, $certs);

    $document = new DOMDocument('1.0', 'UTF-8');
    $document->preserveWhiteSpace = false;
    $document->loadXML($signedXml);

    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

    $reference = $xpath->query("//ds:Reference[@URI='#comprobante']")->item(0);
    $digestValue = trim($xpath->query('./ds:DigestValue', $reference)->item(0)->textContent);

    /** @var DOMDocument $clone */
    $clone = $document->cloneNode(true);
    $xpathClone = new DOMXPath($clone);
    $xpathClone->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

    foreach ($xpathClone->query('//ds:Signature') as $signature) {
        $signature->parentNode?->removeChild($signature);
    }

    $canonicalized = $clone->documentElement->C14N(false, false);
    $calculated = base64_encode(hash('sha1', $canonicalized, true));

    expect($digestValue)->toBe($calculated);
});
