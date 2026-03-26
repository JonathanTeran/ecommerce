<?php

use App\Services\SriService;
use App\Services\SriXmlValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('validates xml against xsd', function () {
    Storage::fake('local');

    $xsd = <<<'XSD'
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="note">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="to" type="xs:string"/>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
</xs:schema>
XSD;

    Storage::disk('local')->put('testing/xsd/simple.xsd', $xsd);
    $xsdPath = Storage::disk('local')->path('testing/xsd/simple.xsd');

    $validator = new SriXmlValidator();

    $validResult = $validator->validate('<note><to>Ok</to></note>', $xsdPath);
    expect($validResult['valid'])->toBeTrue();

    $invalidResult = $validator->validate('<note><from>No</from></note>', $xsdPath);
    expect($invalidResult['valid'])->toBeFalse();
    expect($invalidResult['errors'])->not()->toBeEmpty();
});

it('stops before recepcion when xsd validation fails', function () {
    Storage::fake('local');

    $xsd = <<<'XSD'
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="note">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="to" type="xs:string"/>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
</xs:schema>
XSD;

    Storage::disk('local')->put('testing/xsd/stop.xsd', $xsd);
    $xsdPath = Storage::disk('local')->path('testing/xsd/stop.xsd');

    config([
        'sri.validate_xsd' => true,
        'sri.xsd_path' => $xsdPath,
        'sri.validate_totals' => false,
    ]);

    $service = new class extends SriService {
        public bool $soapCalled = false;

        protected function makeSoapClient(string $wsdl, array $options): SoapClient
        {
            $this->soapCalled = true;

            throw new Exception('SOAP should not be called.');
        }
    };

    $response = $service->sendToRecepcion('<note><from>Fail</from></note>');

    expect($response['status'])->toBe('XSD_ERROR');
    expect($response['source'])->toBe('offline');
    expect($response['validation'])->toBe('xsd');
    expect($service->soapCalled)->toBeFalse();
});

it('stops before recepcion when totals validation fails', function () {
    Storage::fake('local');

    config([
        'sri.validate_xsd' => false,
        'sri.validate_totals' => true,
    ]);

    $service = new class extends SriService {
        public bool $soapCalled = false;

        protected function makeSoapClient(string $wsdl, array $options): SoapClient
        {
            $this->soapCalled = true;

            throw new Exception('SOAP should not be called.');
        }
    };

    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<factura id="comprobante" version="2.1.0">
  <infoFactura>
    <totalSinImpuestos>2.00</totalSinImpuestos>
    <totalDescuento>0.00</totalDescuento>
    <totalConImpuestos>
      <totalImpuesto>
        <codigo>2</codigo>
        <codigoPorcentaje>4</codigoPorcentaje>
        <baseImponible>2.00</baseImponible>
        <valor>0.30</valor>
      </totalImpuesto>
    </totalConImpuestos>
    <propina>0.00</propina>
    <importeTotal>2.00</importeTotal>
  </infoFactura>
  <detalles>
    <detalle>
      <cantidad>2.00</cantidad>
      <precioUnitario>1.00</precioUnitario>
      <descuento>0.00</descuento>
      <precioTotalSinImpuesto>2.00</precioTotalSinImpuesto>
      <impuestos>
        <impuesto>
          <codigo>2</codigo>
          <codigoPorcentaje>4</codigoPorcentaje>
          <tarifa>15.00</tarifa>
          <baseImponible>2.00</baseImponible>
          <valor>0.30</valor>
        </impuesto>
      </impuestos>
    </detalle>
  </detalles>
</factura>
XML;

    $response = $service->sendToRecepcion($xml);

    expect($response['status'])->toBe('DATA_ERROR');
    expect($response['source'])->toBe('offline');
    expect($response['validation'])->toBe('totals');
    expect($service->soapCalled)->toBeFalse();
});

it('stores soap raw payloads', function () {
    Storage::fake('local');
    Carbon::setTestNow('2026-01-23 12:00:00');

    $apiClient = new class([], '', '') extends \App\Services\Sri\SriApiClient {
        public function storeSoapRaw(string $channel, ?string $request, ?string $response, ?string $accessKey = null): void
        {
            $this->persistSoapRaw($channel, $request, $response, $accessKey);
        }
    };

    $apiClient->storeSoapRaw('recepcion', '<request/>', '<response/>', '123');

    $files = Storage::disk('local')->allFiles('sri/soap/123');

    expect($files)->toHaveCount(2);
    expect(collect($files)->filter(fn (string $file): bool => str_ends_with($file, '_request.xml'))->count())->toBe(1);
    expect(collect($files)->filter(fn (string $file): bool => str_ends_with($file, '_response.xml'))->count())->toBe(1);

    Carbon::setTestNow();
});
