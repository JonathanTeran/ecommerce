<?php

use App\Services\SriService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    config([
        'sri.soap_raw_path' => 'testing/soap',
        'sri.validate_xsd' => false,
        'sri.validate_totals' => false,
    ]);
});

class SpySoapClient extends SoapClient
{
    public string $lastRequest = '';

    public function __doRequest(string $request, string $location, string $action, int $version, bool $one_way = false): string
    {
        $this->lastRequest = $request;

        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
  <SOAP-ENV:Body>
    <ns2:validarComprobanteResponse xmlns:ns2="http://ec.gob.sri.ws.recepcion">
      <RespuestaRecepcionComprobante>
        <estado>RECIBIDA</estado>
      </RespuestaRecepcionComprobante>
    </ns2:validarComprobanteResponse>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML;
    }
}

class TestSriApiClient extends \App\Services\Sri\SriApiClient
{
    public function __construct(private SpySoapClient $spyClient)
    {
        parent::__construct(
            urls: ['recepcion' => 'http://localhost', 'autorizacion' => 'http://localhost'],
            signaturePath: '',
            signaturePassword: '',
        );
    }

    protected function makeSoapClient(string $wsdl, array $options): SoapClient
    {
        return $this->spyClient;
    }
}

class TestSriService extends SriService
{
    public function __construct(SpySoapClient $client)
    {
        parent::__construct();
        $this->apiClient = new TestSriApiClient($client);
    }
}

it('sends raw xml so the SOAP payload is base64-encoded once', function () {
    $wsdl = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<wsdl:definitions xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:tns="http://ec.gob.sri.ws.recepcion" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:ns1="http://schemas.xmlsoap.org/soap/http" name="RecepcionComprobantesOfflineService" targetNamespace="http://ec.gob.sri.ws.recepcion">
  <wsdl:types>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:tns="http://ec.gob.sri.ws.recepcion" elementFormDefault="unqualified" targetNamespace="http://ec.gob.sri.ws.recepcion" version="1.0">

  <xs:element name="RespuestaSolicitud" type="tns:respuestaSolicitud"/>

  <xs:element name="comprobante" type="tns:comprobante"/>

  <xs:element name="mensaje" type="tns:mensaje"/>

  <xs:element name="validarComprobante" type="tns:validarComprobante"/>

  <xs:element name="validarComprobanteResponse" type="tns:validarComprobanteResponse"/>

  <xs:complexType name="validarComprobante">
    <xs:sequence>
      <xs:element minOccurs="0" name="xml" type="xs:base64Binary"/>
    </xs:sequence>
  </xs:complexType>

  <xs:complexType name="validarComprobanteResponse">
    <xs:sequence>
      <xs:element minOccurs="0" name="RespuestaRecepcionComprobante" type="tns:respuestaSolicitud"/>
    </xs:sequence>
  </xs:complexType>

  <xs:complexType name="respuestaSolicitud">
    <xs:sequence>
      <xs:element minOccurs="0" name="estado" type="xs:string"/>
      <xs:element minOccurs="0" name="comprobantes">
        <xs:complexType>
          <xs:sequence>
            <xs:element maxOccurs="unbounded" minOccurs="0" ref="tns:comprobante"/>
          </xs:sequence>
        </xs:complexType>
      </xs:element>
    </xs:sequence>
  </xs:complexType>

  <xs:complexType name="comprobante">
    <xs:sequence>
      <xs:element minOccurs="0" name="claveAcceso" type="xs:string"/>
      <xs:element minOccurs="0" name="mensajes">
        <xs:complexType>
          <xs:sequence>
            <xs:element maxOccurs="unbounded" minOccurs="0" ref="tns:mensaje"/>
          </xs:sequence>
        </xs:complexType>
      </xs:element>
    </xs:sequence>
  </xs:complexType>

  <xs:complexType name="mensaje">
    <xs:sequence>
      <xs:element minOccurs="0" name="identificador" type="xs:string"/>
      <xs:element minOccurs="0" name="mensaje" type="xs:string"/>
      <xs:element minOccurs="0" name="informacionAdicional" type="xs:string"/>
      <xs:element minOccurs="0" name="tipo" type="xs:string"/>
    </xs:sequence>
  </xs:complexType>

</xs:schema>
  </wsdl:types>
  <wsdl:message name="validarComprobanteResponse">
    <wsdl:part element="tns:validarComprobanteResponse" name="parameters">
    </wsdl:part>
  </wsdl:message>
  <wsdl:message name="validarComprobante">
    <wsdl:part element="tns:validarComprobante" name="parameters">
    </wsdl:part>
  </wsdl:message>
  <wsdl:portType name="RecepcionComprobantesOffline">
    <wsdl:operation name="validarComprobante">
      <wsdl:input message="tns:validarComprobante" name="validarComprobante">
    </wsdl:input>
      <wsdl:output message="tns:validarComprobanteResponse" name="validarComprobanteResponse">
    </wsdl:output>
    </wsdl:operation>
  </wsdl:portType>
  <wsdl:binding name="RecepcionComprobantesOfflineServiceSoapBinding" type="tns:RecepcionComprobantesOffline">
    <soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
    <wsdl:operation name="validarComprobante">
      <soap:operation soapAction="" style="document"/>
      <wsdl:input name="validarComprobante">
        <soap:body use="literal"/>
      </wsdl:input>
      <wsdl:output name="validarComprobanteResponse">
        <soap:body use="literal"/>
      </wsdl:output>
    </wsdl:operation>
  </wsdl:binding>
  <wsdl:service name="RecepcionComprobantesOfflineService">
    <wsdl:port binding="tns:RecepcionComprobantesOfflineServiceSoapBinding" name="RecepcionComprobantesOfflinePort">
      <soap:address location="https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline"/>
    </wsdl:port>
  </wsdl:service>
</wsdl:definitions>
XML;

    $wsdlPath = tempnam(sys_get_temp_dir(), 'sri-wsdl-');
    file_put_contents($wsdlPath, $wsdl);

    $client = new SpySoapClient($wsdlPath, ['trace' => true]);
    $service = new TestSriService($client);

    $xmlSigned = '<?xml version="1.0" encoding="UTF-8"?><factura id="comprobante" version="1.1.0"></factura>';
    $response = $service->sendToRecepcion($xmlSigned);

    unlink($wsdlPath);

    expect($response['status'])->toBe('RECIBIDA');

    preg_match('/<xml[^>]*>(.*)<\\/xml>/s', $client->lastRequest, $matches);
    $payload = $matches[1] ?? '';

    expect($payload)->toBe(base64_encode($xmlSigned));
});
