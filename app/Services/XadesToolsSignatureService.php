<?php

namespace App\Services;

use DOMDocument;
use XadesTools\Factory\CertificateFactory;
use XadesTools\Signature;

class XadesToolsSignatureService
{
    public function sign(string $xmlContent, array $certs): string
    {
        $certificate = CertificateFactory::string($certs['pkey'], $certs['cert'], '');
        $signer = new Signature($certificate);

        $signatureXml = $signer->signXml($xmlContent);

        $signatureDocument = new DOMDocument('1.0', 'UTF-8');
        $signatureDocument->loadXML($signatureXml);

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadXML($xmlContent);

        $signatureNode = $document->importNode($signatureDocument->documentElement, true);
        $document->documentElement->appendChild($signatureNode);

        return $document->saveXML();
    }
}
