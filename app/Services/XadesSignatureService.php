<?php

namespace App\Services;

use DOMDocument;
use Exception;
use Illuminate\Support\Str;

class XadesSignatureService
{
    /**
     * @param  string  $xmlContent  The XML content to sign
     * @param  array  $certs  Array with 'cert' (public key) and 'pkey' (private key)
     * @return string Signed XML
     */
    public function sign(string $xmlContent, array $certs): string
    {
        // 1. Load XML
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;

        // Suppress XML parsing errors
        libxml_use_internal_errors(true);
        if (! $doc->loadXML($xmlContent)) {
            throw new Exception('Error cargando el XML para firmar.');
        }
        libxml_clear_errors();

        // 2. Prepare Certificate and Private Key
        $certPem = $certs['cert'];
        $privateKeyPem = $certs['pkey'];
        $certClean = preg_replace('/-{5}(BEGIN|END) CERTIFICATE-{5}|[\n\r]/', '', $certPem);

        // 3. Define IDs and Namespaces
        $idBase = $this->generateSignatureId();
        $signatureId = 'Signature-'.$idBase;
        $signedInfoId = 'SignedInfo-'.$idBase;
        $signedPropertiesId = $signatureId.'-SignedProperties';
        $objectId = 'SignatureObject-'.$idBase;
        $certificateId = 'Certificate-'.$idBase;
        $documentRefId = 'DocumentRef-'.$idBase;
        $signedPropertiesRefId = 'SignedPropertiesRef-'.$idBase;
        $certificateRefId = 'CertificateRef-'.$idBase;
        $signatureValueId = 'SignatureValue-'.$idBase;

        $xmlnsDs = 'http://www.w3.org/2000/09/xmldsig#';
        $xmlnsXades = 'http://uri.etsi.org/01903/v1.3.2#';
        $digestAlgorithm = config('sri.xades_digest', 'sha1');
        $signatureAlgorithm = config('sri.xades_signature', 'sha1');

        $hashFunction = $digestAlgorithm === 'sha256' ? 'sha256' : 'sha1';
        $digestUri = $hashFunction === 'sha256'
            ? 'http://www.w3.org/2001/04/xmlenc#sha256'
            : 'http://www.w3.org/2000/09/xmldsig#sha1';
        $signatureUri = $signatureAlgorithm === 'sha256'
            ? 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256'
            : 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
        $opensslAlgo = $signatureAlgorithm === 'sha256' ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1;

        $signatureNode = $doc->createElementNS($xmlnsDs, 'ds:Signature');
        $signatureNode->setAttribute('Id', $signatureId);
        $signatureNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds', $xmlnsDs);
        $signatureNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:etsi', $xmlnsXades);
        $doc->documentElement->appendChild($signatureNode);

        $signedInfo = $doc->createElementNS($xmlnsDs, 'ds:SignedInfo');
        $signedInfo->setAttribute('Id', $signedInfoId);
        $signatureNode->appendChild($signedInfo);

        $canonAlgo = $doc->createElementNS($xmlnsDs, 'ds:CanonicalizationMethod');
        $canonAlgo->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $signedInfo->appendChild($canonAlgo);

        $sigAlgo = $doc->createElementNS($xmlnsDs, 'ds:SignatureMethod');
        $sigAlgo->setAttribute('Algorithm', $signatureUri);
        $signedInfo->appendChild($sigAlgo);

        $ref2 = $doc->createElementNS($xmlnsDs, 'ds:Reference');
        $ref2->setAttribute('Id', $signedPropertiesRefId);
        $ref2->setAttribute('Type', 'http://uri.etsi.org/01903#SignedProperties');
        $ref2->setAttribute('URI', '#'.$signedPropertiesId);
        $signedInfo->appendChild($ref2);

        $digMethod2 = $doc->createElementNS($xmlnsDs, 'ds:DigestMethod');
        $digMethod2->setAttribute('Algorithm', $digestUri);
        $ref2->appendChild($digMethod2);

        $digVal2 = $doc->createElementNS($xmlnsDs, 'ds:DigestValue', '');
        $ref2->appendChild($digVal2);

        $ref3 = $doc->createElementNS($xmlnsDs, 'ds:Reference');
        $ref3->setAttribute('Id', $certificateRefId);
        $ref3->setAttribute('URI', '#'.$certificateId);
        $signedInfo->appendChild($ref3);

        $digMethod3 = $doc->createElementNS($xmlnsDs, 'ds:DigestMethod');
        $digMethod3->setAttribute('Algorithm', $digestUri);
        $ref3->appendChild($digMethod3);

        $digVal3 = $doc->createElementNS($xmlnsDs, 'ds:DigestValue', '');
        $ref3->appendChild($digVal3);

        $ref1 = $doc->createElementNS($xmlnsDs, 'ds:Reference');
        $ref1->setAttribute('Id', $documentRefId);
        $ref1->setAttribute('URI', '#comprobante');
        $signedInfo->appendChild($ref1);

        $transforms = $doc->createElementNS($xmlnsDs, 'ds:Transforms');
        $ref1->appendChild($transforms);

        $transform = $doc->createElementNS($xmlnsDs, 'ds:Transform');
        $transform->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
        $transforms->appendChild($transform);

        $digMethod1 = $doc->createElementNS($xmlnsDs, 'ds:DigestMethod');
        $digMethod1->setAttribute('Algorithm', $digestUri);
        $ref1->appendChild($digMethod1);

        $digVal1 = $doc->createElementNS($xmlnsDs, 'ds:DigestValue', '');
        $ref1->appendChild($digVal1);

        $sigValNode = $doc->createElementNS($xmlnsDs, 'ds:SignatureValue', '');
        $sigValNode->setAttribute('Id', $signatureValueId);
        $signatureNode->appendChild($sigValNode);

        $keyInfoNode = $doc->createElementNS($xmlnsDs, 'ds:KeyInfo');
        $keyInfoNode->setAttribute('Id', $certificateId);
        $signatureNode->appendChild($keyInfoNode);

        $x509DataNode = $doc->createElementNS($xmlnsDs, 'ds:X509Data');
        $keyInfoNode->appendChild($x509DataNode);

        $x509CertNode = $doc->createElementNS($xmlnsDs, 'ds:X509Certificate', $certClean);
        $x509DataNode->appendChild($x509CertNode);

        $publicKey = openssl_pkey_get_public($certs['cert']);
        $publicKeyDetails = $publicKey ? openssl_pkey_get_details($publicKey) : null;
        if ($publicKeyDetails && isset($publicKeyDetails['rsa'])) {
            $keyValue = $doc->createElementNS($xmlnsDs, 'ds:KeyValue');
            $rsaKeyValue = $doc->createElementNS($xmlnsDs, 'ds:RSAKeyValue');

            $modulus = base64_encode($publicKeyDetails['rsa']['n']);
            $exponent = base64_encode($publicKeyDetails['rsa']['e']);

            $rsaKeyValue->appendChild($doc->createElementNS($xmlnsDs, 'ds:Modulus', $modulus));
            $rsaKeyValue->appendChild($doc->createElementNS($xmlnsDs, 'ds:Exponent', $exponent));
            $keyValue->appendChild($rsaKeyValue);
            $keyInfoNode->appendChild($keyValue);
        }

        $objectNode = $doc->createElementNS($xmlnsDs, 'ds:Object');
        $objectNode->setAttribute('Id', $objectId);
        $signatureNode->appendChild($objectNode);

        $qualifyingProps = $doc->createElementNS($xmlnsXades, 'etsi:QualifyingProperties');
        $qualifyingProps->setAttribute('Target', '#'.$signatureId);
        $objectNode->appendChild($qualifyingProps);

        $signedProps = $doc->createElementNS($xmlnsXades, 'etsi:SignedProperties');
        $signedProps->setAttribute('Id', $signedPropertiesId);
        $qualifyingProps->appendChild($signedProps);

        // A.1 SignedSignatureProperties
        $signedSigProps = $doc->createElementNS($xmlnsXades, 'etsi:SignedSignatureProperties');
        $signedProps->appendChild($signedSigProps);

        $signingTime = $doc->createElementNS($xmlnsXades, 'etsi:SigningTime', date('c'));
        $signedSigProps->appendChild($signingTime);

        $signingCert = $doc->createElementNS($xmlnsXades, 'etsi:SigningCertificate');
        $signedSigProps->appendChild($signingCert);

        $certEl = $doc->createElementNS($xmlnsXades, 'etsi:Cert');
        $signingCert->appendChild($certEl);

        $certDigest = $doc->createElementNS($xmlnsXades, 'etsi:CertDigest');
        $certEl->appendChild($certDigest);

        $digestMethodCert = $doc->createElementNS($xmlnsDs, 'ds:DigestMethod');
        $digestMethodCert->setAttribute('Algorithm', $digestUri);
        $certDigest->appendChild($digestMethodCert);

        $certHash = $this->hashDigest($hashFunction, base64_decode($certClean));
        $digestValueCert = $doc->createElementNS($xmlnsDs, 'ds:DigestValue', $certHash);
        $certDigest->appendChild($digestValueCert);

        $issuerSerial = $doc->createElementNS($xmlnsXades, 'etsi:IssuerSerial');
        $certEl->appendChild($issuerSerial);

        // Issuer Name & Serial
        $certInfo = openssl_x509_parse($certs['cert']);
        $issuerStr = '';
        if (isset($certInfo['issuer'])) {
            $issuer = $certInfo['issuer'];
            $parts = [];
            if (array_key_exists('organizationIdentifier', $issuer)) {
                $orgId = $issuer['organizationIdentifier'];
                if (is_array($orgId)) {
                    $orgId = $orgId[0];
                }
                $parts[] = '2.5.4.97='.$this->encodeOidValue((string) $orgId);
            }

            $orderedKeys = ['CN', 'OU', 'O', 'L', 'C'];
            foreach ($orderedKeys as $key) {
                if (! array_key_exists($key, $issuer)) {
                    continue;
                }
                $value = $issuer[$key];
                if (is_array($value)) {
                    $value = $value[0];
                }
                $parts[] = $key.'='.$value;
            }
            $issuerStr = implode(',', $parts);
        }

        $x509IssuerName = $doc->createElementNS($xmlnsDs, 'ds:X509IssuerName', $issuerStr);
        $issuerSerial->appendChild($x509IssuerName);

        $serialNumber = $certInfo['serialNumber'] ?? $certInfo['serialNumberHex'];
        $x509SerialNumber = $doc->createElementNS($xmlnsDs, 'ds:X509SerialNumber', $serialNumber);
        $issuerSerial->appendChild($x509SerialNumber);

        // A.2 SignedDataObjectProperties
        $signedDOProps = $doc->createElementNS($xmlnsXades, 'etsi:SignedDataObjectProperties');
        $signedProps->appendChild($signedDOProps);

        $dataObjectFormat = $doc->createElementNS($xmlnsXades, 'etsi:DataObjectFormat');
        $dataObjectFormat->setAttribute('ObjectReference', '#'.$documentRefId);
        $signedDOProps->appendChild($dataObjectFormat);

        $description = $doc->createElementNS($xmlnsXades, 'etsi:Description', 'Firma digital');
        $dataObjectFormat->appendChild($description);

        $mimeType = $doc->createElementNS($xmlnsXades, 'etsi:MimeType', 'text/xml');
        $dataObjectFormat->appendChild($mimeType);

        $encoding = $doc->createElementNS($xmlnsXades, 'etsi:Encoding', 'UTF-8');
        $dataObjectFormat->appendChild($encoding);
        $c14nSignedProps = $signedProps->C14N(false, false);
        $hashSignedProps = $this->hashDigest($hashFunction, $c14nSignedProps);
        $digVal2->nodeValue = $hashSignedProps;

        $c14nKeyInfo = $keyInfoNode->C14N(false, false);
        $hashKeyInfo = $this->hashDigest($hashFunction, $c14nKeyInfo);
        $digVal3->nodeValue = $hashKeyInfo;

        $c14nComprobante = $this->canonicalizeComprobante($doc, $xmlnsDs);
        $hashComprobante = $this->hashDigest($hashFunction, $c14nComprobante);
        $digVal1->nodeValue = $hashComprobante;

        $c14nSignedInfo = $signedInfo->C14N(false, false);
        $signaturePayload = '';
        if (! openssl_sign($c14nSignedInfo, $signaturePayload, $privateKeyPem, $opensslAlgo)) {
            throw new Exception('Error al generar la firma RSA: '.openssl_error_string());
        }
        $sigValNode->nodeValue = base64_encode($signaturePayload);

        return $doc->saveXML();
    }

    private function generateSignatureId(): string
    {
        return (string) Str::uuid();
    }

    private function canonicalizeComprobante(DOMDocument $document, string $namespace): string
    {
        $clone = $document->cloneNode(true);
        $xpath = new \DOMXPath($clone);
        $xpath->registerNamespace('ds', $namespace);

        foreach ($xpath->query('//ds:Signature') as $signature) {
            $signature->parentNode?->removeChild($signature);
        }

        return $clone->documentElement->C14N(false, false);
    }

    private function hashDigest(string $algorithm, string $data): string
    {
        return base64_encode(hash($algorithm, $data, true));
    }

    private function encodeOidValue(string $value): string
    {
        $value = (string) $value;
        $length = strlen($value);
        if ($length < 128) {
            $lenHex = sprintf('%02x', $length);
        } else {
            $lenHex = '81'.sprintf('%02x', $length);
        }

        return '#0c'.$lenHex.bin2hex($value);
    }
}
