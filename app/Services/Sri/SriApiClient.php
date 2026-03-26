<?php

namespace App\Services\Sri;

use App\Jobs\CheckSriAuthorizationStatus;
use App\Models\Order;
use App\Services\SriInvoiceTotalsValidator;
use App\Services\SriXmlValidator;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SriApiClient
{
    public function __construct(
        protected array $urls,
        protected string $signaturePath,
        protected string $signaturePassword,
    ) {}

    /**
     * Send to SRI Recepcion
     */
    public function sendToRecepcion(string $xmlSigned): array
    {
        $accessKey = $this->extractAccessKey($xmlSigned);

        if (config('sri.validate_totals', true)) {
            $totalsValidator = new SriInvoiceTotalsValidator;
            $totalsValidation = $totalsValidator->validate($xmlSigned);

            if (! $totalsValidation['valid']) {
                Log::warning('SRI totals validation failed.', [
                    'access_key' => $accessKey,
                    'message' => $totalsValidation['message'],
                    'errors' => $totalsValidation['errors'],
                ]);

                return [
                    'status' => 'DATA_ERROR',
                    'source' => 'offline',
                    'validation' => 'totals',
                    'message' => $totalsValidation['message'],
                    'errors' => $totalsValidation['errors'],
                ];
            }
        }

        if (config('sri.validate_xsd', true)) {
            $xsdPath = (string) config('sri.xsd_path', '');
            $validator = new SriXmlValidator;
            $validation = $validator->validate($xmlSigned, $xsdPath);

            if (! $validation['valid']) {
                Log::warning('SRI XSD validation failed.', [
                    'access_key' => $accessKey,
                    'message' => $validation['message'],
                    'errors' => $validation['errors'],
                ]);

                return [
                    'status' => 'XSD_ERROR',
                    'source' => 'offline',
                    'validation' => 'xsd',
                    'message' => $validation['message'],
                    'errors' => $validation['errors'],
                ];
            }
        }

        $client = null;

        try {
            // Extract access key for logging without exposing full XML
            preg_match('/<claveAcceso>(\d+)<\/claveAcceso>/', $xmlSigned, $keyMatch);
            Log::info('SRI: Sending signed document to reception', ['access_key' => $keyMatch[1] ?? 'unknown']);
            $xmlPayload = $xmlSigned;

            $opts = [
                'ssl' => [
                    'verify_peer' => app()->isProduction(),
                    'verify_peer_name' => app()->isProduction(),
                ],
            ];

            $context = stream_context_create($opts);

            $client = $this->makeSoapClient($this->urls['recepcion'], [
                'stream_context' => $context,
                'trace' => true,
                'connection_timeout' => 30,
                'default_socket_timeout' => 30,
            ]);

            $result = $client->validarComprobante(['xml' => $xmlPayload]);

            $this->persistSoapRaw(
                'recepcion',
                $client->__getLastRequest(),
                $client->__getLastResponse(),
                $accessKey
            );

            Log::info('SRI Recepcion Response received', ['status' => $result->estado ?? 'unknown']);

            // Parse response - handle both formats
            $response = $result->RespuestaRecepcionComprobante ?? $result;

            // Check for top-level estado (standard response)
            $estado = $response->estado ?? null;

            // Extract message from comprobantes if available
            $mensaje = 'Procesado';
            $comprobante = $response->comprobantes->comprobante ?? null;

            if ($comprobante) {
                $mensajeObj = $comprobante->mensajes->mensaje ?? null;
                if ($mensajeObj) {
                    // Handle both single message and array of messages
                    if (is_array($mensajeObj)) {
                        $mensajeObj = $mensajeObj[0];
                    }
                    $mensaje = $mensajeObj->mensaje ?? 'Sin mensaje';
                    $infoAdicional = $mensajeObj->informacionAdicional ?? '';
                    if ($infoAdicional) {
                        $mensaje .= ' - '.$infoAdicional;
                    }

                    // If mensaje has tipo ERROR, set estado accordingly
                    if (($mensajeObj->tipo ?? '') === 'ERROR' && ! $estado) {
                        $estado = 'DEVUELTA';
                    }
                }
            }

            if ($estado === 'RECIBIDA') {
                return [
                    'status' => 'RECIBIDA',
                    'source' => 'sri',
                    'message' => 'Comprobante recibido correctamente',
                    'data' => $result,
                ];
            }

            return [
                'status' => $estado ?? 'DEVUELTA',
                'source' => 'sri',
                'message' => $mensaje,
                'data' => $result,
            ];

        } catch (Exception $e) {
            if ($client instanceof \SoapClient) {
                $this->persistSoapRaw(
                    'recepcion',
                    $client->__getLastRequest(),
                    $client->__getLastResponse(),
                    $accessKey
                );
            }

            Log::error('SRI Recepcion Error: '.$e->getMessage());

            return [
                'status' => 'ERROR',
                'source' => 'system',
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function makeSoapClient(string $wsdl, array $options): \SoapClient
    {
        return new \SoapClient($wsdl, $options);
    }

    /**
     * Schedule authorization check job
     */
    public function scheduleAuthorizationCheck(Order $order, int $attempt = 1): void
    {
        $delaySeconds = (int) config('sri.authorization_retry_delay', 120);

        CheckSriAuthorizationStatus::dispatch($order->id, $attempt)
            ->delay(now()->addSeconds($delaySeconds));
    }

    /**
     * Determine if authorization should be attempted based on recepcion response
     */
    public function shouldAttemptAuthorization(array $recepcionResponse): bool
    {
        $status = $recepcionResponse['status'] ?? null;
        $message = $recepcionResponse['message'] ?? '';

        if ($status === 'RECIBIDA') {
            return true;
        }

        if (! is_string($message)) {
            return false;
        }

        return $status === 'DEVUELTA' && stripos($message, 'EN PROCESAMIENTO') !== false;
    }

    /**
     * Check authorization status for a given access key
     */
    public function authorize(string $accessKey): array
    {
        $client = null;

        try {
            $opts = [
                'ssl' => [
                    'verify_peer' => app()->isProduction(),
                    'verify_peer_name' => app()->isProduction(),
                ],
            ];

            $context = stream_context_create($opts);

            $client = $this->makeSoapClient($this->urls['autorizacion'], [
                'stream_context' => $context,
                'trace' => true,
                'connection_timeout' => 30,
                'default_socket_timeout' => 30,
            ]);

            $result = $client->autorizacionComprobante(['claveAccesoComprobante' => $accessKey]);

            $this->persistSoapRaw(
                'autorizacion',
                $client->__getLastRequest(),
                $client->__getLastResponse(),
                $accessKey
            );

            if (isset($result->RespuestaAutorizacionComprobante->autorizaciones->autorizacion)) {
                $auth = $result->RespuestaAutorizacionComprobante->autorizaciones->autorizacion;

                // Note: Authorization is often an array if multiple attempts
                if (is_array($auth)) {
                    $auth = $auth[0];
                }

                $message = $auth->mensajes->mensaje ?? null;
                if (is_array($message)) {
                    $message = $message[0] ?? null;
                }
                $detail = $message->mensaje ?? 'Procesado';
                $infoAdicional = $message->informacionAdicional ?? '';
                if ($infoAdicional) {
                    $detail .= ' - '.$infoAdicional;
                }

                $authorizedXml = $this->normalizeAuthorizedXml($auth->comprobante ?? null);

                return [
                    'status' => $auth->estado, // AUTORIZADO, NO AUTORIZADO
                    'source' => 'sri',
                    'authorization_number' => $auth->numeroAutorizacion ?? null,
                    'date' => $auth->fechaAutorizacion ?? null,
                    'xml_url' => '', // You can save the authorized XML here
                    'xml' => $authorizedXml,
                    'message' => $detail,
                ];
            }

            return [
                'status' => 'PENDING',
                'source' => 'sri',
                'message' => 'No se encontro autorizacion',
                'xml' => null,
            ];

        } catch (Exception $e) {
            if ($client instanceof \SoapClient) {
                $this->persistSoapRaw(
                    'autorizacion',
                    $client->__getLastRequest(),
                    $client->__getLastResponse(),
                    $accessKey
                );
            }

            Log::error('SRI Autorizacion Error: '.$e->getMessage());

            return [
                'status' => 'ERROR',
                'source' => 'system',
                'message' => $e->getMessage(),
                'xml' => null,
            ];
        }
    }

    /**
     * Store the authorized XML for an order
     */
    public function storeAuthorizedXml(Order $order, string $xml): string
    {
        $accessKey = $order->sri_access_key
            ?? $this->extractAccessKey($xml)
            ?? $order->order_number;

        $path = 'xml/facturas/autorizadas/'.$accessKey.'.xml';
        Storage::disk('local')->put($path, $xml);

        return $path;
    }

    /**
     * Sign XML using OpenSSL and PKCS12
     */
    public function signXml(string $xmlContent): string
    {
        if (! file_exists($this->signaturePath)) {
            Log::error('SRI Error: Signature file not found at '.$this->signaturePath);
            throw new Exception('Archivo de firma no encontrado en la ruta especificada.');
        }

        $pkcs12 = file_get_contents($this->signaturePath);
        $certs = [];

        if (! openssl_pkcs12_read($pkcs12, $certs, $this->signaturePassword)) {
            Log::warning('SRI: Primary PKCS12 read failed, attempting CLI fallback due to OpenSSL 3.x legacy issues.');

            try {
                $certs = $this->readPkcs12ViaCli($this->signaturePath, $this->signaturePassword);
            } catch (Exception $e) {
                $error = '';
                while ($msg = openssl_error_string()) {
                    $error .= $msg.' ';
                }
                Log::error("SRI Error: OpenSSL PKCS12 Read Failed. Path: {$this->signaturePath}. Error: ".$error.' Fallback: '.$e->getMessage());
                throw new Exception('Error leyendo archivo .p12: '.$error.' | Intento Legacy: '.$e->getMessage());
            }
        }

        $certData = openssl_x509_read($certs['cert']);
        $privateKey = openssl_pkey_get_private($certs['pkey']);
        $publicKey = openssl_pkey_get_details(openssl_pkey_get_public($certData));

        $driver = config('sri.xades_driver', 'native');
        $signer = $driver === 'xades-tools'
            ? new \App\Services\XadesToolsSignatureService
            : new \App\Services\XadesSignatureService;

        try {
            return $signer->sign($xmlContent, $certs);
        } catch (Exception $e) {
            Log::error('SRI Error: XAdES Signing Failed. '.$e->getMessage());
            throw new Exception('Error generando firma XAdES: '.$e->getMessage());
        }
    }

    /**
     * Validate and modernize P12 certificate
     */
    public function validateAndModernizeP12(string $path, string $password): array
    {
        if (! file_exists($path)) {
            throw new Exception("El archivo no existe en: $path");
        }

        $pkcs12 = file_get_contents($path);
        if (! $pkcs12) {
            throw new Exception('No se puede leer el contenido del archivo.');
        }

        $certs = [];

        // 1. Try standard read (Modern format)
        if (openssl_pkcs12_read($pkcs12, $certs, $password)) {
            return $this->extractDatesFromCerts($certs);
        }

        // 2. Standard read failed. Try Legacy repair via CLI.
        try {
            $certs = $this->readPkcs12ViaCli($path, $password);

            // If success, it means it IS a valid cert but in Legacy format.
            $this->modernizeP12File($path, $certs['cert'], $certs['pkey'], $password);

            return $this->extractDatesFromCerts($certs);
        } catch (Exception $e) {
            throw new Exception('Error validando firma: '.$e->getMessage());
        }
    }

    protected function extractAccessKey(string $xml): ?string
    {
        if (preg_match('/<claveAcceso>(\d+)<\/claveAcceso>/', $xml, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    protected function normalizeAuthorizedXml(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $raw = trim($value);

        if (str_starts_with($raw, '<')) {
            return $raw;
        }

        $decoded = base64_decode($raw, true);
        if ($decoded !== false && str_starts_with(trim($decoded), '<')) {
            return $decoded;
        }

        $decodedHtml = html_entity_decode($raw, ENT_QUOTES | ENT_XML1);
        if (str_starts_with(trim($decodedHtml), '<')) {
            return $decodedHtml;
        }

        return $raw;
    }

    protected function persistSoapRaw(string $channel, ?string $request, ?string $response, ?string $accessKey = null): void
    {
        if (! $request && ! $response) {
            return;
        }

        $basePath = trim((string) config('sri.soap_raw_path', 'sri/soap'), '/');
        $keySegment = $accessKey ?: 'unknown';
        $timestamp = now()->format('Ymd_His');

        if ($request) {
            Storage::disk('local')->put(
                $basePath.'/'.$keySegment.'/'.$channel.'_'.$timestamp.'_request.xml',
                $request
            );
        }

        if ($response) {
            Storage::disk('local')->put(
                $basePath.'/'.$keySegment.'/'.$channel.'_'.$timestamp.'_response.xml',
                $response
            );
        }
    }

    /**
     * Fallback for reading PKCS12 using CLI (bypasses OpenSSL 3 legacy restrictions)
     */
    protected function readPkcs12ViaCli(string $path, string $password): array
    {
        $escapedPath = escapeshellarg($path);
        $escapedPassword = escapeshellarg("pass:$password");

        // Try Legacy command first (explicit -legacy provider for OpenSSL 3)
        $legacyCommand = "openssl pkcs12 -legacy -in $escapedPath -nodes -passin $escapedPassword";
        // Fallback or Standard command
        $command = "openssl pkcs12 -in $escapedPath -nodes -passin $escapedPassword";

        $output = shell_exec($legacyCommand);

        if (! $output || trim($output) === '') {
            $output = shell_exec($command);
        }

        if (! $output || trim($output) === '') {
            throw new Exception('OpenSSL CLI command failed or returned empty (Password incorrect or file corrupted).');
        }

        $certs = [];

        // Extract Certificate
        if (preg_match('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $output, $matches)) {
            $certs['cert'] = $matches[0];
        } else {
            throw new Exception('No certificate found in CLI output.');
        }

        // Extract Private Key
        if (preg_match('/-----BEGIN PRIVATE KEY-----.*?-----END PRIVATE KEY-----/s', $output, $matches)) {
            $certs['pkey'] = $matches[0];
        } elseif (preg_match('/-----BEGIN RSA PRIVATE KEY-----.*?-----END RSA PRIVATE KEY-----/s', $output, $matches)) {
            $certs['pkey'] = $matches[0];
        } else {
            throw new Exception('No private key found in CLI output.');
        }

        return $certs;
    }

    protected function modernizeP12File(string $targetPath, string $certContent, string $keyContent, string $password): void
    {
        $tempPem = tempnam(sys_get_temp_dir(), 'sri_cert_');
        $tempP12 = tempnam(sys_get_temp_dir(), 'sri_modern_');

        if ($tempPem === false || $tempP12 === false) {
            throw new Exception('No se pudieron crear archivos temporales para la conversion.');
        }

        file_put_contents($tempPem, $keyContent."\n".$certContent);

        $escapedPem = escapeshellarg($tempPem);
        $escapedP12 = escapeshellarg($tempP12);
        $escapedPass = escapeshellarg("pass:$password");

        $cmd = "openssl pkcs12 -export -in $escapedPem -out $escapedP12 -passout $escapedPass -keypbe AES-256-CBC -certpbe AES-256-CBC -macalg SHA256";

        $output = shell_exec($cmd);

        @unlink($tempPem);

        if (file_exists($tempP12) && filesize($tempP12) > 0) {
            if (! copy($tempP12, $targetPath)) {
                @unlink($tempP12);
                throw new Exception('No se pudo sobrescribir el archivo original con la version moderna.');
            }
            @unlink($tempP12);
            Log::info("SRI: Certificado modernizado y guardado exitosamente en $targetPath");
        } else {
            throw new Exception("Fallo la generacion del certificado moderno (OpenSSL output: $output)");
        }
    }

    /**
     * @return array{valid_from: ?\Carbon\Carbon, expires_at: ?\Carbon\Carbon, subject: array, issuer: array}
     */
    protected function extractDatesFromCerts(array $certs): array
    {
        if (! isset($certs['cert'])) {
            return [];
        }
        $certData = openssl_x509_parse($certs['cert']);

        return [
            'valid_from' => isset($certData['validFrom_time_t']) ? \Carbon\Carbon::createFromTimestamp($certData['validFrom_time_t']) : null,
            'expires_at' => isset($certData['validTo_time_t']) ? \Carbon\Carbon::createFromTimestamp($certData['validTo_time_t']) : null,
            'subject' => $certData['subject'] ?? [],
            'issuer' => $certData['issuer'] ?? [],
        ];
    }
}
