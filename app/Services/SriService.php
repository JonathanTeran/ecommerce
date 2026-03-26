<?php

namespace App\Services;

use App\Models\GeneralSetting;
use App\Models\Order;
use App\Services\Sri\SriAccessKeyGenerator;
use App\Services\Sri\SriApiClient;
use App\Services\Sri\SriXmlBuilder;
use Illuminate\Support\Facades\Storage;

class SriService
{
    public SriAccessKeyGenerator $keyGenerator;

    public SriXmlBuilder $xmlBuilder;

    public SriApiClient $apiClient;

    protected $environment;

    protected $urls;

    protected $ruc;

    protected $companyName;

    protected $commercialName;

    protected $address;

    protected $contabilidad;

    protected $signaturePath;

    protected $signaturePassword;

    protected $estab;

    protected $ptoEmi;

    protected $codDoc;

    protected string $invoiceVersion;

    public function __construct()
    {
        $settings = GeneralSetting::first();

        // Defaults from config if settings not ready
        $configEnv = config('sri.environment', 1);
        $this->invoiceVersion = config('sri.invoice_version', '2.1.0');

        // Environment
        $this->environment = $settings?->sri_environment ?? $configEnv;
        $this->urls = config('sri.urls.'.$this->environment);

        // Company Info
        $this->ruc = $settings?->sri_ruc ?? config('sri.ruc');
        $this->companyName = $settings?->sri_company_name ?? config('sri.company_name');
        $this->address = $settings?->sri_establishment_address ?? config('sri.address');
        $this->contabilidad = ($settings?->sri_accounting_required ?? false) ? 'SI' : 'NO';

        // Signature
        if ($settings && $settings->sri_signature_path) {
            // Using 'local' disk which maps to storage/app/private
            $this->signaturePath = Storage::disk('local')->path($settings->sri_signature_path);
        } else {
            $this->signaturePath = config('sri.signature_path');
        }

        $this->signaturePassword = $settings?->sri_signature_password ?? config('sri.signature_password');

        // Codes
        $this->codDoc = config('sri.cod_doc') ?? '01'; // 01 Factura
        $this->estab = ($settings?->sri_establishment_code ?? config('sri.estab')) ?: '001';
        $this->ptoEmi = ($settings?->sri_emission_point_code ?? config('sri.pto_emi')) ?: '001';

        // Ensure Address is not empty
        $this->address = ($settings?->sri_establishment_address ?? config('sri.address')) ?: 'Ecuador';
        $this->companyName = ($settings?->sri_company_name ?? config('sri.company_name')) ?: 'Tienda Virtual';
        $this->commercialName = ($settings?->sri_commercial_name ?? $this->companyName);
        $this->ruc = ($settings?->sri_ruc ?? config('sri.ruc')) ?: '9999999999999';

        // Initialize sub-services
        $this->keyGenerator = new SriAccessKeyGenerator(
            ruc: $this->ruc,
            environment: $this->environment,
            codDoc: $this->codDoc,
            estab: $this->estab,
            ptoEmi: $this->ptoEmi,
        );

        $this->xmlBuilder = new SriXmlBuilder(
            environment: $this->environment,
            ruc: $this->ruc,
            companyName: $this->companyName,
            commercialName: $this->commercialName,
            address: $this->address,
            contabilidad: $this->contabilidad,
            codDoc: $this->codDoc,
            estab: $this->estab,
            ptoEmi: $this->ptoEmi,
            invoiceVersion: $this->invoiceVersion,
        );

        $this->apiClient = new SriApiClient(
            urls: $this->urls ?? [],
            signaturePath: $this->signaturePath ?? '',
            signaturePassword: $this->signaturePassword ?? '',
        );
    }

    /**
     * Generate Access Key (Clave de Acceso)
     */
    public function generateAccessKey(Order $order): string
    {
        return $this->keyGenerator->generate($order);
    }

    /**
     * Generate Factura XML
     */
    public function generateInvoiceXml(Order $order, string $accessKey): string
    {
        return $this->xmlBuilder->generateInvoiceXml($order, $accessKey);
    }

    /**
     * Sign XML using OpenSSL and PKCS12
     */
    public function signXml(string $xmlContent): string
    {
        return $this->apiClient->signXml($xmlContent);
    }

    /**
     * Send to SRI Recepcion
     */
    public function sendToRecepcion(string $xmlSigned): array
    {
        return $this->apiClient->sendToRecepcion($xmlSigned);
    }

    protected function makeSoapClient(string $wsdl, array $options): \SoapClient
    {
        return $this->apiClient->sendToRecepcion('') ? new \SoapClient($wsdl, $options) : new \SoapClient($wsdl, $options);
    }

    public function scheduleAuthorizationCheck(Order $order, int $attempt = 1): void
    {
        $this->apiClient->scheduleAuthorizationCheck($order, $attempt);
    }

    public function shouldAttemptAuthorization(array $recepcionResponse): bool
    {
        return $this->apiClient->shouldAttemptAuthorization($recepcionResponse);
    }

    public function authorize(string $accessKey): array
    {
        return $this->apiClient->authorize($accessKey);
    }

    public function storeAuthorizedXml(Order $order, string $xml): string
    {
        return $this->apiClient->storeAuthorizedXml($order, $xml);
    }

    /**
     * Main function to validate, modernize, and extract info from P12
     */
    public function validateAndModernizeP12(string $path, string $password): array
    {
        return $this->apiClient->validateAndModernizeP12($path, $password);
    }
}
